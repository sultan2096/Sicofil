<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\User;
use App\Models\Taxe;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\ZoneFiscale;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\RapportFinancierExport;
use Maatwebsite\Excel\Facades\Excel;

class RapportController extends Controller
{
    /**
     * Afficher le tableau de bord des rapports
     */
    public function index()
    {
        // Vérifier que l'utilisateur est maire ou gestionnaire
        $role = strtolower(auth()->user()->role ?? '');
        if (!in_array($role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès réservé au maire et au gestionnaire');
        }

        $communeId = auth()->user()->commune_id;

        // Dates (période affichée sur le dashboard)
        $generationDate = now();
        $dateRange = [
            'start' => now()->startOfMonth(),
            'end' => now()->endOfMonth(),
        ];

        // Statistiques principales sur la période
        $totalUnitesActives = UniteCommerciale::actif()->count();
        $unitesPayeesPeriode = UniteCommerciale::actif()
            ->whereHas('paiements', function ($q) use ($dateRange) {
                $q->where('statut', 'valide')
                  ->whereBetween('date_paiement', [$dateRange['start'], $dateRange['end']]);
            })
            ->count();
        $unitesImpayeesPeriode = max(0, $totalUnitesActives - $unitesPayeesPeriode);

        $totalPaiementsPeriode = Paiement::where('statut', 'valide')
            ->whereBetween('date_paiement', [$dateRange['start'], $dateRange['end']])
            ->count();
        $totalMontantPeriode = Paiement::where('statut', 'valide')
            ->whereBetween('date_paiement', [$dateRange['start'], $dateRange['end']])
            ->sum('montant');

        // Statistiques générales pour le dashboard
        $stats = [
            'total_unites' => $totalUnitesActives,
            // Pour la période affichée (mois en cours)
            'payees' => $unitesPayeesPeriode,
            'impayees' => $unitesImpayeesPeriode,
            'total_montant' => $totalMontantPeriode,
            'total_paiements' => $totalPaiementsPeriode,
            'total_recettes' => $totalMontantPeriode,
            'total_collecteurs' => User::where('role', 'collecteur')->count(),
            'total_zones' => ZoneFiscale::count(),
            'total_taxes' => Taxe::count(),
        ];

        // Données pour les graphiques - Évolution des recettes 2025
        $recettes_par_mois = Paiement::where('statut', 'valide')
            ->whereYear('date_paiement', now()->year)
            ->selectRaw('EXTRACT(MONTH FROM date_paiement) as mois, SUM(montant) as total')
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Données pour les graphiques - Répartition par Zone
        $paiements_par_zone = DB::table('paiements')
            ->join('unites_commerciales', 'paiements.unite_id', '=', 'unites_commerciales.id')
            ->join('zones_fiscales', 'unites_commerciales.zone_id', '=', 'zones_fiscales.id')
            ->where('paiements.statut', 'valide')
            ->where('paiements.commune_id', $communeId)
            ->selectRaw('zones_fiscales.nom as zone, COUNT(*) as nombre, SUM(paiements.montant) as total')
            ->groupBy('zones_fiscales.id', 'zones_fiscales.nom')
            ->get();

        // Données pour les graphiques - Performance des collecteurs
        $performanceCollecteurs = User::where('role', 'collecteur')
            ->withSum(['paiementsCollectes' => function($q) {
                $q->where('statut', 'valide');
            }], 'montant')
            ->withCount(['paiementsCollectes' => function($q) {
                $q->where('statut', 'valide');
            }])
            ->orderBy('paiements_collectes_sum_montant', 'desc')
            ->limit(10)
            ->get();

        // Données pour les graphiques - Performance par zone
        $performanceZones = DB::table('zones_fiscales')
            ->leftJoin('unites_commerciales', function ($join) {
                $join->on('zones_fiscales.id', '=', 'unites_commerciales.zone_id')
                     ->where('unites_commerciales.actif', true);
            })
            ->leftJoin('paiements', function ($join) {
                $join->on('unites_commerciales.id', '=', 'paiements.unite_id')
                     ->where('paiements.statut', '=', 'valide');
            })
            ->where('zones_fiscales.commune_id', $communeId)
            ->select(
                'zones_fiscales.id',
                'zones_fiscales.nom',
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements')
            )
            ->groupBy('zones_fiscales.id', 'zones_fiscales.nom')
            ->orderBy('total_recettes', 'desc')
            ->get();

        // Données pour les graphiques - Répartition des recettes par type de taxe
        $repartitionTaxes = DB::table('taxes')
            ->leftJoin('unites_commerciales', function ($join) {
                $join->on('taxes.id', '=', 'unites_commerciales.taxe_id')
                     ->where('unites_commerciales.actif', true);
            })
            ->leftJoin('paiements', function ($join) {
                $join->on('unites_commerciales.id', '=', 'paiements.unite_id')
                     ->where('paiements.statut', '=', 'valide');
            })
            ->where('taxes.commune_id', $communeId)
            ->select(
                'taxes.id',
                'taxes.nom',
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements')
            )
            ->groupBy('taxes.id', 'taxes.nom')
            ->orderBy('total_recettes', 'desc')
            ->get();

        // Données pour les graphiques - Répartition des unités par zone
        $unitesParZone = DB::table('zones_fiscales')
            ->leftJoin('unites_commerciales', function ($join) {
                $join->on('zones_fiscales.id', '=', 'unites_commerciales.zone_id')
                     ->where('unites_commerciales.actif', true);
            })
            ->where('zones_fiscales.commune_id', $communeId)
            ->select(
                'zones_fiscales.id',
                'zones_fiscales.nom',
                DB::raw('COUNT(unites_commerciales.id) as nombre_unites')
            )
            ->groupBy('zones_fiscales.id', 'zones_fiscales.nom')
            ->orderBy('nombre_unites', 'desc')
            ->get();

        // Données pour un éventuel graphique - Statut de paiement (période courante)
        $statutPaiement = [
            'payantes' => $unitesPayeesPeriode,
            'non_payantes' => $unitesImpayeesPeriode,
            'total' => $totalUnitesActives,
        ];

        return view('rapports.index', compact(
            'stats', 
            'recettes_par_mois', 
            'paiements_par_zone',
            'performanceCollecteurs',
            'performanceZones',
            'repartitionTaxes',
            'unitesParZone',
            'statutPaiement',
            'generationDate',
            'dateRange'
        ));
    }

    /**
     * Générer un rapport financier
     */
    public function financier(Request $request)
    {
        // Version simple pour diagnostic
        $generationDate = now();
        $stats = [
            'periode' => '01/01/2025 au 31/01/2025',
            'total_paiements' => 25,
            'total_montant' => 1250000,
            'moyenne_paiement' => 50000,
            'zone' => 'Toutes les zones',
            'generation_date' => $generationDate->format('d/m/Y à H:i'),
            'periode_debut' => '01/01/2025',
            'periode_fin' => '31/01/2025'
        ];

        $par_zone = collect([
            (object)['nom' => 'Zone 1'],
            (object)['nom' => 'Zone 2'],
            (object)['nom' => 'Zone 3']
        ]);

        $paiements = collect([]); // Collection vide pour test

        $data = compact('stats', 'par_zone', 'paiements');

        // Test format d'export
        if ($request->get('format') === 'excel') {
            return $this->generateExcel($data, 'test_rapport');
        }

        if ($request->get('format') === 'pdf') {
            // Rediriger vers le rapport avec camemberts pour le PDF
            return redirect('/rapports/financier-camembert?' . http_build_query($request->all()));
        }

        return view('rapports.financier', $data);
    }

    /**
     * Générer un rapport d'activité des collecteurs
     */
    public function collecteurs(Request $request)
    {
        // Validation des paramètres
        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'collecteur_id' => 'nullable|exists:users,id',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        // Déterminer la période
        $dateDebut = $request->date_debut ? Carbon::parse($request->date_debut) : Carbon::now()->startOfMonth();
        $dateFin = $request->date_fin ? Carbon::parse($request->date_fin) : Carbon::now()->endOfMonth();

        // Récupérer les vraies données des collecteurs
        $query = User::where('role', 'collecteur')
                    ->withSum(['paiementsCollectes' => function($q) use ($dateDebut, $dateFin) {
                        $q->whereBetween('date_paiement', [$dateDebut, $dateFin])
                          ->where('statut', 'valide');
                    }], 'montant')
                    ->withCount(['paiementsCollectes' => function($q) use ($dateDebut, $dateFin) {
                        $q->whereBetween('date_paiement', [$dateDebut, $dateFin])
                          ->where('statut', 'valide');
                    }]);

        if ($request->zone_id) {
            $query->whereHas('paiementsCollectes.unite', function($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        if ($request->collecteur_id) {
            $query->where('id', $request->collecteur_id);
        }

        $collecteurs = $query->orderBy('paiements_collectes_sum_montant', 'desc')->get();

        // Calculer les statistiques
        $totalPaiements = $collecteurs->sum('paiements_collectes_count');
        $totalMontant = $collecteurs->sum('paiements_collectes_sum_montant');
        $totalCollecteurs = $collecteurs->count();

        $stats = [
            'periode' => $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'),
            'total_collecteurs' => $totalCollecteurs,
            'total_paiements' => $totalPaiements,
            'total_montant' => $totalMontant,
            'moyenne_par_collecteur' => $totalCollecteurs > 0 ? $totalMontant / $totalCollecteurs : 0
        ];

        // Préparer les données des collecteurs avec calculs de performance
        $maxMontant = $collecteurs->max('paiements_collectes_sum_montant');
        $stats_collecteurs = $collecteurs->map(function($collecteur) use ($maxMontant) {
            $montantTotal = $collecteur->paiements_collectes_sum_montant ?? 0;
            $nombrePaiements = $collecteur->paiements_collectes_count ?? 0;
            
            return [
                'collecteur' => $collecteur,
                'nombre_paiements' => $nombrePaiements,
                'montant_total' => $montantTotal,
                'moyenne_par_paiement' => $nombrePaiements > 0 ? $montantTotal / $nombrePaiements : 0,
                'performance_pct' => $maxMontant > 0 ? ($montantTotal / $maxMontant) * 100 : 0
            ];
        })->toArray();

        $format = $request->format ?? 'html';

        // Préparer les données pour la vue
        $data = compact('stats', 'stats_collecteurs');

        // Génération selon le format demandé
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.collecteurs_html', $data, 'rapport_collecteurs_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'rapport_collecteurs_' . now()->format('Y-m-d'));
            default:
                return view('rapports.collecteurs_html', $data);
        }
    }

    /**
     * Générer un rapport des unités commerciales
     */
    public function unites(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'statut' => 'nullable|in:actif,inactif',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        // Déterminer la période pour les paiements
        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        // Récupération des unités avec leurs paiements sur la période
        $query = UniteCommerciale::with([
            'zone', 
            'taxe', 
            'paiements' => function($q) use ($dateDebut, $dateFin) {
                $q->where('statut', 'valide')
                  ->whereBetween('date_paiement', [$dateDebut, $dateFin]);
            }
        ]);

        if ($validated['zone_id'] ?? null) {
            $query->where('zone_id', $validated['zone_id']);
        }

        if ($validated['statut'] ?? null) {
            $query->where('actif', $validated['statut'] === 'actif');
        }

        $unites = $query->orderBy('nom_proprietaire')->orderBy('prenom_proprietaire')->get();

        // Statistiques détaillées
        $stats = [
            'total_unites' => $unites->count(),
            'unites_actives' => $unites->where('actif', true)->count(),
            'unites_inactives' => $unites->where('actif', false)->count(),
            'avec_paiement_periode' => $unites->filter(function($unite) {
                return $unite->paiements->count() > 0;
            })->count(),
            'montant_total_collecte' => $unites->sum(function($unite) {
                return $unite->paiements->sum('montant');
            }),
            'zone' => ($validated['zone_id'] ?? null) ? ZoneFiscale::find($validated['zone_id'])->nom : 'Toutes les zones',
            'periode' => $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y')
        ];

        // Répartition par type d'activité
        $stats['par_activite'] = $unites->groupBy('type_activite')->map(function($group, $activite) {
            return [
                'activite' => $activite ?: 'Non spécifié',
                'nombre' => $group->count(),
                'actives' => $group->where('actif', true)->count(),
                'montant_collecte' => $group->sum(function($unite) {
                    return $unite->paiements->sum('montant');
                })
            ];
        })->values();

        // Répartition par zone (si toutes les zones sont sélectionnées)
        if (!$validated['zone_id']) {
            $stats['par_zone'] = $unites->groupBy('zone.nom')->map(function($group, $zone) {
                return [
                    'zone' => $zone ?: 'Zone non définie',
                    'nombre' => $group->count(),
                    'actives' => $group->where('actif', true)->count(),
                    'montant_collecte' => $group->sum(function($unite) {
                        return $unite->paiements->sum('montant');
                    })
                ];
            })->values();
        }

        $format = $validated['format'] ?? 'html';
        $data = compact('unites', 'stats');

        // Génération selon le format demandé
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.unites_html', $data, 'rapport_unites_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'rapport_unites_' . now()->format('Y-m-d'));
            default:
                return view('rapports.unites_html', $data);
        }
    }

    /**
     * Générer un PDF avec logo SICOSA pour le rapport financier
     */
    public function financierPdf(Request $request)
    {
        // Données pour le PDF
        $stats = [
            'periode' => $request->get('date_debut', '01/01/2025') . ' au ' . $request->get('date_fin', '31/01/2025'),
            'zone' => $request->get('zone', 'Toutes les zones'),
            'total_paiements' => 156,
            'total_montant' => 2450000,
            'moyenne_paiement' => 15705
        ];

        $paiements = collect([
            (object) [
                'date_paiement' => now(),
                'unite' => (object) [
                    'nom_commercial' => 'Boutique Alpha', 
                    'nom_proprietaire' => 'Moussa Diop', 
                    'zone' => (object) ['nom' => 'Centre-ville']
                ],
                'collecteur' => (object) ['name' => 'Ahmed Sow'],
                'montant' => 25000
            ],
            (object) [
                'date_paiement' => now()->subDays(1),
                'unite' => (object) [
                    'nom_commercial' => 'Pharmacie Wellness', 
                    'nom_proprietaire' => 'Dr. Fatou Kane', 
                    'zone' => (object) ['nom' => 'Marché']
                ],
                'collecteur' => (object) ['name' => 'Mamadou Ba'],
                'montant' => 45000
            ],
            (object) [
                'date_paiement' => now()->subDays(2),
                'unite' => (object) [
                    'nom_commercial' => 'Restaurant Teranga', 
                    'nom_proprietaire' => 'Ibrahima Fall', 
                    'zone' => (object) ['nom' => 'Centre-ville']
                ],
                'collecteur' => (object) ['name' => 'Ahmed Sow'],
                'montant' => 35000
            ]
        ]);

        $par_zone = collect(['Centre-ville', 'Marché', 'Périphérie']);

        // Retourner la vue PDF avec logo
        return view('rapports.financier_pdf', compact('stats', 'paiements', 'par_zone'));
    }

    /**
     * Générer un PDF
     */
    private function generatePDF($view, $data, $filename)
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Générer un fichier Excel
     */
    private function generateExcel($data, $filename)
    {
        // Générer un CSV simple
        $output = '';
        $headers = [];
        
        if (isset($data['paiements']) && $data['paiements']->count() > 0) {
            // Format CSV pour rapport financier
            $headers = ['Date', 'Unité Commerciale', 'Propriétaire', 'Zone', 'Collecteur', 'Montant'];
            $output = implode(';', $headers) . "\n";
            
            foreach ($data['paiements'] as $paiement) {
                $row = [
                    $paiement->date_paiement->format('d/m/Y'),
                    $paiement->unite->nom_commercial ?? 'N/A',
                    $paiement->unite->nom_complet_proprietaire ?? 'N/A',
                    $paiement->unite->zone->nom ?? 'N/A',
                    $paiement->collecteur->name ?? 'N/A',
                    $paiement->montant
                ];
                $output .= implode(';', $row) . "\n";
            }
        } elseif (isset($data['unites']) && $data['unites']->count() > 0) {
            // Format CSV pour rapport unités
            $headers = ['Nom Commercial', 'Propriétaire', 'Zone', 'Activité', 'Statut', 'Téléphone'];
            $output = implode(';', $headers) . "\n";
            
            foreach ($data['unites'] as $unite) {
                $row = [
                    $unite->nom_commercial ?? 'N/A',
                    $unite->nom_complet_proprietaire ?? 'N/A',
                    $unite->zone->nom ?? 'N/A',
                    $unite->type_activite ?? 'N/A',
                    $unite->actif ? 'Actif' : 'Inactif',
                    $unite->telephone ?? 'N/A'
                ];
                $output .= implode(';', $row) . "\n";
            }
        } elseif (isset($data['donnees']) && count($data['donnees']) > 0) {
            $donnees = $data['donnees'];
            $first = (array) ($donnees[0] ?? null);
            if (!$first) {
                $output = "Aucune donnée à exporter\n";
            } else {
                $labels = [
                    'nature' => 'Nature',
                    'type_commerce' => 'Type de commerce',
                    'categorie' => 'Catégorie',
                    'nombre_unites' => 'Nombre unités',
                    'nombre_paiements' => 'Nombre paiements',
                    'total_recettes' => 'Total recettes',
                    'montant_moyen' => 'Montant moyen',
                ];
                $headers = [];
                foreach (array_keys($first) as $key) {
                    $headers[] = $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                }
                $output = implode(';', $headers) . "\n";
                foreach ($donnees as $item) {
                    $row = [];
                    foreach (array_keys($first) as $key) {
                        $val = ((array) $item)[$key] ?? '';
                        $row[] = is_numeric($val) ? number_format($val, 2, ',', '') : $val;
                    }
                    $output .= implode(';', $row) . "\n";
                }
                if (isset($data['stats']) && is_array($data['stats'])) {
                    $output .= "\n";
                    foreach ($data['stats'] as $key => $value) {
                        $output .= (ucfirst(str_replace('_', ' ', $key)) . ';' . $value . "\n");
                    }
                }
            }
        } else {
            $output = "Aucune donnée à exporter\n";
        }

        return response($output)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
            ->header('Content-Description', 'File Transfer');
    }

    /**
     * Page de diagnostic pour tester le rendu Blade et le logo
     */
    public function diagnostic()
    {
        // Données de test complètes
        $stats = [
            'periode' => '01/01/2025 au 31/01/2025',
            'zone' => 'Toutes les zones',
            'total_paiements' => 156,
            'total_montant' => 2450000,
            'moyenne_paiement' => 15705
        ];

        $paiements = collect([
            (object) [
                'date_paiement' => now(),
                'unite' => (object) [
                    'nom_commercial' => 'Boutique Alpha', 
                    'nom_proprietaire' => 'Moussa Diop', 
                    'zone' => (object) ['nom' => 'Centre-ville']
                ],
                'collecteur' => (object) ['name' => 'Ahmed Sow'],
                'montant' => 25000
            ],
            (object) [
                'date_paiement' => now()->subDays(1),
                'unite' => (object) [
                    'nom_commercial' => 'Pharmacie Wellness', 
                    'nom_proprietaire' => 'Dr. Fatou Kane', 
                    'zone' => (object) ['nom' => 'Marché']
                ],
                'collecteur' => (object) ['name' => 'Mamadou Ba'],
                'montant' => 45000
            ]
        ]);

        $par_zone = collect(['Centre-ville', 'Marché', 'Périphérie']);

        return view('rapports.diagnostic', compact('stats', 'paiements', 'par_zone'));
    }

    /**
     * Rapport financier moderne avec graphiques interactifs
     */
    public function financierModerne(Request $request)
    {
        // Données enrichies pour le rapport moderne
        $stats = [
            'periode' => $request->get('date_debut', '01/01/2025') . ' au ' . $request->get('date_fin', '31/01/2025'),
            'zone' => $request->get('zone', 'Toutes les zones'),
            'total_paiements' => 156,
            'total_montant' => 2450000,
            'moyenne_paiement' => 15705,
            'marge_brute' => 79,
            'marge_operationnelle' => 30,
            'marge_nette' => 12
        ];

        // Données détaillées des paiements
        $paiements = collect([
            (object) [
                'date_paiement' => now(),
                'unite' => (object) [
                    'nom_commercial' => 'Boutique Alpha', 
                    'nom_proprietaire' => 'Moussa Diop', 
                    'zone' => (object) ['nom' => 'Centre-ville']
                ],
                'collecteur' => (object) ['name' => 'Ahmed Sow'],
                'montant' => 25000
            ],
            (object) [
                'date_paiement' => now()->subDays(1),
                'unite' => (object) [
                    'nom_commercial' => 'Pharmacie Wellness', 
                    'nom_proprietaire' => 'Dr. Fatou Kane', 
                    'zone' => (object) ['nom' => 'Marché']
                ],
                'collecteur' => (object) ['name' => 'Mamadou Ba'],
                'montant' => 45000
            ],
            (object) [
                'date_paiement' => now()->subDays(2),
                'unite' => (object) [
                    'nom_commercial' => 'Restaurant Teranga', 
                    'nom_proprietaire' => 'Ibrahima Fall', 
                    'zone' => (object) ['nom' => 'Centre-ville']
                ],
                'collecteur' => (object) ['name' => 'Ahmed Sow'],
                'montant' => 35000
            ],
            (object) [
                'date_paiement' => now()->subDays(3),
                'unite' => (object) [
                    'nom_commercial' => 'Épicerie Moderne', 
                    'nom_proprietaire' => 'Aminata Diallo', 
                    'zone' => (object) ['nom' => 'Marché']
                ],
                'collecteur' => (object) ['name' => 'Mamadou Ba'],
                'montant' => 18000
            ],
            (object) [
                'date_paiement' => now()->subDays(4),
                'unite' => (object) [
                    'nom_commercial' => 'Salon de Coiffure Elite', 
                    'nom_proprietaire' => 'Khady Ndiaye', 
                    'zone' => (object) ['nom' => 'Périphérie']
                ],
                'collecteur' => (object) ['name' => 'Fatou Sow'],
                'montant' => 22000
            ]
        ]);

        $par_zone = collect(['Centre-ville', 'Marché', 'Périphérie']);

        // Données pour les graphiques
        $données_graphiques = [
            'trésorerie_mensuelle' => [15000, 18000, 22000, 19000, 25000],
            'recettes_prévues' => [120000, 135000, 140000, 145000, 150000],
            'recettes_réelles' => [115000, 132000, 138000, 142000, 148000],
            'répartition_coûts' => [40, 25, 15, 12, 8]
        ];

        return view('rapports.financier_moderne', compact('stats', 'paiements', 'par_zone', 'données_graphiques'));
    }

    /**
     * Rapport financier avec graphiques camemberts (format PDF)
     */
    public function financierCamembert(Request $request)
    {
        // Données pour le rapport avec camemberts
        $stats = [
            'periode' => $request->get('date_debut', '01/01/2025') . ' au ' . $request->get('date_fin', '31/01/2025'),
            'zone' => $request->get('zone', 'Toutes les zones'),
            'total_depenses' => 3194070,
            'total_recettes' => 2614854,
            'total_paiements' => 156,
            'total_montant' => 2450000
        ];

        // Données détaillées des dépenses
        $depenses = [
            ['activite' => 'Collecte des taxes', 'montant' => 584310624, 'pourcentage' => 28, 'couleur' => '#4a90e2'],
            ['activite' => 'Autres subventions', 'montant' => 432110792, 'pourcentage' => 21, 'couleur' => '#50c878'],
            ['activite' => 'Autres charges externes', 'montant' => 432110792, 'pourcentage' => 21, 'couleur' => '#ff6b6b'],
            ['activite' => 'Charges de personnel', 'montant' => 358416064, 'pourcentage' => 17, 'couleur' => '#ffa726'],
            ['activite' => 'Autres charges', 'montant' => 108667376, 'pourcentage' => 5, 'couleur' => '#ab47bc'],
            ['activite' => 'Charges financières', 'montant' => 126129120, 'pourcentage' => 6, 'couleur' => '#26a69a'],
            ['activite' => 'Dotations aux amortissements', 'montant' => 53822832, 'pourcentage' => 3, 'couleur' => '#78909c']
        ];

        // Données détaillées des recettes
        $recettes = [
            ['activite' => 'Ventes', 'montant' => 429582256, 'pourcentage' => 25, 'couleur' => '#e53e3e'],
            ['activite' => 'Conseil départemental', 'montant' => 768461280, 'pourcentage' => 45, 'couleur' => '#38a169'],
            ['activite' => 'CAF', 'montant' => 128842320, 'pourcentage' => 8, 'couleur' => '#3182ce'],
            ['activite' => 'État', 'montant' => 85827776, 'pourcentage' => 5, 'couleur' => '#d69e2e'],
            ['activite' => 'Conseil régional', 'montant' => 85827776, 'pourcentage' => 5, 'couleur' => '#805ad5'],
            ['activite' => 'Autres (prestations formation, etc.)', 'montant' => 216963024, 'pourcentage' => 13, 'couleur' => '#319795']
        ];

        $paiements = collect([
            (object) [
                'date_paiement' => now(),
                'unite' => (object) [
                    'nom_commercial' => 'Boutique Alpha', 
                    'nom_proprietaire' => 'Moussa Diop', 
                    'zone' => (object) ['nom' => 'Centre-ville']
                ],
                'collecteur' => (object) ['name' => 'Ahmed Sow'],
                'montant' => 25000
            ]
        ]);

        $par_zone = collect(['Centre-ville', 'Marché', 'Périphérie']);

        return view('rapports.financier_camembert', compact('stats', 'depenses', 'recettes', 'paiements', 'par_zone'));
    }

    /**
     * Générer un rapport financier complet en PDF, HTML ou Excel
     */
    public function rapportFinancier(Request $request)
    {
        // Validation des paramètres
        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'periode' => 'nullable|in:jour,semaine,mois,trimestre,annee',
            'format' => 'required|in:pdf,html,excel'
        ]);

        // Déterminer la période
        $dateDebut = $request->date_debut ? Carbon::parse($request->date_debut) : Carbon::now()->startOfMonth();
        $dateFin = $request->date_fin ? Carbon::parse($request->date_fin) : Carbon::now()->endOfMonth();
        
        // Si une période prédéfinie est sélectionnée
        if ($request->periode) {
            switch ($request->periode) {
                case 'jour':
                    $dateDebut = Carbon::now()->startOfDay();
                    $dateFin = Carbon::now()->endOfDay();
                    break;
                case 'semaine':
                    $dateDebut = Carbon::now()->startOfWeek();
                    $dateFin = Carbon::now()->endOfWeek();
                    break;
                case 'mois':
                    $dateDebut = Carbon::now()->startOfMonth();
                    $dateFin = Carbon::now()->endOfMonth();
                    break;
                case 'trimestre':
                    $dateDebut = Carbon::now()->startOfQuarter();
                    $dateFin = Carbon::now()->endOfQuarter();
                    break;
                case 'annee':
                    $dateDebut = Carbon::now()->startOfYear();
                    $dateFin = Carbon::now()->endOfYear();
                    break;
            }
        }

        // 1. Résumé des recettes par période
        $recettesParPeriode = $this->getRecettesParPeriode($dateDebut, $dateFin, $request->zone_id);
        
        // 2. Répartition par zone fiscale
        $recettesParZone = $this->getRecettesParZone($dateDebut, $dateFin);
        
        // 3. Performance des collecteurs
        $performanceCollecteurs = $this->getPerformanceCollecteurs($dateDebut, $dateFin, $request->zone_id);
        
        // 4. Statistiques détaillées
        $statistiques = $this->getStatistiquesDetaillees($dateDebut, $dateFin, $request->zone_id);
        
        // 5. Évolution des recettes (pour graphiques)
        $evolutionRecettes = $this->getEvolutionRecettes($dateDebut, $dateFin, $request->zone_id);

        // Données pour le PDF
        $data = [
            'titre' => 'Rapport Financier - SICOSA',
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
                'libelle' => $this->getPeriodeLibelle($request->periode, $dateDebut, $dateFin)
            ],
            'zone_selectionnee' => $request->zone_id ? ZoneFiscale::find($request->zone_id) : null,
            'recettes_periode' => $recettesParPeriode,
            'recettes_zones' => $recettesParZone,
            'performance_collecteurs' => $performanceCollecteurs,
            'statistiques' => $statistiques,
            'evolution_recettes' => $evolutionRecettes,
            'date_generation' => Carbon::now()->format('d/m/Y à H:i')
        ];

        // Nom du fichier de base
        $nomFichier = 'rapport-financier-' . $dateDebut->format('Y-m-d') . '-au-' . $dateFin->format('Y-m-d');
        
        // Générer selon le format demandé
        switch ($request->format) {
            case 'pdf':
                $pdf = Pdf::loadView('rapports.rapport-financier', $data);
                $pdf->setPaper('A4', 'portrait');
                return $pdf->download($nomFichier . '.pdf');
                
            case 'html':
                return view('rapports.rapport-html', $data);
                
            case 'excel':
                return Excel::download(new RapportFinancierExport($data), $nomFichier . '.xlsx');
                
            default:
                // Par défaut, retourner le PDF
                $pdf = Pdf::loadView('rapports.rapport-financier', $data);
                $pdf->setPaper('A4', 'portrait');
                return $pdf->download($nomFichier . '.pdf');
        }
    }

    /**
     * Afficher le formulaire de génération de rapport
     */
    public function formulaireRapport()
    {
        $zones = ZoneFiscale::orderBy('nom')->get();
        return view('rapports.formulaire', compact('zones'));
    }

    /**
     * Obtenir les recettes par période
     */
    private function getRecettesParPeriode($dateDebut, $dateFin, $zoneId = null)
    {
        $query = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
                         ->where('statut', 'valide');
        
        if ($zoneId) {
            $query->whereHas('unite.zoneFiscale', function($q) use ($zoneId) {
                $q->where('id', $zoneId);
            });
        }

        $totalRecettes = $query->sum('montant');
        $nombrePaiements = $query->count();
        $montantMoyen = $nombrePaiements > 0 ? $totalRecettes / $nombrePaiements : 0;

        // Recettes par type de taxe
        $recettesParTaxe = $query->join('taxes', 'paiements.taxe_id', '=', 'taxes.id')
                                ->select('taxes.nom', 'taxes.taux', DB::raw('SUM(paiements.montant) as total'))
                                ->groupBy('taxes.id', 'taxes.nom', 'taxes.taux')
                                ->orderBy('total', 'desc')
                                ->get();

        return [
            'total' => $totalRecettes,
            'nombre_paiements' => $nombrePaiements,
            'montant_moyen' => $montantMoyen,
            'par_taxe' => $recettesParTaxe
        ];
    }

    /**
     * Obtenir les recettes par zone fiscale
     */
    private function getRecettesParZone($dateDebut, $dateFin, $zoneId = null)
    {
        $query = ZoneFiscale::withSum(['paiements' => function($query) use ($dateDebut, $dateFin) {
            $query->whereBetween('date_paiement', [$dateDebut, $dateFin])
                  ->where('paiements.statut', 'valide');
        }], 'montant')
        ->withCount(['paiements' => function($query) use ($dateDebut, $dateFin) {
            $query->whereBetween('date_paiement', [$dateDebut, $dateFin])
                  ->where('paiements.statut', 'valide');
        }]);

        if ($zoneId) {
            $query->where('id', $zoneId);
        }

        return $query->orderBy('paiements_sum_montant', 'desc')->get();
    }

    /**
     * Obtenir la performance des collecteurs
     */
    private function getPerformanceCollecteurs($dateDebut, $dateFin, $zoneId = null)
    {
        $query = User::where('role', 'collecteur')
                    ->withSum(['paiementsCollectes' => function($q) use ($dateDebut, $dateFin) {
                        $q->whereBetween('date_paiement', [$dateDebut, $dateFin])
                          ->where('paiements.statut', 'valide');
                    }], 'montant')
                    ->withCount(['paiementsCollectes' => function($q) use ($dateDebut, $dateFin) {
                        $q->whereBetween('date_paiement', [$dateDebut, $dateFin])
                          ->where('paiements.statut', 'valide');
                    }]);

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        return $query->orderBy('paiements_collectes_sum_montant', 'desc')->get();
    }

    /**
     * Obtenir les statistiques détaillées
     */
    private function getStatistiquesDetaillees($dateDebut, $dateFin, $zoneId = null)
    {
        // Requête de base
        $baseQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin]);
        if ($zoneId) {
            $baseQuery->whereHas('unite.zoneFiscale', function($q) use ($zoneId) {
                $q->where('id', $zoneId);
            });
        }

        // Cloner pour chaque count afin d'éviter l'accumulation des clauses WHERE
        $totalPaiements = (clone $baseQuery)->count();
        $paiementsValides = (clone $baseQuery)->where('statut', 'valide')->count();
        $paiementsAnnules = (clone $baseQuery)->where('statut', 'annule')->count();
        $tauxReussite = $totalPaiements > 0 ? ($paiementsValides / $totalPaiements) * 100 : 0;

        // Recettes totales et moyennes
        $recettesQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
                                ->where('statut', 'valide');
        if ($zoneId) {
            $recettesQuery->whereHas('unite.zoneFiscale', function($q) use ($zoneId) {
                $q->where('id', $zoneId);
            });
        }
        $totalRecettes = $recettesQuery->sum('montant');
        $moyenneParPaiement = $paiementsValides > 0 ? $totalRecettes / $paiementsValides : 0;

        // Unités commerciales actives
        $unitesActives = UniteCommerciale::whereHas('paiements', function($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_paiement', [$dateDebut, $dateFin])
              ->where('statut', 'valide');
        })->count();

        $totalUnites = UniteCommerciale::where('actif', true)->count();
        $tauxParticipation = $totalUnites > 0 ? ($unitesActives / $totalUnites) * 100 : 0;

        return [
            'total_paiements' => $totalPaiements,
            'paiements_valides' => $paiementsValides,
            'paiements_annules' => $paiementsAnnules,
            'total_recettes' => $totalRecettes,
            'moyenne_par_paiement' => $moyenneParPaiement,
            'taux_reussite' => $tauxReussite,
            'unites_actives' => $unitesActives,
            'total_unites' => $totalUnites,
            'taux_participation' => $tauxParticipation,
            'taux_contribution' => $tauxParticipation
        ];
    }

    /**
     * Obtenir l'évolution des recettes (pour graphiques)
     */
    private function getEvolutionRecettes($dateDebut, $dateFin, $zoneId = null)
    {
        $query = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
                         ->where('statut', 'valide');
        
        if ($zoneId) {
            $query->whereHas('unite.zoneFiscale', function($q) use ($zoneId) {
                $q->where('id', $zoneId);
            });
        }

        // Expressions de période compatibles avec le driver
        $driver = DB::getDriverName();
        $periodeJour = 'DATE(date_paiement)';
        $periodeSemaine = $driver === 'pgsql'
            ? "to_char(date_paiement, 'IYYY-IW')"
            : 'YEARWEEK(date_paiement)';
        $periodeMois = $driver === 'pgsql'
            ? "to_char(date_paiement, 'YYYY-MM')"
            : "DATE_FORMAT(date_paiement, '%Y-%m')";

        // Grouper par jour si la période est courte, sinon par semaine ou mois
        $diffJours = $dateDebut->diffInDays($dateFin);
        
        if ($diffJours <= 31) {
            // Par jour
            return $query->select(
                DB::raw($periodeJour . ' as periode'),
                DB::raw('SUM(montant) as total'),
                DB::raw('COUNT(*) as nombre')
            )
            ->groupBy(DB::raw($periodeJour))
            ->orderBy('periode')
            ->get();
        } elseif ($diffJours <= 365) {
            // Par semaine
            return $query->select(
                DB::raw($periodeSemaine . ' as periode'),
                DB::raw('SUM(montant) as total'),
                DB::raw('COUNT(*) as nombre')
            )
            ->groupBy(DB::raw($periodeSemaine))
            ->orderBy('periode')
            ->get();
        } else {
            // Par mois
            return $query->select(
                DB::raw($periodeMois . ' as periode'),
                DB::raw('SUM(montant) as total'),
                DB::raw('COUNT(*) as nombre')
            )
            ->groupBy(DB::raw($periodeMois))
            ->orderBy('periode')
            ->get();
        }
    }

    /**
     * Obtenir le libellé de la période
     */
    private function getPeriodeLibelle($periode, $dateDebut, $dateFin)
    {
        switch ($periode) {
            case 'jour':
                return 'Aujourd\'hui (' . $dateDebut->format('d/m/Y') . ')';
            case 'semaine':
                return 'Cette semaine (' . $dateDebut->format('d/m') . ' au ' . $dateFin->format('d/m/Y') . ')';
            case 'mois':
                return $dateDebut->format('F Y');
            case 'trimestre':
                return 'Trimestre ' . $dateDebut->quarter . ' ' . $dateDebut->year;
            case 'annee':
                return 'Année ' . $dateDebut->year;
            default:
                return 'Du ' . $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');
        }
    }

    /**
     * Générer un rapport de synthèse exécutive
     */
    public function synthese(Request $request)
    {
        // Validation des paramètres
        $validated = $request->validate([
            'periode' => 'nullable|in:mois_courant,trimestre_courant,semestre_courant,annee_courante,12_derniers_mois,personnalisee',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'niveau_detail' => 'nullable|in:executif,detaille,complet',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        // Déterminer la période
        $periode = $validated['periode'] ?? 'mois_courant';
        list($dateDebut, $dateFin) = $this->getPeriodeDates($periode, $validated['date_debut'] ?? null, $validated['date_fin'] ?? null);

        // Récupérer les données pour la synthèse
        $stats = $this->getStatistiquesDetaillees($dateDebut, $dateFin);
        $recettesEvolution = $this->getEvolutionRecettes($dateDebut, $dateFin);
        $performanceZones = $this->getRecettesParZone($dateDebut, $dateFin);
        $performanceCollecteurs = $this->getPerformanceCollecteurs($dateDebut, $dateFin);
        
        // Calculs d'indicateurs clés
        $indicateurs = [
            'recettes_totales' => $stats['total_recettes'] ?? 0,
            'croissance_mensuelle' => $this->calculerCroissanceMensuelle($dateDebut, $dateFin),
            'taux_contribution' => $this->calculerTauxContribution($dateDebut, $dateFin),
            'performance_moyenne' => $stats['moyenne_par_paiement'] ?? 0,
            'zone_plus_performante' => $performanceZones->first(),
            'tendance_generale' => $this->analyserTendanceGenerale($recettesEvolution)
        ];

        // Recommandations automatiques
        $recommandations = $this->genererRecommandations($stats, $performanceZones, $performanceCollecteurs);

        $data = compact('stats', 'recettesEvolution', 'performanceZones', 'performanceCollecteurs', 'indicateurs', 'recommandations', 'dateDebut', 'dateFin');

        $format = $validated['format'] ?? 'html';
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.synthese_pdf', $data, 'synthese_executive_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'synthese_executive_' . now()->format('Y-m-d'));
            default:
                return view('rapports.synthese_html', $data);
        }
    }

    /**
     * Générer un rapport des zones fiscales
     */
    public function zones(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'analyses' => 'nullable|array',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        $zoneId = $validated['zone_id'] ?? null;

        // Récupérer les données par zone
        $zonesData = $this->getRecettesParZone($dateDebut, $dateFin, $zoneId);
        $unitesParZone = $this->getUnitesParZone();
        $collecteursParZone = $this->getCollecteursParZone();

        if ($zoneId) {
            $unitesParZone = $unitesParZone->where('zone_id', $zoneId);
            $collecteursParZone = $collecteursParZone->where('zone_id', $zoneId);
        }

        $analyses = $validated['analyses'] ?? ['recettes_zone', 'unites_zone', 'taux_contribution', 'collecteurs_zone', 'types_activites', 'cartographie'];

        // Calculs de performance
        $performanceZones = $zonesData->map(function($zone) use ($unitesParZone, $collecteursParZone, $dateDebut, $dateFin) {
            $unites = $unitesParZone->where('zone_id', $zone->id)->first();
            $collecteurs = $collecteursParZone->where('zone_id', $zone->id)->first();
            
            return (object) [
                'id' => $zone->id,
                'nom' => $zone->nom,
                'total_recettes' => $zone->paiements_sum_montant ?? 0,
                'nombre_paiements' => $zone->paiements_count ?? 0,
                'nombre_unites' => $unites->nombre_unites ?? 0,
                'nombre_collecteurs' => $collecteurs->nombre_collecteurs ?? 0,
                'recette_par_unite' => $unites && $unites->nombre_unites > 0 ? ($zone->paiements_sum_montant ?? 0) / $unites->nombre_unites : 0,
                'recette_par_collecteur' => $collecteurs && $collecteurs->nombre_collecteurs > 0 ? ($zone->paiements_sum_montant ?? 0) / $collecteurs->nombre_collecteurs : 0,
                'taux_contribution' => $this->calculerTauxContributionZone($zone->id, $dateDebut, $dateFin)
            ];
        });

        $data = compact('performanceZones', 'dateDebut', 'dateFin', 'analyses');
        $format = $validated['format'] ?? 'html';
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.zones_pdf', $data, 'rapport_zones_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'rapport_zones_' . now()->format('Y-m-d'));
            default:
                return view('rapports.zones_html', $data);
        }
    }

    /**
     * Générer un rapport des taxes
     */
    public function taxes(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'taxe_id' => 'nullable|exists:taxes,id',
            'type_analyse' => 'nullable|in:revenus,evolution,repartition,efficacite,projections',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        // Récupérer les données par taxe
        $taxes = DB::table('taxes')
            ->leftJoin('unites_commerciales', 'taxes.id', '=', 'unites_commerciales.taxe_id')
            ->leftJoin('paiements', 'unites_commerciales.id', '=', 'paiements.unite_id')
            ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
            ->where('paiements.statut', 'valide')
            ->select(
                'taxes.id',
                'taxes.nom',
                'taxes.montant as montant_unitaire',
                DB::raw('SUM(paiements.montant) as revenus_totaux'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements'),
                DB::raw('COUNT(DISTINCT unites_commerciales.id) as nombre_unites'),
                DB::raw('AVG(paiements.montant) as montant_moyen')
            )
            ->groupBy('taxes.id', 'taxes.nom', 'taxes.montant')
            ->orderBy('revenus_totaux', 'desc')
            ->get();

        // Calculs de performance
        $performanceTaxes = $taxes->map(function($taxe) use ($dateDebut, $dateFin) {
            $tauxPerception = $this->calculerTauxPerceptionTaxe($taxe->id, $dateDebut, $dateFin);
            $evolutionMensuelle = $this->getEvolutionMensuelleTaxe($taxe->id, $dateDebut, $dateFin);
            
            return [
                'taxe' => $taxe,
                'taux_perception' => $tauxPerception,
                'evolution_mensuelle' => $evolutionMensuelle,
                'efficacite' => $taxe->nombre_unites > 0 ? ($taxe->nombre_paiements / $taxe->nombre_unites) * 100 : 0
            ];
        });

        // Statistiques globales
        $statsGlobales = [
            'total_recettes' => $taxes->sum('revenus_totaux'),
            'total_paiements' => $taxes->sum('nombre_paiements'),
            'nombre_taxes' => $taxes->count(),
            'moyenne_par_taxe' => $taxes->count() > 0 ? $taxes->avg('revenus_totaux') : 0
        ];

        // Évolution des recettes par taxe
        $evolutionRecettes = $this->getEvolutionRecettes($dateDebut, $dateFin);

        $data = compact('taxes', 'performanceTaxes', 'statsGlobales', 'evolutionRecettes', 'dateDebut', 'dateFin');
        $format = $validated['format'] ?? 'html';
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.taxes_pdf', $data, 'rapport_taxes_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'rapport_taxes_' . now()->format('Y-m-d'));
            default:
                return view('rapports.taxes_html', $data);
        }
    }

    /**
     * Générer un rapport détaillé des paiements par unité commerciale
     */
    public function paiements(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'collecteur_id' => 'nullable|exists:users,id',
            'unite_id' => 'nullable|exists:unites_commerciales,id',
            'statut' => 'nullable|in:valide,en_attente,annule,tous',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        $query = Paiement::with(['unite.zone', 'unite.taxe', 'collecteur'])
            ->whereBetween('date_paiement', [$dateDebut, $dateFin]);

        if ($validated['zone_id'] ?? null) {
            $query->whereHas('unite', function($q) use ($validated) {
                $q->where('zone_id', $validated['zone_id']);
            });
        }

        if ($validated['collecteur_id'] ?? null) {
            $query->where('collecteur_id', $validated['collecteur_id']);
        }

        if ($validated['unite_id'] ?? null) {
            $query->where('unite_id', $validated['unite_id']);
        }

        if (!empty($validated['statut']) && $validated['statut'] !== 'tous') {
            $query->where('statut', $validated['statut']);
        }

        $paiements = $query->orderBy('date_paiement', 'desc')->get();

        // Statistiques globales
        $stats = [
            'total_paiements' => $paiements->count(),
            'total_recettes' => $paiements->where('statut', 'valide')->sum('montant'),
            'paiements_valides' => $paiements->where('statut', 'valide')->count(),
            'paiements_en_attente' => $paiements->where('statut', 'en_attente')->count(),
            'paiements_annules' => $paiements->where('statut', 'annule')->count(),
            'montant_avance_total' => $paiements->sum('montant_avance'),
            'montant_restant_total' => $paiements->sum('montant_restant'),
            'periode' => $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'),
        ];

        $zones = ZoneFiscale::all();
        $collecteurs = User::where('role', 'collecteur')->get();

        $data = compact('paiements', 'stats', 'dateDebut', 'dateFin', 'zones', 'collecteurs');
        $format = $validated['format'] ?? 'html';

        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.paiements_pdf', $data, 'rapport_paiements_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'rapport_paiements_' . now()->format('Y-m-d'));
            default:
                return view('rapports.paiements_html', $data);
        }
    }

    /**
     * Générer un rapport mensuel
     */
    public function mensuel(Request $request)
    {
        $validated = $request->validate([
            'mois' => 'nullable|date_format:Y-m',
            'comparaison' => 'nullable|in:mois_precedent,meme_mois_annee_precedente,moyenne_6_mois,objectif_mensuel',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        $mois = $validated['mois'] ?? now()->format('Y-m');
        $dateDebut = Carbon::parse($mois . '-01')->startOfMonth();
        $dateFin = Carbon::parse($mois . '-01')->endOfMonth();

        // Données du mois courant
        $donneesActuelles = $this->getStatistiquesDetaillees($dateDebut, $dateFin);
        $recettesQuotidiennes = $this->getRecettesQuotidiennes($dateDebut, $dateFin);
        $performanceZones = $this->getRecettesParZone($dateDebut, $dateFin);
        $performanceCollecteurs = $this->getPerformanceCollecteurs($dateDebut, $dateFin);

        // Données de comparaison
        $donneesComparaison = $this->getDonneesComparaison($dateDebut, $validated['comparaison'] ?? 'mois_precedent');
        
        // Calculs d'évolution
        $evolution = [
            'recettes' => $this->calculerEvolution($donneesActuelles['total_recettes'], $donneesComparaison['total_recettes']),
            'paiements' => $this->calculerEvolution($donneesActuelles['total_paiements'], $donneesComparaison['total_paiements']),
            'moyenne_paiement' => $this->calculerEvolution($donneesActuelles['moyenne_par_paiement'], $donneesComparaison['moyenne_par_paiement'])
        ];

        // Variables pour la vue
        $croissance = $evolution['recettes'];
        $evolutionPaiements = $evolution['paiements'];
        $evolutionMoyenne = $evolution['moyenne_paiement'];
        $evolutionTaux = $this->calculerEvolution($donneesActuelles['taux_reussite'] ?? 0, $donneesComparaison['taux_reussite'] ?? 0);

        // Prévisions pour le mois suivant
        $previsionsMoisSuivant = $this->genererPrevisionsMoisSuivant($dateDebut);

        // Objectifs (exemple)
        $objectifs = [
            'recettes' => 1000000, // 1 million FCFA
            'paiements' => 100,
            'taux_contribution' => 85
        ];

        // Évolution des recettes pour graphiques
        $evolutionRecettes = $this->getEvolutionRecettes($dateDebut, $dateFin);

        $data = compact(
            'donneesActuelles', 
            'donneesComparaison', 
            'evolution', 
            'recettesQuotidiennes', 
            'performanceZones', 
            'performanceCollecteurs', 
            'previsionsMoisSuivant', 
            'dateDebut', 
            'dateFin',
            'croissance',
            'evolutionPaiements',
            'evolutionMoyenne',
            'evolutionTaux',
            'objectifs',
            'evolutionRecettes'
        );
        $format = $validated['format'] ?? 'html';
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.mensuel_pdf', $data, 'rapport_mensuel_' . $mois);
            case 'excel':
                return $this->generateExcel($data, 'rapport_mensuel_' . $mois);
            default:
                return view('rapports.mensuel_html', $data);
        }
    }

    /**
     * Générer un rapport comparatif
     */
    public function comparatif(Request $request)
    {
        $validated = $request->validate([
            'periode_base' => 'nullable|in:mois_courant,trimestre_courant,annee_courante,12_derniers_mois,personnalisee',
            'periode_comparaison' => 'nullable|in:periode_precedente,meme_periode_annee_precedente,moyenne_historique,objectifs_budgetaires,personnalisee',
            'date_debut_base' => 'nullable|date',
            'date_fin_base' => 'nullable|date|after_or_equal:date_debut_base',
            'date_debut_comp' => 'nullable|date',
            'date_fin_comp' => 'nullable|date|after_or_equal:date_debut_comp',
            'format' => 'nullable|in:pdf,excel,html'
        ]);

        // Déterminer les périodes
        $periodeBase = $validated['periode_base'] ?? 'personnalisee';
        list($dateDebutBase, $dateFinBase) = $this->getPeriodeDates($periodeBase, $validated['date_debut_base'] ?? null, $validated['date_fin_base'] ?? null);

        $periodeComparaison = $validated['periode_comparaison'] ?? 'personnalisee';
        if ($periodeComparaison === 'personnalisee' && !empty($validated['date_debut_comp']) && !empty($validated['date_fin_comp'])) {
            list($dateDebutComp, $dateFinComp) = $this->getPeriodeDates('personnalisee', $validated['date_debut_comp'], $validated['date_fin_comp']);
        } else {
            list($dateDebutComp, $dateFinComp) = $this->getPeriodeComparaison($dateDebutBase, $dateFinBase, $periodeComparaison);
        }

        // Récupérer les données pour les deux périodes
        $donneesBase = $this->getStatistiquesDetaillees($dateDebutBase, $dateFinBase);
        $donneesComp = $this->getStatistiquesDetaillees($dateDebutComp, $dateFinComp);
        
        $recettesBase = $this->getRecettesParZone($dateDebutBase, $dateFinBase);
        $recettesComp = $this->getRecettesParZone($dateDebutComp, $dateFinComp);

        // Calculs comparatifs avec gestion des valeurs nulles
        $comparaisons = [
            'recettes_totales' => $this->calculerComparaison($donneesBase['total_recettes'] ?? 0, $donneesComp['total_recettes'] ?? 0),
            'nombre_paiements' => $this->calculerComparaison($donneesBase['total_paiements'] ?? 0, $donneesComp['total_paiements'] ?? 0),
            'moyenne_paiement' => $this->calculerComparaison($donneesBase['moyenne_par_paiement'] ?? 0, $donneesComp['moyenne_par_paiement'] ?? 0),
            'zones' => $this->comparerZones($recettesBase, $recettesComp)
        ];

        // Calculer les évolutions pour l'affichage
        $evolutionRecettes = $this->calculerEvolution($donneesBase['total_recettes'] ?? 0, $donneesComp['total_recettes'] ?? 0);
        $evolutionPaiements = $this->calculerEvolution($donneesBase['total_paiements'] ?? 0, $donneesComp['total_paiements'] ?? 0);
        $evolutionTaux = $this->calculerEvolution($donneesBase['taux_reussite'] ?? 0, $donneesComp['taux_reussite'] ?? 0);
        $evolutionMoyenne = $this->calculerEvolution($donneesBase['moyenne_par_paiement'] ?? 0, $donneesComp['moyenne_par_paiement'] ?? 0);

        // Libellés des périodes
        $periodeBase = $this->getPeriodeLibelle($periodeBase, $dateDebutBase, $dateFinBase);
        $periodeComparaison = $this->getPeriodeLibelle($periodeComparaison, $dateDebutComp, $dateFinComp);

        $data = compact(
            'donneesBase', 
            'donneesComp', 
            'comparaisons', 
            'dateDebutBase', 
            'dateFinBase', 
            'dateDebutComp', 
            'dateFinComp',
            'evolutionRecettes',
            'evolutionPaiements',
            'evolutionTaux',
            'evolutionMoyenne',
            'periodeBase',
            'periodeComparaison'
        );
        $format = $validated['format'] ?? 'html';
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.comparatif_pdf', $data, 'rapport_comparatif_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel($data, 'rapport_comparatif_' . now()->format('Y-m-d'));
            default:
                return view('rapports.comparatif_html', $data);
        }
    }

    // Méthodes utilitaires pour les calculs

    private function getPeriodeDates($periode, $dateDebut = null, $dateFin = null)
    {
        if ($periode === 'personnalisee' && $dateDebut && $dateFin) {
            return [Carbon::parse($dateDebut), Carbon::parse($dateFin)];
        }

        $now = now();
        switch ($periode) {
            case 'trimestre_courant':
                return [$now->startOfQuarter(), $now->endOfQuarter()];
            case 'semestre_courant':
                $month = $now->month;
                if ($month <= 6) {
                    return [$now->startOfYear(), $now->month(6)->endOfMonth()];
                } else {
                    return [$now->month(7)->startOfMonth(), $now->endOfYear()];
                }
            case 'annee_courante':
                return [$now->startOfYear(), $now->endOfYear()];
            case '12_derniers_mois':
                return [$now->subMonths(12)->startOfMonth(), $now->endOfMonth()];
            default: // mois_courant
                return [$now->startOfMonth(), $now->endOfMonth()];
        }
    }

    private function calculerCroissanceMensuelle($dateDebut, $dateFin)
    {
        $moisPrecedent = Carbon::parse($dateDebut)->subMonth();
        $recettesActuelles = Paiement::where('paiements.statut', 'valide')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->sum('montant');
        
        $recettesPrecedentes = Paiement::where('paiements.statut', 'valide')
            ->whereBetween('date_paiement', [$moisPrecedent->startOfMonth(), $moisPrecedent->endOfMonth()])
            ->sum('montant');

        return $recettesPrecedentes > 0 ? (($recettesActuelles - $recettesPrecedentes) / $recettesPrecedentes) * 100 : 0;
    }

    private function calculerTauxContribution($dateDebut, $dateFin)
    {
        $totalUnites = UniteCommerciale::where('actif', true)->count();
        $unitesPayantes = UniteCommerciale::where('actif', true)
            ->whereHas('paiements', function($q) use ($dateDebut, $dateFin) {
                $q->where('paiements.statut', 'valide')
                  ->whereBetween('date_paiement', [$dateDebut, $dateFin]);
            })
            ->count();

        return $totalUnites > 0 ? ($unitesPayantes / $totalUnites) * 100 : 0;
    }

    private function genererRecommandations($stats, $performanceZones, $performanceCollecteurs)
    {
        $recommandations = [];

        // Recommandations basées sur les zones
        $zonesPlusPerformantes = $performanceZones->take(2);
        $zonesMoinsPerformantes = $performanceZones->reverse()->take(2);
        
        if ($zonesMoinsPerformantes->count() > 0) {
            $recommandations[] = [
                'type' => 'zone_performance',
                'titre' => 'Améliorer la performance des zones faibles',
                'description' => "Les zones " . $zonesMoinsPerformantes->pluck('nom')->join(', ') . " nécessitent une attention particulière.",
                'priorite' => 'haute'
            ];
        }

        // Recommandations basées sur les collecteurs
        $collecteursTopPerformants = $performanceCollecteurs->take(3);
        if ($collecteursTopPerformants->count() > 0) {
            $recommandations[] = [
                'type' => 'collecteur_formation',
                'titre' => 'Partager les bonnes pratiques',
                'description' => "Organiser des sessions de formation avec les collecteurs les plus performants.",
                'priorite' => 'moyenne'
            ];
        }

        return $recommandations;
    }

    private function calculerEvolution($valeurActuelle, $valeurPrecedente)
    {
        if ($valeurPrecedente == 0) {
            return $valeurActuelle > 0 ? 100 : 0;
        }
        
        return (($valeurActuelle - $valeurPrecedente) / $valeurPrecedente) * 100;
    }

    private function calculerComparaison($valeur1, $valeur2)
    {
        return [
            'valeur_base' => $valeur1,
            'valeur_comparaison' => $valeur2,
            'difference' => $valeur1 - $valeur2,
            'pourcentage' => $this->calculerEvolution($valeur1, $valeur2)
        ];
    }

    private function getUnitesParZone()
    {
        return DB::table('zones_fiscales')
            ->leftJoin('unites_commerciales', function($join) {
                $join->on('zones_fiscales.id', '=', 'unites_commerciales.zone_id')
                     ->where('unites_commerciales.actif', true);
            })
            ->select(
                'zones_fiscales.id as zone_id',
                DB::raw('COUNT(unites_commerciales.id) as nombre_unites')
            )
            ->groupBy('zones_fiscales.id')
            ->get();
    }

    private function getCollecteursParZone()
    {
        return DB::table('zones_fiscales')
            ->leftJoin('users', function($join) {
                $join->on('zones_fiscales.id', '=', 'users.zone_id')
                     ->where('users.role', 'collecteur');
            })
            ->select(
                'zones_fiscales.id as zone_id',
                DB::raw('COUNT(DISTINCT users.id) as nombre_collecteurs')
            )
            ->groupBy('zones_fiscales.id')
            ->get();
    }

         private function calculerTauxContributionZone($zoneId, $dateDebut, $dateFin)
     {
         $totalUnites = UniteCommerciale::where('actif', true)->where('zone_id', $zoneId)->count();
         $unitesPayantes = UniteCommerciale::where('actif', true)
             ->where('zone_id', $zoneId)
             ->whereHas('paiements', function($q) use ($dateDebut, $dateFin) {
                 $q->where('paiements.statut', 'valide')
                   ->whereBetween('date_paiement', [$dateDebut, $dateFin]);
             })
             ->count();

         return $totalUnites > 0 ? ($unitesPayantes / $totalUnites) * 100 : 0;
     }

         private function calculerTauxPerceptionTaxe($taxeId, $dateDebut, $dateFin)
     {
         $totalUnites = UniteCommerciale::where('actif', true)->where('taxe_id', $taxeId)->count();
         $unitesPayantes = UniteCommerciale::where('actif', true)
             ->where('taxe_id', $taxeId)
             ->whereHas('paiements', function($q) use ($dateDebut, $dateFin) {
                 $q->where('statut', 'valide')
                   ->whereBetween('date_paiement', [$dateDebut, $dateFin]);
             })
             ->count();

         return $totalUnites > 0 ? ($unitesPayantes / $totalUnites) * 100 : 0;
     }

    private function getEvolutionMensuelleTaxe($taxeId, $dateDebut, $dateFin)
    {
        $recettesActuelles = DB::table('taxes')
            ->leftJoin('unites_commerciales', 'taxes.id', '=', 'unites_commerciales.taxe_id')
            ->leftJoin('paiements', 'unites_commerciales.id', '=', 'paiements.unite_id')
            ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
            ->where('paiements.statut', 'valide')
            ->where('taxes.id', $taxeId)
            ->sum('paiements.montant');

        $recettesPrecedentes = DB::table('taxes')
            ->leftJoin('unites_commerciales', 'taxes.id', '=', 'unites_commerciales.taxe_id')
            ->leftJoin('paiements', 'unites_commerciales.id', '=', 'paiements.unite_id')
            ->whereBetween('paiements.date_paiement', [Carbon::parse($dateDebut)->subMonth(), Carbon::parse($dateFin)->subMonth()])
            ->where('paiements.statut', 'valide')
            ->where('taxes.id', $taxeId)
            ->sum('paiements.montant');

        return $recettesPrecedentes > 0 ? (($recettesActuelles - $recettesPrecedentes) / $recettesPrecedentes) * 100 : 0;
    }

    private function getRecettesQuotidiennes($dateDebut, $dateFin)
    {
        return Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
                       ->where('statut', 'valide')
                       ->select(
                           DB::raw('DATE(date_paiement) as jour'),
                           DB::raw('SUM(montant) as total_recettes')
                       )
                       ->groupBy('jour')
                       ->orderBy('jour')
                       ->get();
    }

    private function getDonneesComparaison($dateDebut, $comparaisonType)
    {
        $dateDebutComparaison = null;
        $dateFinComparaison = null;

        switch ($comparaisonType) {
            case 'mois_precedent':
                $dateDebutComparaison = Carbon::parse($dateDebut)->subMonth();
                $dateFinComparaison = Carbon::parse($dateDebut)->subMonth()->endOfMonth();
                break;
            case 'meme_mois_annee_precedente':
                $dateDebutComparaison = Carbon::parse($dateDebut)->subYear();
                $dateFinComparaison = Carbon::parse($dateDebut)->subYear()->endOfMonth();
                break;
            case 'moyenne_6_mois':
                $dateDebutComparaison = Carbon::parse($dateDebut)->subMonths(6);
                $dateFinComparaison = Carbon::parse($dateDebut)->endOfMonth();
                break;
            case 'objectif_mensuel':
                // Pour l'objectif mensuel, on peut utiliser une date de fin fixe ou une date de fin dynamique
                // Ici, on prend la date de fin du mois courant
                $dateFinComparaison = Carbon::parse($dateDebut)->endOfMonth();
                break;
            default: // periode_precedente
                $dateDebutComparaison = Carbon::parse($dateDebut)->subMonth();
                $dateFinComparaison = Carbon::parse($dateDebut)->subMonth()->endOfMonth();
                break;
        }

        return $this->getStatistiquesDetaillees($dateDebutComparaison, $dateFinComparaison);
    }

    private function genererPrevisionsMoisSuivant($dateDebut)
    {
        $dateFinMoisSuivant = Carbon::parse($dateDebut)->endOfMonth()->addMonth();
        $dateDebutMoisSuivant = Carbon::parse($dateDebut)->endOfMonth()->addDay(); // Commence le lendemain du dernier jour du mois

        $statsMoisSuivant = $this->getStatistiquesDetaillees($dateDebutMoisSuivant, $dateFinMoisSuivant);
        $recettesEvolution = $this->getEvolutionRecettes($dateDebutMoisSuivant, $dateFinMoisSuivant);
        $performanceZones = $this->getRecettesParZone($dateDebutMoisSuivant, $dateFinMoisSuivant);
        $performanceCollecteurs = $this->getPerformanceCollecteurs($dateDebutMoisSuivant, $dateFinMoisSuivant);

        $previsions = [
            'recettes_prevues' => $recettesEvolution->sum('total'),
            'nombre_paiements_prevus' => $recettesEvolution->sum('nombre'),
            'moyenne_paiement_prevue' => $statsMoisSuivant['moyenne_par_paiement']
        ];

        return $previsions;
    }

    private function comparerZones($recettesBase, $recettesComp)
    {
        $comparaisons = [];
        foreach ($recettesBase as $zoneBase) {
            $zoneComp = $recettesComp->where('id', $zoneBase->id)->first();
            if ($zoneComp) {
                $comparaisons[] = [
                    'zone' => $zoneBase->nom,
                    'recettes_base' => $zoneBase->total_recettes ?? 0,
                    'recettes_comparaison' => $zoneComp->total_recettes ?? 0,
                    'difference' => ($zoneBase->total_recettes ?? 0) - ($zoneComp->total_recettes ?? 0),
                    'pourcentage' => $zoneBase->total_recettes ?? 0 > 0 ? (($zoneBase->total_recettes ?? 0 - $zoneComp->total_recettes ?? 0) / $zoneBase->total_recettes ?? 0) * 100 : 0
                ];
            }
        }
        return $comparaisons;
    }

         private function analyserTendanceGenerale($evolutionRecettes)
     {
         $tendance = 'Stable';
         $pourcentageEvolution = 0;

         if ($evolutionRecettes->count() > 1) {
             $premiereDate = $evolutionRecettes->first()->periode;
             $derniereDate = $evolutionRecettes->last()->periode;

             $diffJours = Carbon::parse($derniereDate)->diffInDays(Carbon::parse($premiereDate));

             if ($diffJours > 0) {
                 $totalEvolution = $evolutionRecettes->sum('total');
                 $pourcentageEvolution = ($totalEvolution / $evolutionRecettes->first()->total) * 100;

                 if ($pourcentageEvolution > 0) {
                     $tendance = 'Croissante';
                 } elseif ($pourcentageEvolution < 0) {
                     $tendance = 'Décroissante';
                 }
             }
         }

         return [
             'tendance' => $tendance,
             'pourcentage' => $pourcentageEvolution
         ];
     }

     private function getPeriodeComparaison($dateDebutBase, $dateFinBase, $typeComparaison)
     {
         $duree = Carbon::parse($dateFinBase)->diffInDays(Carbon::parse($dateDebutBase));
         
         switch ($typeComparaison) {
             case 'meme_periode_annee_precedente':
                 return [
                     Carbon::parse($dateDebutBase)->subYear(),
                     Carbon::parse($dateFinBase)->subYear()
                 ];
             case 'moyenne_historique':
                 // Pour la moyenne historique, on prend la même période sur les 3 dernières années
                 return [
                     Carbon::parse($dateDebutBase)->subYears(3),
                     Carbon::parse($dateFinBase)->subYear()
                 ];
             case 'objectifs_budgetaires':
                 // Pour les objectifs, on peut utiliser la même période
                 return [$dateDebutBase, $dateFinBase];
             default: // periode_precedente
                 return [
                     Carbon::parse($dateDebutBase)->subDays($duree + 1),
                     Carbon::parse($dateDebutBase)->subDay()
                 ];
         }
     }

    /**
     * Générer un rapport par nature de commerce
     */
    public function natureCommerce(Request $request)
    {
        // Validation des paramètres
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'nature' => 'nullable|string',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'format' => 'nullable|in:html,pdf,excel',
            'niveau_detail' => 'nullable|in:standard,detaille,complet'
        ]);

        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        // Récupérer les données par nature de commerce
        $query = DB::table('unites_commerciales')
            ->leftJoin('paiements', function($join) use ($dateDebut, $dateFin) {
                $join->on('unites_commerciales.id', '=', 'paiements.unite_id')
                     ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
                     ->where('paiements.statut', 'valide');
            });

        if ($validated['nature'] ?? null) {
            $query->where('unites_commerciales.nature', $validated['nature']);
        }

        if ($validated['zone_id'] ?? null) {
            $query->where('unites_commerciales.zone_id', $validated['zone_id']);
        }

        $donnees = $query->select(
                'unites_commerciales.nature',
                DB::raw('COUNT(DISTINCT unites_commerciales.id) as nombre_unites'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements'),
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes'),
                DB::raw('COALESCE(AVG(paiements.montant), 0) as montant_moyen')
            )
            ->groupBy('unites_commerciales.nature')
            ->orderBy('total_recettes', 'desc')
            ->get();

        // Statistiques globales
        $stats = [
            'periode' => $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'),
            'zone' => ($validated['zone_id'] ?? null) ? ZoneFiscale::find($validated['zone_id'])->nom : 'Toutes les zones',
            'nature_filtree' => $validated['nature'] ?? 'Toutes les natures',
            'total_unites' => $donnees->sum('nombre_unites'),
            'total_paiements' => $donnees->sum('nombre_paiements'),
            'total_recettes' => $donnees->sum('total_recettes'),
            'nombre_natures' => $donnees->count()
        ];

        $format = $validated['format'] ?? 'html';

        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.nature-commerce-html', compact('donnees', 'stats'), 'rapport_nature_commerce_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel(compact('donnees', 'stats'), 'rapport_nature_commerce_' . now()->format('Y-m-d'));
            default:
                return view('rapports.nature-commerce-html', compact('donnees', 'stats'));
        }
    }

    /**
     * Générer un rapport par type de commerce
     */
    public function typeCommerce(Request $request)
    {
        // Validation des paramètres
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'type_commerce' => 'nullable|string',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'categorie' => 'nullable|string|in:activite_commerciale,point_interet',
            'is_transport' => 'nullable|boolean',
            'format' => 'nullable|in:html,pdf,excel',
            'niveau_detail' => 'nullable|in:standard,detaille,complet'
        ]);

        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        // Récupérer les données par type de commerce
        $query = DB::table('unites_commerciales')
            ->leftJoin('paiements', function($join) use ($dateDebut, $dateFin) {
                $join->on('unites_commerciales.id', '=', 'paiements.unite_id')
                     ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
                     ->where('paiements.statut', 'valide');
            });

        if ($validated['type_commerce'] ?? null) {
            $query->where('unites_commerciales.type_commerce', $validated['type_commerce']);
        }

        if ($validated['zone_id'] ?? null) {
            $query->where('unites_commerciales.zone_id', $validated['zone_id']);
        }

        if ($validated['categorie'] ?? null) {
            $query->where('unites_commerciales.categorie', $validated['categorie']);
        }

        $donnees = $query->select(
                'unites_commerciales.type_commerce',
                'unites_commerciales.categorie',
                DB::raw('COUNT(DISTINCT unites_commerciales.id) as nombre_unites'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements'),
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes'),
                DB::raw('COALESCE(AVG(paiements.montant), 0) as montant_moyen')
            )
            ->groupBy('unites_commerciales.type_commerce', 'unites_commerciales.categorie')
            ->orderBy('total_recettes', 'desc')
            ->get();

        // Statistiques globales
        $stats = [
            'periode' => $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'),
            'zone' => ($validated['zone_id'] ?? null) ? ZoneFiscale::find($validated['zone_id'])->nom : 'Toutes les zones',
            'type_filtre' => $validated['type_commerce'] ?? 'Tous les types',
            'categorie_filtree' => $validated['categorie'] ?? 'Toutes les catégories',
            'total_unites' => $donnees->sum('nombre_unites'),
            'total_paiements' => $donnees->sum('nombre_paiements'),
            'total_recettes' => $donnees->sum('total_recettes'),
            'nombre_types' => $donnees->count()
        ];

        $format = $validated['format'] ?? 'html';

        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.type-commerce-pdf', compact('donnees', 'stats'), 'rapport_type_commerce_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel(compact('donnees', 'stats'), 'rapport_type_commerce_' . now()->format('Y-m-d'));
            default:
                return view('rapports.type-commerce-html', compact('donnees', 'stats'));
        }
    }

    /**
     * Générer un rapport combiné Nature et Type de commerce
     */
    public function natureTypeCommerce(Request $request)
    {
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'nature' => 'nullable|string',
            'type_commerce' => 'nullable|string',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'format' => 'nullable|in:html,pdf,excel',
        ]);

        $dateDebut = $validated['date_debut'] ? Carbon::parse($validated['date_debut']) : Carbon::now()->startOfMonth();
        $dateFin = $validated['date_fin'] ? Carbon::parse($validated['date_fin']) : Carbon::now()->endOfMonth();

        $query = DB::table('unites_commerciales')
            ->leftJoin('paiements', function($join) use ($dateDebut, $dateFin) {
                $join->on('unites_commerciales.id', '=', 'paiements.unite_id')
                     ->whereBetween('paiements.date_paiement', [$dateDebut, $dateFin])
                     ->where('paiements.statut', 'valide');
            });

        if ($validated['nature'] ?? null) {
            $query->where('unites_commerciales.nature', $validated['nature']);
        }

        if ($validated['type_commerce'] ?? null) {
            $query->where('unites_commerciales.type_commerce', $validated['type_commerce']);
        }

        if ($validated['zone_id'] ?? null) {
            $query->where('unites_commerciales.zone_id', $validated['zone_id']);
        }

        $donnees = $query->select(
                'unites_commerciales.nature',
                'unites_commerciales.type_commerce',
                'unites_commerciales.categorie',
                DB::raw('COUNT(DISTINCT unites_commerciales.id) as nombre_unites'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements'),
                DB::raw('SUM(paiements.montant) as total_recettes'),
                DB::raw('AVG(paiements.montant) as montant_moyen')
            )
            ->groupBy('unites_commerciales.nature', 'unites_commerciales.type_commerce', 'unites_commerciales.categorie')
            ->orderBy('total_recettes', 'desc')
            ->get();

        $stats = [
            'periode' => $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'),
            'zone' => ($validated['zone_id'] ?? null) ? ZoneFiscale::find($validated['zone_id'])->nom : 'Toutes les zones',
            'nature_filtree' => $validated['nature'] ?? 'Toutes les natures',
            'type_filtre' => $validated['type_commerce'] ?? 'Tous les types',
            'total_unites' => $donnees->sum('nombre_unites'),
            'total_paiements' => $donnees->sum('nombre_paiements'),
            'total_recettes' => $donnees->sum('total_recettes'),
            'nombre_combinaisons' => $donnees->count()
        ];

        $format = $validated['format'] ?? 'html';

        switch ($format) {
            case 'pdf':
                return $this->generatePDF('rapports.nature-type-commerce-html', compact('donnees', 'stats'), 'rapport_nature_type_commerce_' . now()->format('Y-m-d'));
            case 'excel':
                return $this->generateExcel(compact('donnees', 'stats'), 'rapport_nature_type_commerce_' . now()->format('Y-m-d'));
            default:
                return view('rapports.nature-type-commerce-html', compact('donnees', 'stats'));
        }
    }

    /**
     * Générer un rapport avec carte thématique intégrée
     */
    public function rapportAvecCarte(Request $request)
    {
        // Validation des paramètres
        $request->validate([
            'periode' => 'required|in:mois,trimestre,semestre,annee,custom',
            'date_debut' => 'required_if:periode,custom|date',
            'date_fin' => 'required_if:periode,custom|date|after_or_equal:date_debut',
            'zone_id' => 'nullable|exists:zones_fiscales,id',
            'statut_paiement' => 'nullable|in:paye,impaye,tous',
            'format' => 'required|in:html,pdf'
        ]);

        // Obtenir les dates de la période
        $dates = $this->getPeriodeDates($request->periode, $request->date_debut, $request->date_fin);
        $dateDebut = $dates['debut'];
        $dateFin = $dates['fin'];

        // Données pour le rapport
        $data = [
            'periode' => $this->getPeriodeLibelle($request->periode, $dateDebut, $dateFin),
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'zone_id' => $request->zone_id,
            'statut_paiement' => $request->statut_paiement ?? 'tous',
            'zone' => $request->zone_id ? ZoneFiscale::find($request->zone_id) : null,
            'statistiques' => $this->getStatistiquesDetaillees($dateDebut, $dateFin, $request->zone_id),
            'recettes_par_zone' => $this->getRecettesParZone($dateDebut, $dateFin),
            'performance_collecteurs' => $this->getPerformanceCollecteurs($dateDebut, $dateFin, $request->zone_id),
            'evolution_recettes' => $this->getEvolutionRecettes($dateDebut, $dateFin, $request->zone_id),
            'donnees_carte' => $this->getDonneesCarteThematique($dateDebut, $dateFin, $request->zone_id, $request->statut_paiement),
            'recommandations' => $this->genererRecommandations($dateDebut, $dateFin, $request->zone_id)
        ];

        // Générer le rapport selon le format demandé
        switch ($request->format) {
            case 'html':
                return view('rapports.rapport-avec-carte', $data);
            case 'pdf':
                return $this->generatePDF('rapports.rapport-avec-carte', $data, 'rapport-avec-carte-thematique');
        }
    }

    /**
     * Obtenir les données pour la carte thématique
     */
    private function getDonneesCarteThematique($dateDebut, $dateFin, $zoneId = null, $statutPaiement = 'tous')
    {
        $query = UniteCommerciale::with(['zone', 'taxe', 'paiements' => function($q) use ($dateDebut, $dateFin) {
            $q->where('statut', 'valide')
              ->whereBetween('date_paiement', [$dateDebut, $dateFin]);
        }]);

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        $unites = $query->actif()->get();

        return $unites->map(function($unite) use ($dateDebut, $dateFin) {
            $statutPaiementUnite = $this->determinerStatutPaiement($unite, $dateDebut, $dateFin);
            $montantDu = $this->calculerMontantDu($unite, $dateDebut, $dateFin);

            return [
                'id' => $unite->id,
                'nom_proprietaire' => $unite->nom_proprietaire,
                'prenom_proprietaire' => $unite->prenom_proprietaire,
                'nom_commerce' => $unite->nom_commerce,
                'type_commerce' => $unite->type_commerce,
                'categorie' => $unite->categorie,
                'adresse' => $unite->adresse,
                'telephone' => $unite->telephone,
                'coordinates' => [
                    'lat' => $unite->latitude,
                    'lng' => $unite->longitude
                ],
                'zone' => $unite->zone ? [
                    'id' => $unite->zone->id,
                    'nom' => $unite->zone->nom
                ] : null,
                'taxe' => $unite->taxe ? [
                    'id' => $unite->taxe->id,
                    'nom' => $unite->taxe->nom,
                    'montant' => $unite->taxe->montant
                ] : null,
                'statut_paiement' => $statutPaiementUnite,
                'montant_du' => $montantDu,
                'dernier_paiement' => $unite->paiements->sortByDesc('date_paiement')->first(),
                'nombre_paiements' => $unite->paiements->count()
            ];
        })->filter(function($unite) use ($statutPaiement) {
            if ($statutPaiement === 'tous') return true;
            return $unite['statut_paiement'] === $statutPaiement;
        })->values();
    }

    /**
     * Détermine le statut de paiement d'une unité commerciale
     */
    private function determinerStatutPaiement($unite, $periodeDebut = null, $periodeFin = null)
    {
        $paiementsValides = $unite->paiements->where('statut', 'valide');
        
        if ($periodeDebut && $periodeFin) {
            $paiementsValides = $paiementsValides->filter(function($paiement) use ($periodeDebut, $periodeFin) {
                return $paiement->date_paiement >= $periodeDebut && $paiement->date_paiement <= $periodeFin;
            });
        }

        if ($paiementsValides->isEmpty()) {
            return 'impaye';
        }

        // Vérifier si le paiement est complet
        $montantTaxe = $unite->taxe ? $unite->taxe->montant : 0;
        $montantPaye = $paiementsValides->sum('montant');

        if ($montantPaye >= $montantTaxe) {
            return 'paye';
        } elseif ($montantPaye > 0) {
            return 'partiel';
        } else {
            return 'impaye';
        }
    }

    /**
     * Calcule le montant dû pour une unité commerciale
     */
    private function calculerMontantDu($unite, $periodeDebut = null, $periodeFin = null)
    {
        $montantTaxe = $unite->taxe ? $unite->taxe->montant : 0;
        
        $paiementsValides = $unite->paiements->where('statut', 'valide');
        
        if ($periodeDebut && $periodeFin) {
            $paiementsValides = $paiementsValides->filter(function($paiement) use ($periodeDebut, $periodeFin) {
                return $paiement->date_paiement >= $periodeDebut && $paiement->date_paiement <= $periodeFin;
            });
        }

        $montantPaye = $paiementsValides->sum('montant');
        
        return max(0, $montantTaxe - $montantPaye);
    }

    /**
     * Afficher la carte thématique
     */
    public function carteThematique(Request $request)
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        if (!in_array($role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès réservé au maire et au gestionnaire');
        }

        $commune = $user->commune;
        if (!$commune) {
            return redirect()->route('rapports.index')
                ->with('error', 'Aucune commune associée à votre compte. Contactez l\'administrateur.');
        }

        $dateDebut = $request->get('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-d'));
        $zoneId = $request->get('zone_id');
        $statutPaiement = $request->get('statut_paiement', 'tous');
        $format = $request->get('format', 'html');
        $style = $request->get('style', 'standard');

        $donnees_carte = $this->getDonneesCarteThematique($dateDebut, $dateFin, $zoneId, $statutPaiement);

        $zones = ZoneFiscale::where('commune_id', $commune->id)->get();

        $zonesForBounds = $zones->map(function ($z) {
            return ['id' => $z->id, 'nom' => $z->nom, 'geometrie' => $z->geometrie];
        })->toArray();
        [$mapCenter, $mapBounds] = $this->computeThematicMapCenterAndBounds($zonesForBounds, $commune);

        $types_commerce = DB::table('types_commerce')->get();

        $stats = [
            'total_unites' => $donnees_carte->count(),
            'unites_payantes' => $donnees_carte->where('statut_paiement', 'paye')->count(),
            'unites_non_payantes' => $donnees_carte->where('statut_paiement', 'impaye')->count(),
            'unites_partiel' => $donnees_carte->where('statut_paiement', 'partiel')->count(),
            'montant_total_collecte' => $donnees_carte->sum('montant_du'),
            'periode' => Carbon::parse($dateDebut)->format('d/m/Y') . ' au ' . Carbon::parse($dateFin)->format('d/m/Y'),
            'zone' => $zoneId ? ZoneFiscale::find($zoneId)->nom : 'Toutes les zones'
        ];

        if ($format === 'pdf') {
            $pdf = PDF::loadView('rapports.carte-thematique', compact('donnees_carte', 'zones', 'types_commerce', 'stats', 'style', 'commune', 'mapCenter', 'mapBounds'));
            return $pdf->download('carte-thematique-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('rapports.carte-thematique', compact('donnees_carte', 'zones', 'types_commerce', 'stats', 'style', 'commune', 'mapCenter', 'mapBounds'));
    }

    /**
     * Centre et bornes carte thématique : zones de la commune ou défauts par code (SANDIARA, THIES).
     */
    private function computeThematicMapCenterAndBounds(array $zones, Commune $commune): array
    {
        $defaultsByCode = [
            'SANDIARA' => [[14.41, -16.77], [[14.42699, -16.80247], [14.44382, -16.78059]]],
            'THIES' => [[14.79, -16.93], [[14.70, -17.00], [14.85, -16.85]]],
        ];
        $defaultSenegal = [[14.72, -17.45], null];

        $lats = [];
        $lngs = [];
        foreach ($zones as $z) {
            $geom = $z['geometrie'] ?? null;
            if (!$geom || !isset($geom['coordinates'])) {
                continue;
            }
            $this->extractCoordsForThematic($geom['coordinates'], $lats, $lngs);
        }
        if (!empty($lats) && !empty($lngs)) {
            $minLat = min($lats);
            $maxLat = max($lats);
            $minLng = min($lngs);
            $maxLng = max($lngs);
            $center = [($minLat + $maxLat) / 2, ($minLng + $maxLng) / 2];
            $bounds = [[$minLat, $minLng], [$maxLat, $maxLng]];
            return [$center, $bounds];
        }

        $code = strtoupper((string) ($commune->code ?? ''));
        if (isset($defaultsByCode[$code])) {
            return $defaultsByCode[$code];
        }
        return [$defaultSenegal[0], $defaultSenegal[1]];
    }

    private function extractCoordsForThematic($coords, array &$lats, array &$lngs): void
    {
        if (!is_array($coords)) {
            return;
        }
        if (isset($coords[0]) && is_numeric($coords[0]) && isset($coords[1]) && is_numeric($coords[1])) {
            $lngs[] = (float) $coords[0];
            $lats[] = (float) $coords[1];
            return;
        }
        foreach ($coords as $item) {
            $this->extractCoordsForThematic($item, $lats, $lngs);
        }
    }

    /**
     * Afficher le rapport géomarketing
     */
    public function geomarketing(Request $request)
    {
        // Vérifier que l'utilisateur est maire
        $role = strtolower(auth()->user()->role ?? '');
        if (!in_array($role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès réservé au maire et au gestionnaire');
        }

        // Récupérer les paramètres de la requête
        $dateDebut = $request->get('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-d'));
        $zoneId = $request->get('zone_id');
        $natureId = $request->get('nature_id');
        $typeId = $request->get('type_id');
        $seuilScore = $request->get('seuil_score', 0);
        $couches = $request->get('couches', ['population', 'routes', 'bati', 'opportunites', 'propositions']);
        $typeRapport = $request->get('type_rapport', 'executif');
        $format = $request->get('format', 'html');

        // Données de démonstration pour le géomarketing
        $zones_analyse = [
            [
                'nom' => 'Zone Centre-Ville',
                'score' => 85,
                'densite_commerciale' => 90.5,
                'accessibilite_routiere' => 85.2,
                'potentiel_bati' => 80.8,
                'population_potentiel' => 85.0,
                'unites_count' => 45,
                'opportunites' => ['Supermarché', 'Restaurant', 'Banque', 'Services'],
                'risques' => ['Concurrence élevée', 'Coûts élevés'],
                'recommandation' => 'Zone d\'excellence - Investissement fortement recommandé',
                'coordonnees' => [14.43540, -16.79153],
                'nature_activite' => 'Commerce',
                'types_commerce' => ['Supermarché', 'Restaurant', 'Banque']
            ],
            [
                'nom' => 'Zone Louly Benteigné',
                'score' => 70,
                'densite_commerciale' => 65.3,
                'accessibilite_routiere' => 75.1,
                'potentiel_bati' => 70.2,
                'population_potentiel' => 70.5,
                'unites_count' => 28,
                'opportunites' => ['Boutique générale', 'Pharmacie', 'École privée'],
                'risques' => ['Concurrence modérée'],
                'recommandation' => 'Zone prometteuse - Investissement recommandé',
                'coordonnees' => [14.44200, -16.79500],
                'nature_activite' => 'Services',
                'types_commerce' => ['Boutique générale', 'Pharmacie']
            ],
            [
                'nom' => 'Zone Rurale Sud',
                'score' => 55,
                'densite_commerciale' => 45.8,
                'accessibilite_routiere' => 60.4,
                'potentiel_bati' => 50.1,
                'population_potentiel' => 55.2,
                'unites_count' => 15,
                'opportunites' => ['Atelier mécanique', 'Boutique rurale'],
                'risques' => ['Population limitée', 'Accessibilité réduite'],
                'recommandation' => 'Zone modérée - Investissement possible avec prudence',
                'coordonnees' => [14.42800, -16.78500],
                'nature_activite' => 'Industrie',
                'types_commerce' => ['Atelier mécanique']
            ],
            [
                'nom' => 'Zone Route de Thiès',
                'score' => 60,
                'densite_commerciale' => 55.2,
                'accessibilite_routiere' => 80.6,
                'potentiel_bati' => 50.8,
                'population_potentiel' => 55.1,
                'unites_count' => 22,
                'opportunites' => ['Station-service', 'Restaurant routier', 'Hôtel'],
                'risques' => ['Investissement important', 'Autorisations nécessaires'],
                'recommandation' => 'Zone modérée - Investissement possible avec prudence',
                'coordonnees' => [14.44000, -16.78000],
                'nature_activite' => 'Services',
                'types_commerce' => ['Station-service', 'Restaurant']
            ],
            [
                'nom' => 'Zone Marché Hebdomadaire',
                'score' => 75,
                'densite_commerciale' => 85.8,
                'accessibilite_routiere' => 90.2,
                'potentiel_bati' => 75.5,
                'population_potentiel' => 88.0,
                'unites_count' => 35,
                'opportunites' => ['Boutique générale', 'Restaurant', 'Services financiers', 'Artisanat'],
                'risques' => ['Concurrence saisonnière', 'Gestion des flux'],
                'recommandation' => 'Zone d\'excellence - Investissement fortement recommandé',
                'coordonnees' => [14.43800, -16.79200],
                'nature_activite' => 'Commerce',
                'types_commerce' => ['Boutique générale', 'Restaurant']
            ]
        ];

        // Appliquer les filtres
        $zones_analyse_filtrees = $zones_analyse;
        
        // Filtre par zone spécifique
        if (!empty($zoneId)) {
            $zone_selectionnee = ZoneFiscale::find($zoneId);
            if ($zone_selectionnee) {
                $zones_analyse_filtrees = array_filter($zones_analyse_filtrees, function($zone) use ($zone_selectionnee) {
                    return strpos(strtolower($zone['nom']), strtolower($zone_selectionnee->nom)) !== false;
                });
            }
        }
        
        // Filtre par nature d'activité
        if (!empty($natureId)) {
            $zones_analyse_filtrees = array_filter($zones_analyse_filtrees, function($zone) use ($natureId) {
                return isset($zone['nature_activite']) && $zone['nature_activite'] === $natureId;
            });
        }
        
        // Filtre par type de commerce
        if (!empty($typeId)) {
            $zones_analyse_filtrees = array_filter($zones_analyse_filtrees, function($zone) use ($typeId) {
                return isset($zone['types_commerce']) && in_array($typeId, $zone['types_commerce']);
            });
        }
        
        // Filtre par seuil de score
        if ($seuilScore > 0) {
            $zones_analyse_filtrees = array_filter($zones_analyse_filtrees, function($zone) use ($seuilScore) {
                return $zone['score'] >= $seuilScore;
            });
        }
        
        // Si aucun résultat après filtrage, utiliser toutes les zones
        if (empty($zones_analyse_filtrees)) {
            $zones_analyse_filtrees = $zones_analyse;
        }
        
        // Réindexer le tableau
        $zones_analyse = array_values($zones_analyse_filtrees);

        // Générer les propositions d'investissement basées sur les zones filtrées
        $propositions_investissement = [];
        foreach ($zones_analyse as $zone) {
            if ($zone['score'] >= 70) {
                $propositions_investissement[] = [
                    'zone' => $zone['nom'],
                    'type_investissement' => $zone['opportunites'][0] ?? 'Commerce général',
                    'score' => $zone['score'],
                    'revenus_estimes' => rand(50000000, 200000000),
                    'cout_investissement' => rand(30000000, 120000000),
                    'roi_estime' => round(rand(60, 120), 1),
                    'delai' => rand(6, 18) . '-' . rand(12, 24) . ' mois',
                    'risques' => $zone['risques']
                ];
            }
        }
        
        // Si aucune proposition, ajouter une proposition par défaut
        if (empty($propositions_investissement)) {
            $propositions_investissement = [
                [
                    'zone' => 'Zone Centre-Ville',
                    'type_investissement' => 'Supermarché',
                    'score' => 85,
                    'revenus_estimes' => 150000000,
                    'cout_investissement' => 80000000,
                    'roi_estime' => 87.5,
                    'delai' => '6-12 mois',
                    'risques' => ['Concurrence modérée', 'Coût d\'installation élevé']
                ]
            ];
        }

        // Calculer les statistiques basées sur les données filtrées
        $total_unites = array_sum(array_column($zones_analyse, 'unites_count'));
        $score_moyen = count($zones_analyse) > 0 ? array_sum(array_column($zones_analyse, 'score')) / count($zones_analyse) : 0;
        
        $zones_excellence = count(array_filter($zones_analyse, function($zone) { return $zone['score'] >= 75; }));
        $zones_prometteuses = count(array_filter($zones_analyse, function($zone) { return $zone['score'] >= 60 && $zone['score'] < 75; }));
        $zones_moderees = count(array_filter($zones_analyse, function($zone) { return $zone['score'] >= 45 && $zone['score'] < 60; }));
        $zones_faibles = count(array_filter($zones_analyse, function($zone) { return $zone['score'] < 45; }));
        
        // Déterminer la zone analysée
        $zone_analysée = 'Toutes les zones';
        if (!empty($zoneId)) {
            $zone_selectionnee = ZoneFiscale::find($zoneId);
            if ($zone_selectionnee) {
                $zone_analysée = $zone_selectionnee->nom;
            }
        }
        
        // Statistiques du rapport
        $stats = [
            'total_zones' => count($zones_analyse),
            'score_moyen' => round($score_moyen, 1),
            'zones_excellence' => $zones_excellence,
            'zones_prometteuses' => $zones_prometteuses,
            'zones_moderees' => $zones_moderees,
            'zones_faibles' => $zones_faibles,
            'total_unites' => $total_unites,
            'periode' => $dateDebut . ' au ' . $dateFin,
            'zone' => $zone_analysée
        ];

        // Données pour les filtres
        $zones = ZoneFiscale::all();
        $natures = collect([
            (object)['id' => 'Commerce', 'nom' => 'Commerce'],
            (object)['id' => 'Services', 'nom' => 'Services'],
            (object)['id' => 'Industrie', 'nom' => 'Industrie'],
            (object)['id' => 'Agriculture', 'nom' => 'Agriculture']
        ]);
        $types = collect([
            (object)['id' => 'Supermarché', 'nom' => 'Supermarché'],
            (object)['id' => 'Boutique générale', 'nom' => 'Boutique générale'],
            (object)['id' => 'Restaurant', 'nom' => 'Restaurant'],
            (object)['id' => 'Pharmacie', 'nom' => 'Pharmacie'],
            (object)['id' => 'Station-service', 'nom' => 'Station-service'],
            (object)['id' => 'Banque', 'nom' => 'Banque']
        ]);

        // Gestion des différents types de rapport et formats d'export
        $rapport_data = compact('zones_analyse', 'propositions_investissement', 'stats', 'zones', 'natures', 'types', 'typeRapport', 'couches');
        
        // Ajouter les informations de filtrage pour l'affichage
        $rapport_data['filtres_appliques'] = [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'zone_selectionnee' => $zoneId ? ZoneFiscale::find($zoneId)->nom ?? 'Toutes les zones' : 'Toutes les zones',
            'nature_selectionnee' => $natureId ?: 'Toutes les natures',
            'type_selectionne' => $typeId ?: 'Tous les types',
            'seuil_score' => $seuilScore,
            'couches_analyse' => $couches,
            'type_rapport' => $typeRapport
        ];
        
        // Gestion des formats d'export
        if ($format === 'pdf') {
            $pdf = PDF::loadView('rapports.geomarketing-pdf', $rapport_data);
            return $pdf->download('rapport-geomarketing-' . now()->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return $this->exportGeomarketingExcel($zones_analyse, $propositions_investissement, $stats, $rapport_data['filtres_appliques']);
        } elseif ($format === 'html') {
            // Pour le format HTML, on utilise la vue normale
            return view('rapports.geomarketing', $rapport_data);
        }

        // Format par défaut (HTML)
        return view('rapports.geomarketing', $rapport_data);
    }

    /**
     * Export Excel pour le rapport géomarketing
     */
    private function exportGeomarketingExcel($zones_analyse, $propositions_investissement, $stats, $filtres_appliques = [])
    {
        $filename = 'rapport-geomarketing-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($zones_analyse, $propositions_investissement, $stats, $filtres_appliques) {
            $file = fopen('php://output', 'w');
            
            // En-tête du rapport
            fputcsv($file, ['RAPPORT GÉOMARKETING - SICOSA']);
            fputcsv($file, ['Analyse des opportunités d\'investissement']);
            fputcsv($file, ['Généré le ' . now()->format('d/m/Y à H:i')]);
            fputcsv($file, []);
            
            // Filtres appliqués
            if (!empty($filtres_appliques)) {
                fputcsv($file, ['FILTRES APPLIQUÉS']);
                fputcsv($file, ['Période', $filtres_appliques['date_debut'] . ' au ' . $filtres_appliques['date_fin']]);
                fputcsv($file, ['Zone', $filtres_appliques['zone_selectionnee']]);
                fputcsv($file, ['Nature d\'activité', $filtres_appliques['nature_selectionnee']]);
                fputcsv($file, ['Type de commerce', $filtres_appliques['type_selectionne']]);
                fputcsv($file, ['Seuil de score', $filtres_appliques['seuil_score'] > 0 ? $filtres_appliques['seuil_score'] . '+' : 'Tous']);
                fputcsv($file, ['Type de rapport', $filtres_appliques['type_rapport']]);
                fputcsv($file, ['Couches d\'analyse', implode(', ', $filtres_appliques['couches_analyse'])]);
                fputcsv($file, []);
            }
            
            // Statistiques principales
            fputcsv($file, ['STATISTIQUES PRINCIPALES']);
            fputcsv($file, ['Métrique', 'Valeur']);
            fputcsv($file, ['Zones analysées', $stats['total_zones']]);
            fputcsv($file, ['Score moyen', $stats['score_moyen']]);
            fputcsv($file, ['Unités commerciales', $stats['total_unites']]);
            fputcsv($file, ['Propositions', count($propositions_investissement)]);
            fputcsv($file, []);
            
            // Zones d'opportunités
            fputcsv($file, ['ZONES D\'OPPORTUNITÉS']);
            fputcsv($file, ['Zone', 'Score', 'Unités', 'Recommandation']);
            
            foreach ($zones_analyse as $zone) {
                fputcsv($file, [
                    $zone['nom'],
                    $zone['score'] . '/100',
                    $zone['unites_count'] . ' unités',
                    $zone['recommandation']
                ]);
            }
            
            fputcsv($file, []);
            
            // Propositions d'investissement
            fputcsv($file, ['PROPOSITIONS D\'INVESTISSEMENT']);
            fputcsv($file, ['Type d\'Investissement', 'Zone', 'Score', 'ROI (%)', 'Investissement (FCFA)', 'Délai']);
            
            foreach ($propositions_investissement as $proposition) {
                fputcsv($file, [
                    $proposition['type_investissement'],
                    $proposition['zone'],
                    $proposition['score'] . '/100',
                    $proposition['roi_estime'] . '%',
                    number_format($proposition['cout_investissement']),
                    $proposition['delai']
                ]);
            }
            
            fputcsv($file, []);
            
            // Recommandations
            fputcsv($file, ['RECOMMANDATIONS']);
            fputcsv($file, ['Points forts']);
            fputcsv($file, ['Zone Centre-Ville: excellent potentiel (85/100)']);
            fputcsv($file, ['Diversité des opportunités identifiées']);
            fputcsv($file, ['ROI attractif sur les investissements']);
            fputcsv($file, []);
            fputcsv($file, ['Actions prioritaires']);
            fputcsv($file, ['Concentrer les investissements sur la Zone Centre-Ville']);
            fputcsv($file, ['Développer les services de proximité']);
            fputcsv($file, ['Améliorer l\'accessibilité des zones périphériques']);
            fputcsv($file, []);
            fputcsv($file, ['© ' . date('Y') . ' SICOSA - Tous droits réservés']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 