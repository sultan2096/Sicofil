<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\UniteCommerciale;
use App\Models\Log;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class PaiementController extends Controller
{
    private function currentCommuneId(): int
    {
        return (int) auth()->user()->commune_id;
    }

    /**
     * Display a listing of paiements.
     */
    public function index(Request $request)
    {
        // Base query (sans eager loading pour les stats)
        $query = Paiement::query();

        // Filtrer par zone et collecteur pour les collecteurs
        if (auth()->user()->role === 'collecteur') {
            $query->byZone(auth()->user()->zone_id)
                  ->where('collecteur_id', auth()->id());
        }

        // Filtres
        if ($request->filled('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }

        if ($request->filled('mode_paiement')) {
            $query->byModePaiement($request->mode_paiement);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('collecteur_id') && auth()->user()->role !== 'collecteur') {
            $query->byCollecteur($request->collecteur_id);
        }

        if ($request->filled('zone_id') && auth()->user()->role !== 'collecteur') {
            $query->byZone($request->zone_id);
        }

        if ($request->filled('unite_id')) {
            $query->where('unite_id', $request->unite_id);
        }

        // Recherche par nom / prénom / téléphone du propriétaire
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('unite', function ($q) use ($search) {
                $q->whereRaw('LOWER(nom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(prenom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(telephone) LIKE LOWER(?)', [$search]);
            });
        }

        // Stats via agrégats (performance)
        $stats = [
            'total_count'    => $query->count(),
            'montant_valide' => (clone $query)->where('statut', 'valide')->sum('montant'),
            'nombre_en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
            'montant_en_attente' => (clone $query)->where('statut', 'en_attente')->sum('montant'),
            'montant_moyen'  => $query->avg('montant') ?? 0,
        ];

        // Pagination avec eager loading
        $paiements = (clone $query)->with([
            'collecteur:id,name,zone_id',
            'contribuable:id,nom_complet,telephone',
            'unite:id,nom_proprietaire,prenom_proprietaire,nature,type_commerce,zone_id'
        ])->orderBy('date_paiement', 'desc')
          ->orderBy('id', 'desc')
          ->paginate(30)
          ->withQueryString();

        // Données filtrées par commune pour les dropdowns
        $communeId = $this->currentCommuneId();
        $zones = \App\Models\ZoneFiscale::where('commune_id', $communeId)->orderBy('nom')->get();
        $collecteurs = \App\Models\User::where('commune_id', $communeId)->where('role', 'collecteur')->select('id', 'name')->orderBy('name')->get();
        $unites = \App\Models\UniteCommerciale::where('commune_id', $communeId)->actif()->select('id', 'nom_proprietaire', 'prenom_proprietaire', 'type_commerce')->orderBy('nom_proprietaire')->orderBy('prenom_proprietaire')->get();

        // Gérer les paramètres de redirection après finalisation
        $paymentFinalized = $request->get('payment_finalized') === 'true';
        $paymentId = $request->get('payment_id');

        return view('paiements.index', compact('paiements', 'stats', 'zones', 'collecteurs', 'unites', 'paymentFinalized', 'paymentId'));
    }

    /**
     * Show the form for creating a new paiement.
     */
    public function create(Request $request)
    {
        // Autoriser les gestionnaires et les collecteurs à créer des paiements
        if (!in_array(auth()->user()->role, ['gestionnaire', 'collecteur'])) {
            abort(403, 'Accès non autorisé. Seuls les gestionnaires et collecteurs peuvent créer des paiements.');
        }

        // Pour les collecteurs, afficher seulement les unités de leur zone
        if (auth()->user()->role === 'collecteur') {
            $unites = UniteCommerciale::where('zone_id', auth()->user()->zone_id)
                ->actif()
                ->with(['zone'])
                ->orderBy('nom_proprietaire')
                ->orderBy('prenom_proprietaire')
                ->get();
        } else {
            $unites = UniteCommerciale::actif()
                ->with(['zone'])
                ->orderBy('nom_proprietaire')
                ->orderBy('prenom_proprietaire')
                ->get();
        }

        // Si on vient d'un recensement, pré-sélectionner l'unité
        $selectedUniteId = $request->get('unite_id');
        $selectedUnite = null;
        if ($selectedUniteId) {
            $selectedUnite = UniteCommerciale::with(['zone'])->find($selectedUniteId);
            // Vérifier que le collecteur a accès à cette unité
            if (auth()->user()->role === 'collecteur' && $selectedUnite && $selectedUnite->zone_id !== auth()->user()->zone_id) {
                $selectedUnite = null;
                $selectedUniteId = null;
            }
        }

        // Récupérer les taxes filtrées selon l'unité sélectionnée
        $taxes = \App\Models\Taxe::orderBy('nom')->get();
        
        // Filtrer les taxes selon les caractéristiques de l'unité sélectionnée
        if ($selectedUnite) {
            $taxes = $taxes->filter(function($taxe) use ($selectedUnite) {
                // Si la taxe n'a pas de filtres de types de commerce, elle s'applique à toutes les unités
                $hasTypes = !empty($taxe->types_commerce_applicables);
                
                // Si aucun filtre n'est défini, la taxe est applicable
                if (!$hasTypes) {
                    return true;
                }
                
                // Vérifier les types de commerce
                if (!in_array($selectedUnite->type_commerce, $taxe->types_commerce_applicables)) {
                    return false;
                }
                
                return true;
            });
        }

        // Préparer les données des unités avec leurs superficies pour le JavaScript
        $unitesData = $unites->map(function($unite) {
            return [
                'id' => $unite->id,
                'superficie' => $unite->superficie,
                'has_polygon' => !empty($unite->geometrie_polygon),
            ];
        })->keyBy('id');

        return view('paiements.create', compact('unites', 'taxes', 'selectedUnite', 'selectedUniteId', 'unitesData'));
    }

    /**
     * Store a newly created paiement in storage.
     */
    public function store(Request $request)
    {
        // Logging pour débogage
        \Log::info('Tentative de création de paiement', [
            'user_id' => auth()->id(),
            'role' => auth()->user()->role,
            'request_data' => $request->all()
        ]);

        // Autoriser les gestionnaires et les collecteurs à créer des paiements
        if (!in_array(auth()->user()->role, ['gestionnaire', 'collecteur'])) {
            abort(403, 'Accès non autorisé. Seuls les gestionnaires et collecteurs peuvent créer des paiements.');
        }

        // Validation de base
        $rules = [
            'unite_id' => 'required|exists:unites_commerciales,id',
            'taxe_id' => 'required|exists:taxes,id',
            'montant' => 'required|numeric|min:0',
            'montant_avance' => 'nullable|numeric|min:0',
            'montant_restant' => 'nullable|numeric|min:0',
            'date_paiement' => 'required|date|before_or_equal:today',
            'mode_paiement' => 'required|in:cash,wave,orange_money',
            'reference_transaction' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:500',
            // Champs de géolocalisation pour les collecteurs
            'payment_latitude' => 'nullable|numeric|between:-90,90',
            'payment_longitude' => 'nullable|numeric|between:-180,180',
            'payment_accuracy' => 'nullable|numeric|min:0'
        ];

        // Pour les collecteurs, géolocalisation obligatoire
        if (auth()->user()->role === 'collecteur') {
            $rules['payment_latitude'] = 'required|numeric|between:-90,90';
            $rules['payment_longitude'] = 'required|numeric|between:-180,180';
            $rules['payment_accuracy'] = 'required|numeric|min:0|max:5000';
        }

        $validated = $request->validate($rules);

        // Récupérer l'unité et la taxe
        $unite = UniteCommerciale::find($validated['unite_id']);
        $taxe = \App\Models\Taxe::find($validated['taxe_id']);
        
        // Vérifier que l'unité est dans la zone du collecteur
        if (auth()->user()->role === 'collecteur') {
            if ($unite->zone_id !== auth()->user()->zone_id) {
                abort(403, 'Cette unité commerciale n\'est pas dans votre zone.');
            }
        }

        // Contrôle anti-doublon : même taxe, même UC, même période
        if ($this->hasDuplicatePayment($validated['unite_id'], $validated['taxe_id'], $validated['date_paiement'], $taxe->periodicite ?? null)) {
            $createRoute = auth()->user()->role === 'collecteur'
                ? 'collecteur.paiements.create'
                : 'paiements.create';

            return redirect()->route($createRoute)
                ->with('error', 'Ce paiement a déjà été enregistré pour cette période.')
                ->withErrors([
                    'taxe_id' => 'Déjà payé : cette taxe a déjà été réglée pour cette unité et cette période.'
                ])
                ->withInput();
        }
        
        // Vérifier et calculer le montant pour les taxes foncières
        if ($taxe && $taxe->is_fonciere) {
            // Taxe foncière : vérifier que l'unité a une superficie
            if (empty($unite->superficie) || $unite->superficie <= 0) {
                return back()->withErrors([
                    'montant' => 'Cette unité n\'a pas de superficie définie. La taxe foncière nécessite une superficie. Veuillez d\'abord recenser le terrain avec sa superficie.'
                ])->withInput();
            }
            
            // Calculer le montant attendu
            $montantAttendu = $unite->superficie * $taxe->montant_par_m2;
            
            // Vérifier que le montant fourni correspond au calcul (avec une petite tolérance pour les arrondis)
            $difference = abs($validated['montant'] - $montantAttendu);
            if ($difference > 0.01) {
                return back()->withErrors([
                    'montant' => "Le montant ne correspond pas au calcul de la taxe foncière. Montant attendu : " . number_format($montantAttendu, 2, ',', ' ') . " FCFA (Superficie : {$unite->superficie} m² × {$taxe->montant_par_m2} FCFA/m²)"
                ])->withInput();
            }
            
            // Forcer le montant calculé pour éviter les erreurs d'arrondi
            $validated['montant'] = $montantAttendu;
        } else {
            // Taxe normale : vérifier que le montant correspond
            if ($taxe && abs($validated['montant'] - $taxe->montant) > 0.01) {
                return back()->withErrors([
                    'montant' => "Le montant ne correspond pas à la taxe sélectionnée. Montant attendu : " . number_format($taxe->montant, 2, ',', ' ') . " FCFA"
                ])->withInput();
            }
        }

        // Calculer le statut selon le montant restant
        $montantAvance = $validated['montant_avance'] ?? 0;
        $montantRestant = $validated['montant_restant'] ?? 0;
        $statut = $montantRestant <= 0 ? 'valide' : 'en_attente';

        $paiementData = [
            'collecteur_id' => auth()->id(),
            'unite_id' => $validated['unite_id'],
            'taxe_id' => $validated['taxe_id'],
            'montant' => $validated['montant'],
            'montant_avance' => $montantAvance,
            'montant_restant' => $montantRestant,
            'date_paiement' => $validated['date_paiement'],
            'mode_paiement' => $validated['mode_paiement'],
            'reference_transaction' => $validated['reference_transaction'],
            'commentaire' => $validated['commentaire'],
            'statut' => $statut
        ];

        // Ajouter les données de géolocalisation si disponibles (pour les collecteurs)
        if (auth()->user()->role === 'collecteur' && isset($validated['payment_latitude'])) {
            // Vérifier si les colonnes existent dans la base de données
            try {
                $hasGeoColumns = \DB::select("
                    SELECT COUNT(*) as count 
                    FROM information_schema.columns 
                    WHERE table_schema = 'public' 
                      AND table_name = 'paiements' 
                      AND column_name = 'payment_latitude'
                ");
                
                if ($hasGeoColumns[0]->count > 0) {
                    $paiementData['payment_latitude'] = $validated['payment_latitude'];
                    $paiementData['payment_longitude'] = $validated['payment_longitude'];
                    $paiementData['payment_accuracy'] = $validated['payment_accuracy'];
                    $paiementData['payment_method'] = 'GPS'; // Par défaut GPS, peut être modifié selon le contexte
                } else {
                    // Si les colonnes n'existent pas encore, continuer sans géolocalisation
                    \Log::warning('Colonnes de géolocalisation non disponibles - Migration requise');
                }
            } catch (\Exception $e) {
                // En cas d'erreur de base de données, continuer sans géolocalisation
                \Log::warning('Erreur lors de la vérification des colonnes de géolocalisation: ' . $e->getMessage());
            }
        }

        $paiement = Paiement::create($paiementData);

        Log::createLog('payment', [
            'paiement_id' => $paiement->id,
            'unite_id' => $paiement->unite_id,
            'montant' => $paiement->montant,
            'mode' => $paiement->mode_paiement
        ]);

        // Rediriger automatiquement vers la page des paiements pour les collecteurs
        if (auth()->user()->role === 'collecteur') {
            return redirect()->route('collecteur.paiements.index')
                ->with('success', 'Paiement enregistré avec succès.')
                ->with('paiement_id', $paiement->id)
                ->with('show_ticket_option', true);
        }
        
        // Pour les autres rôles, comportement habituel
        return redirect()->route('paiements.index')
            ->with('success', 'Paiement enregistré avec succès.')
            ->with('paiement_id', $paiement->id)
            ->with('show_ticket_option', true);
    }

    /**
     * Display the specified paiement.
     */
    public function show(Paiement $paiement)
    {
        // Vérifier l'accès pour les collecteurs
        if (auth()->user()->role === 'collecteur' && $paiement->collecteur_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }
        
        // Charger les relations nécessaires de manière optimisée avec select spécifique
        $paiement->load([
            'unite' => function($query) {
                $query->select('id', 'nom_proprietaire', 'prenom_proprietaire', 'nature', 'type_commerce', 'zone_id', 'adresse', 'telephone');
            },
            'unite.zone' => function($query) {
                $query->select('id', 'nom');
            },
            'collecteur' => function($query) {
                $query->select('id', 'name', 'identifiant', 'zone_id');
            },
            'collecteur.zone' => function($query) {
                $query->select('id', 'nom');
            }
        ]);
        
        return view('paiements.show', compact('paiement'));
    }

    /**
     * Remove the specified paiement from storage.
     */
    public function destroy(Paiement $paiement)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Seuls les gestionnaires peuvent supprimer des paiements.');
        }

        $paiement->delete();

        return redirect()->route('paiements.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }

    /**
     * Generate a payment receipt ticket.
     */
    public function ticket(Paiement $paiement)
    {
        // Vérifier l'accès pour les collecteurs
        if (auth()->user()->role === 'collecteur' && $paiement->collecteur_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        // Charger les relations nécessaires (commune pour le logo du ticket)
        $paiement->load(['unite.zone', 'collecteur.zone', 'commune']);

        // Générer les données pour le QR code
        $qrData = $this->generateQRData($paiement);

        return view('paiements.ticket', compact('paiement', 'qrData'));
    }

    /**
     * Generate QR code data for payment receipt
     */
    private function generateQRData(Paiement $paiement)
    {
        $data = [
            'type' => 'SICOSA_RECU',
            'id' => str_pad($paiement->id, 6, '0', STR_PAD_LEFT),
            'date' => $paiement->date_paiement->format('d/m/Y H:i'),
            'montant' => $paiement->montant,
            'mode' => $paiement->mode_paiement,
            'unite' => $paiement->unite->nom_complet_proprietaire ?? 'N/A',
            'zone' => $paiement->unite->zone->nom ?? 'N/A',
            'collecteur' => $paiement->collecteur->name ?? 'N/A',
            'reference' => $paiement->reference_transaction ?? 'N/A',
            'verification' => url('/paiements/' . $paiement->id . '/verify')
        ];

        return json_encode($data);
    }

    /**
     * Verify payment authenticity via QR code
     */
    public function verify(Paiement $paiement)
    {
        // Cette route est publique pour permettre la vérification
        $paiement->load(['unite.zone', 'collecteur.zone', 'commune']);

        return view('paiements.verify', compact('paiement'));
    }

    /**
     * API endpoint to verify QR code data
     */
    public function verifyQR(Paiement $paiement)
    {
        $paiement->load(['unite.zone', 'collecteur.zone']);

        return response()->json([
            'valid' => true,
            'status' => $paiement->statut,
            'receipt_number' => str_pad($paiement->id, 6, '0', STR_PAD_LEFT),
            'payment_date' => $paiement->date_paiement->format('d/m/Y H:i'),
            'amount' => $paiement->montant,
            'payment_mode' => $paiement->mode_paiement,
            'unit_owner' => $paiement->unite->nom_complet_proprietaire,
            'zone' => $paiement->unite->zone->nom ?? 'Non définie',
            'collector' => $paiement->collecteur->name,
            'reference' => $paiement->reference_transaction,
            'verification_url' => route('paiements.verify', $paiement),
            'verified_at' => now()->format('d/m/Y H:i')
        ]);
    }


    /**
     * Show the form to add additional payment to complete a partial payment.
     */
    public function addPayment(Paiement $paiement)
    {
        // Autoriser gestionnaire, collecteur et maire
        $allowedRoles = ['gestionnaire', 'collecteur', 'maire'];
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            abort(403, 'Accès non autorisé. Action réservée aux gestionnaires, collecteurs et maires.');
        }

        // Vérifier l'accès pour les collecteurs (rediriger avec message au lieu d'un 403)
        if (auth()->user()->role === 'collecteur' && $paiement->collecteur_id !== auth()->id()) {
            return redirect()->route('paiements.show', $paiement->id)
                ->with('error', "Vous ne pouvez finaliser que vos propres paiements.");
        }

        // Vérifier que le paiement peut être complété
        if ($paiement->statut !== 'en_attente' || $paiement->montant_restant <= 0) {
            return redirect()->route('paiements.show', $paiement)
                ->with('error', 'Ce paiement ne peut pas être complété.');
        }

        $paiement->load(['unite.zone', 'unite.taxe', 'collecteur']);

        return view('paiements.add-payment', compact('paiement'));
    }

    /**
     * Store additional payment to complete a partial payment.
     */
    public function storeAdditionalPayment(Request $request, Paiement $paiement)
    {
        // Autoriser gestionnaire, collecteur et maire
        $allowedRoles = ['gestionnaire', 'collecteur', 'maire'];
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            abort(403, 'Accès non autorisé. Action réservée aux gestionnaires, collecteurs et maires.');
        }

        // Vérifier l'accès pour les collecteurs (rediriger avec message)
        // Les gestionnaires et maires peuvent finaliser tous les paiements
        if (auth()->user()->role === 'collecteur' && $paiement->collecteur_id !== auth()->id()) {
            return redirect()->route('paiements.show', $paiement->id)
                ->with('error', "Vous ne pouvez finaliser que vos propres paiements.");
        }

        // Vérifier que le paiement peut être complété
        if ($paiement->statut !== 'en_attente' || $paiement->montant_restant <= 0) {
            return redirect()->route('paiements.show', $paiement)
                ->with('error', 'Ce paiement ne peut pas être complété.');
        }

        $rules = [
            'montant_supplementaire' => 'required|numeric|min:0.01|max:' . $paiement->montant_restant,
            'mode_paiement' => 'required|in:cash,cheque,virement,mobile_money',
            'reference_transaction' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:500',
            // Champs de géolocalisation pour les collecteurs
            'payment_latitude' => 'nullable|numeric|between:-90,90',
            'payment_longitude' => 'nullable|numeric|between:-180,180',
            'payment_accuracy' => 'nullable|numeric|min:0'
        ];

        // Pour les collecteurs, géolocalisation obligatoire
        if (auth()->user()->role === 'collecteur') {
            $rules['payment_latitude'] = 'required|numeric|between:-90,90';
            $rules['payment_longitude'] = 'required|numeric|between:-180,180';
            $rules['payment_accuracy'] = 'required|numeric|min:0|max:5000';
        }

        $validated = $request->validate($rules);

        // Calculer les nouveaux montants
        $nouveauMontantAvance = $paiement->montant_avance + $validated['montant_supplementaire'];
        $nouveauMontantRestant = $paiement->montant_restant - $validated['montant_supplementaire'];

        // Mettre à jour le paiement
        $updateData = [
            'montant_avance' => $nouveauMontantAvance,
            'montant_restant' => $nouveauMontantRestant,
            'statut' => $nouveauMontantRestant <= 0 ? 'valide' : 'en_attente',
            'updated_at' => now()
        ];

        // Ajouter les données de géolocalisation si disponibles (pour les collecteurs)
        if (auth()->user()->role === 'collecteur' && isset($validated['payment_latitude'])) {
            try {
                $hasGeoColumns = \DB::select("
                    SELECT COUNT(*) as count 
                    FROM information_schema.columns 
                    WHERE table_schema = 'public' 
                      AND table_name = 'paiements' 
                      AND column_name = 'payment_latitude'
                ");
                
                if ($hasGeoColumns[0]->count > 0) {
                    $updateData['payment_latitude'] = $validated['payment_latitude'];
                    $updateData['payment_longitude'] = $validated['payment_longitude'];
                    $updateData['payment_accuracy'] = $validated['payment_accuracy'];
                    $updateData['payment_method'] = 'GPS';
                }
            } catch (\Exception $e) {
                \Log::warning('Erreur lors de la vérification des colonnes de géolocalisation: ' . $e->getMessage());
            }
        }

        $paiement->update($updateData);

        // Créer un log pour le paiement supplémentaire
        Log::createLog('additional_payment', [
            'paiement_id' => $paiement->id,
            'unite_id' => $paiement->unite_id,
            'montant_supplementaire' => $validated['montant_supplementaire'],
            'nouveau_montant_avance' => $nouveauMontantAvance,
            'nouveau_montant_restant' => $nouveauMontantRestant,
            'mode_paiement' => $validated['mode_paiement'],
            'reference' => $validated['reference_transaction'],
            'commentaire' => $validated['commentaire']
        ]);

        // Message de succès
        $message = $nouveauMontantRestant <= 0 
            ? 'Paiement complété avec succès ! Le montant total a été payé.'
            : 'Paiement supplémentaire enregistré. Montant restant : ' . number_format($nouveauMontantRestant, 0, ',', ' ') . ' FCFA';

        // Rediriger vers la liste des paiements avec paramètres pour mise à jour automatique
        if (auth()->user()->role === 'collecteur') {
            return redirect()->route('collecteur.paiements.index', [
                'payment_finalized' => 'true',
                'payment_id' => $paiement->id
            ])->with('success', $message);
        } else {
            return redirect()->route('paiements.index', [
                'payment_finalized' => 'true',
                'payment_id' => $paiement->id
            ])->with('success', $message);
        }
    }

    /**
     * Export paiements to CSV
     */
    public function export(Request $request)
    {
        $query = Paiement::with(['collecteur', 'unite.zone']);

        // Appliquer les mêmes filtres que pour l'index
        if (auth()->user()->role === 'collecteur') {
            $query->byZone(auth()->user()->zone_id);
        }

        if ($request->filled('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }

        if ($request->filled('mode_paiement')) {
            $query->byModePaiement($request->mode_paiement);
        }

        if ($request->filled('collecteur_id') && auth()->user()->role !== 'collecteur') {
            $query->byCollecteur($request->collecteur_id);
        }

        if ($request->filled('zone_id') && auth()->user()->role !== 'collecteur') {
            $query->byZone($request->zone_id);
        }

        $paiements = $query->orderBy('date_paiement', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="paiements_' . date('Y-m-d') . '.csv"',
        ];

        // Vérifier s'il y a des paiements à exporter
        if ($paiements->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun paiement trouvé pour la période sélectionnée.');
        }

        $callback = function() use ($paiements) {
            $file = fopen('php://output', 'w');
            
            // Ajouter BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, ['Date', 'Référence', 'Collecteur', 'Unité Commerciale', 'Zone', 'Montant (FCFA)', 'Mode de paiement', 'Statut'], ';');
            
            // Données
            foreach ($paiements as $paiement) {
                fputcsv($file, [
                    $paiement->date_paiement->format('d/m/Y'),
                    $paiement->reference_transaction ?: 'N/A',
                    $paiement->collecteur ? $paiement->collecteur->name : 'N/A',
                    $paiement->unite ? $paiement->unite->nom_complet_proprietaire : 'N/A',
                    ($paiement->unite && $paiement->unite->zone) ? $paiement->unite->zone->nom : 'N/A',
                    number_format($paiement->montant, 0, ',', ' '),
                    ucfirst(str_replace('_', ' ', $paiement->mode_paiement)),
                    ucfirst($paiement->statut)
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Report an error in payment collection
     */
    public function reportError(Request $request, Paiement $paiement)
    {
        $validated = $request->validate([
            'error_type' => 'required|in:wrong_amount,wrong_unit,wrong_date,duplicate,other',
            'description' => 'required|string|max:500',
            'proposed_correction' => 'nullable|string|max:500'
        ]);

        // Changer le statut du paiement en "correction_demandee"
        $paiement->update(['statut' => 'correction_demandee']);

        // Créer un log détaillé
        Log::createLog('payment_error_reported', [
            'paiement_id' => $paiement->id,
            'error_type' => $validated['error_type'],
            'description' => $validated['description'],
            'proposed_correction' => $validated['proposed_correction'],
            'reported_by' => auth()->user()->id,
            'original_amount' => $paiement->montant,
            'original_date' => $paiement->date_paiement,
            'unite_id' => $paiement->unite_id
        ]);

        return redirect()->route('paiements.index')
            ->with('success', 'Erreur signalée avec succès. Le gestionnaire sera notifié pour correction.');
    }

    /**
     * View correction requests (for managers)
     */
    public function correctionRequests()
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $correctionRequests = Paiement::where('statut', 'correction_demandee')
            ->with(['collecteur', 'unite'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // Récupérer les logs d'erreurs correspondants
        $errorLogs = Log::where('action', 'payment_error_reported')
            ->whereIn('details->paiement_id', $correctionRequests->pluck('id'))
            ->latest()
            ->get()
            ->keyBy('details.paiement_id');

        return view('paiements.correction-requests', compact('correctionRequests', 'errorLogs'));
    }

    /**
     * Resolve a correction request
     */
    public function resolveCorrection(Request $request, Paiement $paiement)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve_correction,reject_correction,delete_payment',
            'resolution_note' => 'nullable|string|max:500',
            'corrected_amount' => 'nullable|numeric|min:0',
            'corrected_date' => 'nullable|date'
        ]);

        switch ($validated['action']) {
            case 'approve_correction':
                // Appliquer les corrections
                $updateData = ['statut' => 'valide'];
                if ($validated['corrected_amount']) {
                    $updateData['montant'] = $validated['corrected_amount'];
                }
                if ($validated['corrected_date']) {
                    $updateData['date_paiement'] = $validated['corrected_date'];
                }
                $paiement->update($updateData);
                
                Log::createLog('payment_correction_approved', [
                    'paiement_id' => $paiement->id,
                    'resolution_note' => $validated['resolution_note'],
                    'corrected_by' => auth()->user()->id
                ]);
                
                $message = 'Correction approuvée et appliquée avec succès.';
                break;

            case 'reject_correction':
                $paiement->update(['statut' => 'valide']);
                
                Log::createLog('payment_correction_rejected', [
                    'paiement_id' => $paiement->id,
                    'resolution_note' => $validated['resolution_note'],
                    'rejected_by' => auth()->user()->id
                ]);
                
                $message = 'Demande de correction rejetée. Le paiement reste valide.';
                break;

            case 'delete_payment':
                Log::createLog('payment_deleted_after_error', [
                    'paiement_id' => $paiement->id,
                    'resolution_note' => $validated['resolution_note'],
                    'deleted_by' => auth()->user()->id,
                    'original_amount' => $paiement->montant
                ]);
                
                $paiement->delete();
                $message = 'Paiement supprimé suite à l\'erreur signalée.';
                break;
        }

        return redirect()->route('paiements.correction-requests')
            ->with('success', $message);
    }

    /**
     * Approve a correction request
     */
    public function approveCorrection(Request $request, Paiement $paiement)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'correction_note' => 'nullable|string|max:500',
            'corrected_amount' => 'nullable|numeric|min:0',
            'corrected_date' => 'nullable|date|before_or_equal:today'
        ]);

        // Préparer les données de mise à jour
        $updateData = ['statut' => 'valide'];
        $corrections = [];

        // Appliquer la correction du montant si fournie
        if ($validated['corrected_amount'] && $validated['corrected_amount'] != $paiement->montant) {
            $updateData['montant'] = $validated['corrected_amount'];
            $corrections[] = "Montant corrigé : {$paiement->montant} FCFA → {$validated['corrected_amount']} FCFA";
        }

        // Appliquer la correction de la date si fournie
        if ($validated['corrected_date'] && $validated['corrected_date'] != $paiement->date_paiement->format('Y-m-d')) {
            $updateData['date_paiement'] = $validated['corrected_date'];
            $corrections[] = "Date corrigée : {$paiement->date_paiement->format('d/m/Y')} → " . \Carbon\Carbon::parse($validated['corrected_date'])->format('d/m/Y');
        }

        // Mettre à jour le paiement
        $paiement->update($updateData);
        
        // Créer le log avec les détails des corrections
        Log::createLog('payment_correction_approved', [
            'paiement_id' => $paiement->id,
            'correction_note' => $validated['correction_note'],
            'corrections_applied' => $corrections,
            'approved_by' => auth()->user()->id,
            'original_amount' => $paiement->getOriginal('montant'),
            'new_amount' => $paiement->montant,
            'original_date' => $paiement->getOriginal('date_paiement'),
            'new_date' => $paiement->date_paiement
        ]);
        
        $message = 'Correction approuvée et appliquée avec succès.';
        if (!empty($corrections)) {
            $message .= ' Modifications : ' . implode(', ', $corrections);
        }
        
        return redirect()->route('paiements.correction-requests')
            ->with('success', $message);
    }

    /**
     * Reject a correction request
     */
    public function rejectCorrection(Request $request, Paiement $paiement)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // Remettre le paiement en statut "valide"
        $paiement->update(['statut' => 'valide']);
        
        Log::createLog('payment_correction_rejected', [
            'paiement_id' => $paiement->id,
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_by' => auth()->user()->id,
            'original_amount' => $paiement->montant
        ]);
        
        return redirect()->route('paiements.correction-requests')
            ->with('success', 'Demande de correction rejetée. Le paiement reste valide.');
    }

    /**
     * Delete an erroneous payment
     */
    public function deleteErroneous(Paiement $paiement)
    {
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }

        // Log avant suppression
        Log::createLog('payment_deleted_after_error', [
            'paiement_id' => $paiement->id,
            'deleted_by' => auth()->user()->id,
            'original_amount' => $paiement->montant,
            'original_date' => $paiement->date_paiement,
            'unite_id' => $paiement->unite_id,
            'collecteur_id' => $paiement->collecteur_id
        ]);
        
        // Supprimer le paiement
        $paiement->delete();
        
        return redirect()->route('paiements.correction-requests')
            ->with('success', 'Paiement erroné supprimé définitivement.');
    }

    /**
     * Générer et télécharger un reçu PDF professionnel.
     */
    public function receipt(Paiement $paiement)
    {
        // Vérifier l'accès pour les collecteurs
        if (auth()->user()->role === 'collecteur' && $paiement->collecteur_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $receiptService = new ReceiptService();
        return $receiptService->downloadReceipt($paiement);
    }

    /**
     * Annuler un paiement (pour les gestionnaires)
     */
    public function cancel(Paiement $paiement)
    {
        // Vérifier que l'utilisateur est un gestionnaire
        if (auth()->user()->role !== 'gestionnaire') {
            abort(403, 'Seuls les gestionnaires peuvent annuler des paiements.');
        }

        // Vérifier que le paiement peut être annulé
        if ($paiement->statut !== 'en_attente') {
            return redirect()->route('paiements.show', $paiement)
                ->with('error', 'Seuls les paiements en attente peuvent être annulés.');
        }

        // Mettre à jour le statut du paiement
        $paiement->update([
            'statut' => 'annule',
            'updated_at' => now()
        ]);

        // Créer un log pour l'annulation
        Log::createLog('payment_cancelled', [
            'paiement_id' => $paiement->id,
            'unite_id' => $paiement->unite_id,
            'montant' => $paiement->montant,
            'montant_avance' => $paiement->montant_avance,
            'montant_restant' => $paiement->montant_restant,
            'cancelled_by' => auth()->user()->name,
            'cancelled_at' => now()
        ]);

        return redirect()->route('paiements.show', $paiement)
            ->with('success', 'Paiement annulé avec succès.');
    }

    /**
     * Export des paiements en Excel (CSV).
     */
    public function exportExcel(Request $request)
    {
        $query = Paiement::with([
            'collecteur:id,name',
            'unite:id,nom_proprietaire,prenom_proprietaire,nature,zone_id',
        ]);

        if (auth()->user()->role === 'collecteur') {
            $query->byZone(auth()->user()->zone_id)->where('collecteur_id', auth()->id());
        }

        if ($request->filled('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }
        if ($request->filled('mode_paiement')) {
            $query->byModePaiement($request->mode_paiement);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('collecteur_id') && auth()->user()->role !== 'collecteur') {
            $query->byCollecteur($request->collecteur_id);
        }
        if ($request->filled('zone_id') && auth()->user()->role !== 'collecteur') {
            $query->byZone($request->zone_id);
        }
        if ($request->filled('unite_id')) {
            $query->where('unite_id', $request->unite_id);
        }

        // Recherche par nom / prénom / téléphone du propriétaire
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('unite', function ($q) use ($search) {
                $q->whereRaw('LOWER(nom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(prenom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(telephone) LIKE LOWER(?)', [$search]);
            });
        }

        $paiements = $query->orderBy('date_paiement', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="paiements.csv"',
        ];

        $callback = function () use ($paiements) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Date', 'Unité', 'Propriétaire', 'Montant', 'Mode', 'Collecteur', 'Statut']);
            foreach ($paiements as $p) {
                fputcsv($file, [
                    $p->date_paiement->format('d/m/Y'),
                    $p->unite->nature ?? '',
                    $p->unite->nom_complet_proprietaire ?? '',
                    $p->montant,
                    $p->mode_paiement,
                    $p->collecteur->name ?? '',
                    $p->statut,
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export des paiements en PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = Paiement::with([
            'collecteur:id,name',
            'unite:id,nom_proprietaire,prenom_proprietaire,nature,zone_id',
        ]);

        if (auth()->user()->role === 'collecteur') {
            $query->byZone(auth()->user()->zone_id)->where('collecteur_id', auth()->id());
        }

        if ($request->filled('date_debut')) {
            $query->where('date_paiement', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date_paiement', '<=', $request->date_fin);
        }
        if ($request->filled('mode_paiement')) {
            $query->byModePaiement($request->mode_paiement);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('collecteur_id') && auth()->user()->role !== 'collecteur') {
            $query->byCollecteur($request->collecteur_id);
        }
        if ($request->filled('zone_id') && auth()->user()->role !== 'collecteur') {
            $query->byZone($request->zone_id);
        }
        if ($request->filled('unite_id')) {
            $query->where('unite_id', $request->unite_id);
        }

        // Recherche par nom / prénom / téléphone du propriétaire
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('unite', function ($q) use ($search) {
                $q->whereRaw('LOWER(nom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(prenom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(telephone) LIKE LOWER(?)', [$search]);
            });
        }

        $paiements = $query->orderBy('date_paiement', 'desc')->get();
        $commune = auth()->user()->commune;

        $pdf = Pdf::loadView('paiements.export-pdf', compact('paiements', 'commune'));
        return $pdf->download('paiements.pdf');
    }

    /**
     * Vérifie si un paiement non annulé existe déjà pour la même UC, même taxe et même période.
     *
     * @param int $uniteId
     * @param int $taxeId
     * @param string $datePaiement
     * @param string|null $periodicite
     * @return bool
     */
    private function hasDuplicatePayment(int $uniteId, int $taxeId, string $datePaiement, ?string $periodicite): bool
    {
        $query = Paiement::where('unite_id', $uniteId)
            ->where('taxe_id', $taxeId)
            ->where('statut', '!=', 'annule');

        $date = Carbon::parse($datePaiement);

        switch ($periodicite) {
            case 'jour':
                $query->whereDate('date_paiement', $date->toDateString());
                break;
            case 'mois':
                $query->whereYear('date_paiement', $date->year)
                      ->whereMonth('date_paiement', $date->month);
                break;
            case 'an':
                $query->whereYear('date_paiement', $date->year);
                break;
            default:
                // Ponctuelle ou non définie : vérifier qu'il n'existe aucun paiement non annulé pour cette UC/taxe
                break;
        }

        return $query->exists();
    }
} 
