<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\User;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\Abonnement;
use App\Models\Log;
use App\Models\Taxe;
use App\Models\SupportTicket;
use App\Exports\FacturesAbonnementExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Contrôleur Admin SICOFIL
 * Administration centrale multi-communes pour la plateforme nationale
 */
class AdminSicofilController extends Controller
{
    /**
     * Dashboard Admin SICOFIL - Vue d'ensemble nationale
     */
    public function dashboard()
    {
        // Statistiques globales (sans scope commune pour vue nationale)
        $stats = [
            'total_communes' => Commune::where('actif', true)->count(),
            'total_communes_actives' => Commune::where('actif', true)
                ->whereHas('abonnement', function($q) {
                    $q->where('statut', 'actif');
                })->count(),
            'total_utilisateurs' => User::count(),
            'total_collecteurs' => User::where('role', 'collecteur')->count(),
            'total_unites' => UniteCommerciale::withoutGlobalScopes()->count(),
            'total_paiements_mois' => Paiement::withoutGlobalScopes()
                ->whereMonth('date_paiement', now()->month)
                ->whereYear('date_paiement', now()->year)
                ->where('statut', 'valide')
                ->count(),
            'recettes_totales_mois' => Paiement::withoutGlobalScopes()
                ->whereMonth('date_paiement', now()->month)
                ->whereYear('date_paiement', now()->year)
                ->where('statut', 'valide')
                ->sum('montant'),
            'recettes_totales_annee' => Paiement::withoutGlobalScopes()
                ->whereYear('date_paiement', now()->year)
                ->where('statut', 'valide')
                ->sum('montant'),
        ];

        // Top 10 communes par recettes (sans scope)
        $topCommunes = Commune::withSum(['paiements as recettes' => function($q) {
            $q->withoutGlobalScopes()->where('statut', 'valide')->whereYear('date_paiement', now()->year);
        }], 'montant')
        ->orderByDesc('recettes')
        ->limit(10)
        ->get();

        // Communes avec abonnement expirant bientôt (30 jours)
        $abonnementsExpirant = DB::table('abonnements')
            ->join('communes', 'abonnements.commune_id', '=', 'communes.id')
            ->where('abonnements.statut', 'actif')
            ->whereBetween('abonnements.date_expiration', [now(), now()->addDays(30)])
            ->select('communes.nom', 'abonnements.*')
            ->get();

        // Activité webhook (24h)
        $webhooksStats = DB::table('webhook_logs')
            ->where('created_at', '>=', now()->subDay())
            ->select('provider', 'statut', DB::raw('count(*) as total'))
            ->groupBy('provider', 'statut')
            ->get();

        // Notifications en attente
        $notificationsEnAttente = DB::table('notifications')
            ->where('statut', 'en_attente')
            ->count();

        return view('admin.sicofil.dashboard', compact(
            'stats',
            'topCommunes',
            'abonnementsExpirant',
            'webhooksStats',
            'notificationsEnAttente'
        ));
    }

    /**
     * Formulaire de création d'un abonnement (commune sans abonnement)
     */
    public function createAbonnement()
    {
        $communesSansAbonnement = Commune::where('actif', true)->whereDoesntHave('abonnement')->orderBy('nom')->get();
        return view('admin.sicofil.abonnement-create', compact('communesSansAbonnement'));
    }

    /**
     * Enregistrer un nouvel abonnement
     */
    public function storeAbonnement(Request $request)
    {
        $request->validate([
            'commune_id' => 'required|exists:communes,id',
            'formule' => 'required|in:basique,standard,premium',
            'date_debut' => 'required|date',
            'date_expiration' => 'required|date|after_or_equal:today',
            'montant_fixe_annuel' => 'nullable|numeric|min:0',
            'pourcentage_recettes' => 'nullable|numeric|min:0|max:100',
            'frequence_paiement' => 'required|in:mensuel,trimestriel,semestriel,annuel',
            'montant_achat_licence' => 'nullable|numeric|min:0',
            'frais_maintenance' => 'nullable|numeric|min:0',
        ]);

        $commune = Commune::findOrFail($request->commune_id);
        if ($commune->abonnement) {
            return redirect()->route('admin.sicofil.abonnements.index')
                ->withErrors('Cette commune a déjà un abonnement.');
        }

        Abonnement::create([
            'commune_id' => $commune->id,
            'statut' => 'actif',
            'formule' => $request->formule,
            'date_debut' => $request->date_debut,
            'date_expiration' => $request->date_expiration,
            'montant_fixe_annuel' => $request->montant_fixe_annuel ?? 0,
            'pourcentage_recettes' => $request->pourcentage_recettes ?? 0,
            'frequence_paiement' => $request->frequence_paiement,
            'montant_achat_licence' => $request->montant_achat_licence ?? 0,
            'frais_maintenance' => $request->frais_maintenance ?? 0,
        ]);

        return redirect()->route('admin.sicofil.abonnements.index')
            ->with('success', "Abonnement créé pour la commune « {$commune->nom} ».");
    }

    /**
     * Gestion des abonnements
     */
    public function abonnements()
    {
        $abonnements = DB::table('abonnements')
            ->join('communes', 'abonnements.commune_id', '=', 'communes.id')
            ->select('communes.nom as commune_nom', 'abonnements.*')
            ->orderBy('abonnements.date_expiration', 'asc')
            ->paginate(20);

        // Récupérer les recettes par commune (abonnements actifs)
        $recettesParCommune = DB::table('abonnements')
            ->join('communes', 'abonnements.commune_id', '=', 'communes.id')
            ->leftJoin('paiements', function ($join) {
                $join->on('abonnements.commune_id', '=', 'paiements.commune_id')
                     ->where('paiements.statut', 'valide');
            })
            ->select(
                'communes.nom as commune_nom',
                'abonnements.frais_maintenance',
                'abonnements.montant_achat_licence',
                'abonnements.pourcentage_recettes',
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes')
            )
            ->where('abonnements.statut', 'actif')
            ->groupBy('communes.nom', 'abonnements.frais_maintenance', 'abonnements.montant_achat_licence', 'abonnements.pourcentage_recettes')
            ->orderBy('communes.nom')
            ->get();

        foreach ($recettesParCommune as $item) {
            $item->montant_du_sicofil = $item->total_recettes * ($item->pourcentage_recettes ?? 0) / 100;
        }

        return view('admin.sicofil.abonnements', compact('abonnements', 'recettesParCommune'));
    }

    /**
     * Détails d'un abonnement
     */
    public function showAbonnement($id)
    {
        $abonnement = DB::table('abonnements')
            ->join('communes', 'abonnements.commune_id', '=', 'communes.id')
            ->where('abonnements.id', $id)
            ->select('communes.nom as commune_nom', 'abonnements.*')
            ->first();

        if (!$abonnement) {
            return redirect()->route('admin.sicofil.abonnements.index')->withErrors('Abonnement introuvable');
        }

        // Factures liées
        $factures = DB::table('factures_abonnement')
            ->where('abonnement_id', $id)
            ->orderByDesc('date_emission')
            ->get();

        return view('admin.sicofil.abonnement-details', compact('abonnement', 'factures'));
    }

    /**
     * Suspendre un abonnement
     */
    public function suspendAbonnement($id)
    {
        DB::table('abonnements')
            ->where('id', $id)
            ->update(['statut' => 'suspendu', 'updated_at' => now()]);

        return back()->with('success', 'Abonnement suspendu avec succès');
    }

    /**
     * Activer un abonnement
     */
    public function activateAbonnement($id)
    {
        DB::table('abonnements')
            ->where('id', $id)
            ->update(['statut' => 'actif', 'updated_at' => now()]);

        return back()->with('success', 'Abonnement activé avec succès');
    }

    /**
     * Formulaire de modification d'un abonnement
     */
    public function editAbonnement($id)
    {
        $abonnement = DB::table('abonnements')
            ->join('communes', 'abonnements.commune_id', '=', 'communes.id')
            ->where('abonnements.id', $id)
            ->select('communes.nom as commune_nom', 'abonnements.*')
            ->first();

        if (!$abonnement) {
            return redirect()->route('admin.sicofil.abonnements.index')->withErrors('Abonnement introuvable');
        }

        return view('admin.sicofil.abonnement-edit', compact('abonnement'));
    }

    /**
     * Enregistrer les modifications d'un abonnement
     */
    public function updateAbonnement(Request $request, $id)
    {
        $request->validate([
            'formule'              => 'required|in:basique,standard,premium',
            'statut'               => 'required|in:actif,suspendu,expire',
            'date_debut'           => 'required|date',
            'date_expiration'      => 'required|date',
            'montant_fixe_annuel'  => 'nullable|numeric|min:0',
            'pourcentage_recettes' => 'nullable|numeric|min:0|max:100',
            'frequence_paiement' => 'required|in:mensuel,trimestriel,semestriel,annuel',
            'montant_achat_licence' => 'nullable|numeric|min:0',
            'frais_maintenance' => 'nullable|numeric|min:0',
        ]);

        DB::table('abonnements')->where('id', $id)->update([
            'formule'              => $request->formule,
            'statut'               => $request->statut,
            'date_debut'           => $request->date_debut,
            'date_expiration'      => $request->date_expiration,
            'montant_fixe_annuel'  => $request->montant_fixe_annuel ?? 0,
            'pourcentage_recettes' => $request->pourcentage_recettes ?? 0,
            'frequence_paiement' => $request->frequence_paiement,
            'montant_achat_licence' => $request->montant_achat_licence ?? 0,
            'frais_maintenance' => $request->frais_maintenance ?? 0,
            'updated_at'           => now(),
        ]);

        return redirect()->route('admin.sicofil.abonnements.index')
            ->with('success', 'Abonnement modifié avec succès.');
    }

    /**
     * Renouveler un abonnement (prolonger d'un an ou durée personnalisée)
     */
    public function renewAbonnement(Request $request, $id)
    {
        $request->validate([
            'duree_mois' => 'required|integer|min:1|max:60',
        ]);

        $abonnement = DB::table('abonnements')->where('id', $id)->first();

        if (!$abonnement) {
            return redirect()->route('admin.sicofil.abonnements.index')->withErrors('Abonnement introuvable');
        }

        $dateExpiration = \Carbon\Carbon::parse($abonnement->date_expiration);
        $nouvelleExpiration = ($dateExpiration->isPast() ? now() : $dateExpiration)
            ->addMonths($request->duree_mois);

        DB::table('abonnements')->where('id', $id)->update([
            'date_expiration' => $nouvelleExpiration->toDateString(),
            'statut'          => 'actif',
            'updated_at'      => now(),
        ]);

        return redirect()->route('admin.sicofil.abonnements.index')
            ->with('success', "Abonnement renouvelé jusqu'au " . $nouvelleExpiration->format('d/m/Y') . '.');
    }

    /**
     * Supprimer un abonnement
     */
    public function deleteAbonnement($id)
    {
        $abonnement = DB::table('abonnements')
            ->join('communes', 'abonnements.commune_id', '=', 'communes.id')
            ->where('abonnements.id', $id)
            ->select('communes.nom as commune_nom', 'abonnements.id')
            ->first();

        if (!$abonnement) {
            return redirect()->route('admin.sicofil.abonnements.index')->withErrors('Abonnement introuvable');
        }

        DB::table('factures_abonnement')->where('abonnement_id', $id)->delete();
        DB::table('abonnements')->where('id', $id)->delete();

        return redirect()->route('admin.sicofil.abonnements.index')
            ->with('success', "Abonnement de « {$abonnement->commune_nom} » supprimé.");
    }

    /**
     * Liste des factures SaaS
     */
    public function factures()
    {
        $factures = DB::table('factures_abonnement')
            ->join('communes', 'factures_abonnement.commune_id', '=', 'communes.id')
            ->select('communes.nom as commune_nom', 'factures_abonnement.*')
            ->orderByDesc('factures_abonnement.date_emission')
            ->paginate(30);

        $statsFactures = [
            'en_attente' => DB::table('factures_abonnement')->where('statut', 'en_attente')->count(),
            'payees' => DB::table('factures_abonnement')->where('statut', 'payee')->count(),
            'en_retard' => DB::table('factures_abonnement')->where('statut', 'en_retard')->count(),
            'montant_total_attendu' => DB::table('factures_abonnement')
                ->whereIn('statut', ['en_attente', 'en_retard'])
                ->sum('montant_total'),
        ];

        return view('admin.sicofil.factures', compact('factures', 'statsFactures'));
    }

    public function taxes(Request $request)
    {
        $communes = Commune::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $selectedCommuneId = $request->integer('commune_id') ?: null;

        $taxes = Taxe::withoutGlobalScope('commune')
            ->with('commune:id,nom')
            ->withCount(['unitesCommerciales' => fn ($q) => $q->withoutGlobalScope('commune')])
            ->when($selectedCommuneId, fn ($q) => $q->where('commune_id', $selectedCommuneId))
            ->orderBy('commune_id')
            ->orderBy('nom')
            ->paginate(30)
            ->withQueryString();

        return view('admin.sicofil.taxes', compact('communes', 'taxes', 'selectedCommuneId'));
    }

    public function logs(Request $request)
    {
        $communes = Commune::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $selectedCommuneId = $request->integer('commune_id') ?: null;

        $logs = Log::withoutGlobalScope('commune')
            ->with(['commune:id,nom', 'user:id,name,identifiant,role'])
            ->when($selectedCommuneId, fn ($q) => $q->where('commune_id', $selectedCommuneId))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.sicofil.logs', compact('communes', 'logs', 'selectedCommuneId'));
    }

    /**
     * Générer les factures mensuelles automatiquement
     */
    public function generateFactures(Request $request)
    {
        $periode = $request->input('periode', 'mensuel');
        $mois = now()->month;
        $annee = now()->year;

        // Récupérer tous les abonnements actifs ou en test (pour générer des factures)
        $abonnements = DB::table('abonnements')
            ->whereIn('statut', ['actif', 'test'])
            ->get();

        $facturesCreees = 0;

        foreach ($abonnements as $abonnement) {
            // Vérifier si une facture existe déjà pour cette période
            $existante = DB::table('factures_abonnement')
                ->where('abonnement_id', $abonnement->id)
                ->where('periode', $periode)
                ->whereMonth('date_emission', $mois)
                ->whereYear('date_emission', $annee)
                ->exists();

            if ($existante) {
                continue;
            }

            // Calculer le montant variable (% des recettes)
            $recettesMois = Paiement::where('commune_id', $abonnement->commune_id)
                ->whereMonth('created_at', $mois)
                ->whereYear('created_at', $annee)
                ->where('statut', 'valide')
                ->sum('montant');

            $montantVariable = ($recettesMois * $abonnement->pourcentage_recettes) / 100;

            // Créer la facture
            $diviseur = match ($abonnement->frequence_paiement ?? $periode) {
                'mensuel' => 12,
                'trimestriel' => 4,
                'semestriel' => 2,
                default => 1,
            };
            $montantFixe = ((float) ($abonnement->montant_fixe_annuel ?? 0)) / $diviseur;
            $montantMaintenance = ((float) ($abonnement->frais_maintenance ?? 0)) / $diviseur;
            $montantLicence = $existante ? 0 : (float) ($abonnement->montant_achat_licence ?? 0);

            DB::table('factures_abonnement')->insert([
                'abonnement_id' => $abonnement->id,
                'commune_id' => $abonnement->commune_id,
                'numero_facture' => 'FACT-' . strtoupper(uniqid()),
                'periode' => $periode,
                'date_emission' => now(),
                'date_echeance' => now()->addDays(30),
                'montant_fixe' => $montantFixe + $montantMaintenance + $montantLicence,
                'montant_variable' => $montantVariable,
                'montant_total' => $montantFixe + $montantMaintenance + $montantLicence + $montantVariable,
                'statut' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $facturesCreees++;
        }

        return back()->with('success', "{$facturesCreees} facture(s) générée(s) pour la période {$periode}");
    }

    /**
     * Marquer une facture comme payée (validation manuelle ou confirmation de paiement)
     */
    public function markFacturePayee($id)
    {
        $facture = DB::table('factures_abonnement')->where('id', $id)->first();

        if (!$facture) {
            return back()->withErrors('Facture introuvable.');
        }

        if ($facture->statut === 'payee') {
            return back()->with('success', 'Cette facture est déjà payée.');
        }

        DB::table('factures_abonnement')->where('id', $id)->update([
            'statut' => 'payee',
            'date_paiement' => now(),
            'updated_at' => now(),
        ]);

        // Log
        Log::createLog('facture_paid', [
            'facture_id' => $id,
            'commune_id' => $facture->commune_id,
            'montant' => $facture->montant_total,
        ]);

        return back()->with('success', "Facture #{$facture->numero_facture} marquée comme payée.");
    }

    /**
     * Export de la liste des factures en PDF
     */
    public function exportFacturesPdf()
    {
        $factures = DB::table('factures_abonnement')
            ->join('communes', 'factures_abonnement.commune_id', '=', 'communes.id')
            ->select('communes.nom as commune_nom', 'factures_abonnement.*')
            ->orderByDesc('factures_abonnement.date_emission')
            ->get();

        $statsFactures = [
            'en_attente' => DB::table('factures_abonnement')->where('statut', 'en_attente')->count(),
            'payees' => DB::table('factures_abonnement')->where('statut', 'payee')->count(),
            'en_retard' => DB::table('factures_abonnement')->where('statut', 'en_retard')->count(),
            'montant_total_attendu' => DB::table('factures_abonnement')
                ->whereIn('statut', ['en_attente', 'en_retard'])
                ->sum('montant_total'),
        ];

        $pdf = Pdf::loadView('admin.sicofil.factures-export-pdf', compact('factures', 'statsFactures'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('facturation-sicofil-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export de la liste des factures en Excel
     */
    public function exportFacturesExcel()
    {
        $factures = DB::table('factures_abonnement')
            ->join('communes', 'factures_abonnement.commune_id', '=', 'communes.id')
            ->select('communes.nom as commune_nom', 'factures_abonnement.*')
            ->orderByDesc('factures_abonnement.date_emission')
            ->get();

        return Excel::download(
            new FacturesAbonnementExport($factures),
            'facturation-sicofil-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export d'une facture en PDF (format professionnel)
     */
    public function facturePdf(int $id)
    {
        $facture = DB::table('factures_abonnement')
            ->join('communes', 'factures_abonnement.commune_id', '=', 'communes.id')
            ->where('factures_abonnement.id', $id)
            ->select('communes.nom as commune_nom', 'communes.code as commune_code', 'communes.region', 'communes.departement', 'factures_abonnement.*')
            ->first();

        if (!$facture) {
            abort(404);
        }

        $pdf = Pdf::loadView('admin.sicofil.facture-pdf', compact('facture'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('facture-' . preg_replace('/[^a-zA-Z0-9\-]/', '-', $facture->numero_facture) . '.pdf');
    }

    /**
     * Statistiques globales
     */
    public function stats()
    {
        $recettesParMois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $recettesParMois[] = [
                'mois' => $date->format('M Y'),
                'montant' => Paiement::withoutGlobalScopes()
                    ->whereMonth('date_paiement', $date->month)
                    ->whereYear('date_paiement', $date->year)
                    ->where('statut', 'valide')
                    ->sum('montant'),
            ];
        }

        $paiementsParMode = Paiement::withoutGlobalScopes()
            ->where('statut', 'valide')
            ->whereYear('date_paiement', now()->year)
            ->select('mode_paiement', DB::raw('count(*) as total, sum(montant) as montant_total'))
            ->groupBy('mode_paiement')
            ->get();

        $activeAbonnements = Abonnement::with('commune:id,nom')
            ->where('statut', 'actif')
            ->get();

        $mrr = $activeAbonnements->sum(function ($abonnement) {
            $annuel = (float) ($abonnement->montant_fixe_annuel ?? 0) + (float) ($abonnement->frais_maintenance ?? 0);
            return $annuel / 12;
        });

        $arr = $mrr * 12;
        $recettesGlobales = Paiement::withoutGlobalScopes()->where('statut', 'valide')->sum('montant');
        $volumeGlobalTransactions = Paiement::withoutGlobalScopes()->count();
        $volumeTransactionsValidees = Paiement::withoutGlobalScopes()->where('statut', 'valide')->count();
        $recettesMoisCourant = Paiement::withoutGlobalScopes()
            ->where('statut', 'valide')
            ->whereMonth('date_paiement', now()->month)
            ->whereYear('date_paiement', now()->year)
            ->sum('montant');
        $recettesMoisPrecedent = Paiement::withoutGlobalScopes()
            ->where('statut', 'valide')
            ->whereMonth('date_paiement', now()->subMonth()->month)
            ->whereYear('date_paiement', now()->subMonth()->year)
            ->sum('montant');
        $croissanceRecettes = $recettesMoisPrecedent > 0
            ? (($recettesMoisCourant - $recettesMoisPrecedent) / $recettesMoisPrecedent) * 100
            : null;
        $communesActives = Commune::where('actif', true)->count();
        $communesAvecAbonnement = $activeAbonnements->count();
        $tauxConversion = $communesActives > 0 ? ($communesAvecAbonnement / $communesActives) * 100 : 0;
        $facturesEnRetard = DB::table('factures_abonnement')->where('statut', 'en_retard')->count();
        $montantFacturesEnRetard = DB::table('factures_abonnement')->where('statut', 'en_retard')->sum('montant_total');

        $commercialStats = compact(
            'mrr',
            'arr',
            'recettesGlobales',
            'volumeGlobalTransactions',
            'volumeTransactionsValidees',
            'recettesMoisCourant',
            'recettesMoisPrecedent',
            'croissanceRecettes',
            'communesActives',
            'communesAvecAbonnement',
            'tauxConversion',
            'facturesEnRetard',
            'montantFacturesEnRetard'
        );

        return view('admin.sicofil.stats', compact('recettesParMois', 'paiementsParMode', 'commercialStats', 'activeAbonnements'));
    }

    public function support(Request $request)
    {
        $communes = Commune::where('actif', true)->orderBy('nom')->get(['id', 'nom']);
        $tickets = SupportTicket::with(['commune:id,nom', 'creator:id,name', 'assignee:id,name'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->priorite))
            ->when($request->filled('commune_id'), fn ($q) => $q->where('commune_id', $request->integer('commune_id')))
            ->orderByRaw("CASE priorite WHEN 'critique' THEN 1 WHEN 'haute' THEN 2 WHEN 'normale' THEN 3 ELSE 4 END")
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $statsTickets = [
            'ouverts' => SupportTicket::whereIn('statut', ['ouvert', 'en_cours'])->count(),
            'critiques' => SupportTicket::whereIn('statut', ['ouvert', 'en_cours'])->where('priorite', 'critique')->count(),
            'resolus' => SupportTicket::where('statut', 'resolu')->count(),
        ];

        return view('admin.sicofil.support', compact('communes', 'tickets', 'statsTickets'));
    }

    public function storeSupportTicket(Request $request)
    {
        $validated = $request->validate([
            'commune_id' => 'nullable|exists:communes,id',
            'titre' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'priorite' => 'required|in:basse,normale,haute,critique',
            'categorie' => 'required|in:technique,facturation,compte,paiement,donnees,autre',
        ]);

        $validated['created_by'] = auth()->id();
        SupportTicket::create($validated);

        return redirect()->route('admin.sicofil.support')->with('success', 'Ticket support créé avec succès.');
    }

    public function updateSupportTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'statut' => 'required|in:ouvert,en_cours,resolu,ferme',
            'priorite' => 'required|in:basse,normale,haute,critique',
            'resolution' => 'nullable|string|max:5000',
        ]);

        $validated['assigned_to'] = auth()->id();
        if (in_array($validated['statut'], ['resolu', 'ferme'], true) && empty($ticket->closed_at)) {
            $validated['closed_at'] = now();
        }
        if (in_array($validated['statut'], ['ouvert', 'en_cours'], true)) {
            $validated['closed_at'] = null;
        }

        $ticket->update($validated);

        return redirect()->route('admin.sicofil.support')->with('success', 'Ticket support mis à jour.');
    }

    public function monitoring()
    {
        $checks = [];
        try {
            DB::select('select 1');
            $checks['database'] = ['status' => 'ok', 'label' => 'Base de données', 'details' => 'Connexion active'];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'critical', 'label' => 'Base de données', 'details' => $e->getMessage()];
        }

        try {
            Cache::put('sicofil_monitoring_check', now()->toDateTimeString(), 60);
            $checks['cache'] = ['status' => Cache::has('sicofil_monitoring_check') ? 'ok' : 'warning', 'label' => 'Cache', 'details' => config('cache.default')];
        } catch (\Throwable $e) {
            $checks['cache'] = ['status' => 'warning', 'label' => 'Cache', 'details' => $e->getMessage()];
        }

        $checks['storage'] = [
            'status' => is_writable(storage_path()) ? 'ok' : 'critical',
            'label' => 'Stockage',
            'details' => storage_path(),
        ];

        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        $pendingNotifications = Schema::hasTable('notifications') ? DB::table('notifications')->where('statut', 'en_attente')->count() : 0;
        $failedWebhooks = Schema::hasTable('webhook_logs') ? DB::table('webhook_logs')->where('statut', 'failed')->count() : 0;
        $recentErrors = Log::withoutGlobalScopes()
            ->where('created_at', '>=', now()->subDay())
            ->where(function ($q) {
                $q->where('action', 'like', '%failed%')->orWhere('action', 'like', '%error%')->orWhere('action', 'like', '%violation%');
            })
            ->count();

        $systemStats = compact('failedJobs', 'pendingNotifications', 'failedWebhooks', 'recentErrors');

        return view('admin.sicofil.monitoring', compact('checks', 'systemStats'));
    }

    /**
     * Journal d'audit national
     */
    public function audit()
    {
        $audits = DB::table('audit_sicofil')
            ->leftJoin('communes', 'audit_sicofil.commune_id', '=', 'communes.id')
            ->leftJoin('users', 'audit_sicofil.user_id', '=', 'users.id')
            ->select('audit_sicofil.*', 'communes.nom as commune_nom', 'users.name as user_name')
            ->orderByDesc('audit_sicofil.created_at')
            ->paginate(50);

        return view('admin.sicofil.audit', compact('audits'));
    }

    /**
     * Notifications système
     */
    public function notifications()
    {
        $notifications = DB::table('notifications')
            ->leftJoin('communes', 'notifications.commune_id', '=', 'communes.id')
            ->select('notifications.*', 'communes.nom as commune_nom')
            ->orderByDesc('notifications.created_at')
            ->paginate(50);

        return view('admin.sicofil.notifications', compact('notifications'));
    }

    /**
     * Logs des webhooks
     */
    public function webhooksLogs()
    {
        $webhooks = DB::table('webhook_logs')
            ->leftJoin('communes', 'webhook_logs.commune_id', '=', 'communes.id')
            ->leftJoin('paiements', 'webhook_logs.paiement_id', '=', 'paiements.id')
            ->select('webhook_logs.*', 'communes.nom as commune_nom', 'paiements.reference')
            ->orderByDesc('webhook_logs.webhook_received_at')
            ->paginate(50);

        return view('admin.sicofil.webhooks-logs', compact('webhooks'));
    }
}
