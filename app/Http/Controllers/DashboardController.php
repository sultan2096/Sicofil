<?php

namespace App\Http\Controllers;

use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\ZoneFiscale;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard général - redirige selon le rôle
     */
    public function index()
    {
        switch (auth()->user()->role) {
            case 'maire':
                return $this->maire();
            case 'gestionnaire':
                return $this->gestionnaire();
            case 'collecteur':
                return $this->collecteur();
            default:
                abort(403, 'Rôle non autorisé');
        }
    }

    /**
     * Dashboard du maire
     */
    public function maire()
    {
        return $this->maireDashboard();
    }

    /**
     * Dashboard du gestionnaire
     */
    public function gestionnaire()
    {
        return $this->gestionnaireDashboard();
    }

    /**
     * Dashboard du collecteur
     */
    public function collecteur()
    {
        return $this->collecteurDashboard();
    }

    /**
     * Dashboard du maire
     */
    public function maireDashboard()
    {
        $user = auth()->user();
        // Si le maire n'a pas de commune assignée, utiliser Sandiara par défaut pour afficher des données
        $communeId = $user->commune_id ?? \App\Models\Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->value('id') ?? 0;
        $cacheKey = 'maire_dashboard_stats_' . $communeId;

        // Cache des statistiques pour 5 minutes, par commune (chaque maire voit les données de sa commune)
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($communeId) {
            $qUnites = UniteCommerciale::query();
            $qPaiements = Paiement::query()->where('statut', 'valide');
            $qCollecteurs = User::where('role', 'collecteur');
            $qZones = ZoneFiscale::query();
            if ($communeId) {
                $qUnites->where('commune_id', $communeId);
                $qPaiements->where('commune_id', $communeId);
                $qCollecteurs->where('commune_id', $communeId);
                $qZones->where('commune_id', $communeId);
            } else {
                $qUnites->withoutGlobalScopes();
                $qZones->withoutGlobalScopes();
            }

            $totalUnites = $qUnites->count();
            $baseUnites = (clone $qUnites);

            $unitesEnRegle = (clone $baseUnites)->whereHas('paiements', function ($query) use ($communeId) {
                $query->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->where('statut', 'valide');
                if ($communeId) {
                    $query->where('commune_id', $communeId);
                }
            })->count();

            $unitesEnRetard = (clone $baseUnites)->whereHas('paiements', function ($query) use ($communeId) {
                $query->where('statut', 'valide');
                if ($communeId) {
                    $query->where('commune_id', $communeId);
                }
            })->whereDoesntHave('paiements', function ($query) use ($communeId) {
                $query->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->where('statut', 'valide');
                if ($communeId) {
                    $query->where('commune_id', $communeId);
                }
            })->count();

            $unitesNonPayees = (clone $baseUnites)->whereDoesntHave('paiements', function ($query) use ($communeId) {
                $query->where('statut', 'valide');
                if ($communeId) {
                    $query->where('commune_id', $communeId);
                }
            })->count();

            return [
                'totalUnites' => $totalUnites,
                'unitesEnRegle' => $unitesEnRegle,
                'unitesEnRetard' => $unitesEnRetard,
                'unitesNonPayees' => $unitesNonPayees,
                'totalPaiementsJour' => (clone $qPaiements)->whereDate('date_paiement', today())->sum('montant'),
                'totalPaiementsMois' => (clone $qPaiements)->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->sum('montant'),
                'totalCollecteurs' => $qCollecteurs->count(),
                'totalZones' => $qZones->count(),
            ];
        });
        
        extract($stats);
        
        // S'assurer que les nouvelles variables sont définies
        $unitesEnRegle = $stats['unitesEnRegle'] ?? 0;
        $unitesEnRetard = $stats['unitesEnRetard'] ?? 0;
        $unitesNonPayees = $stats['unitesNonPayees'] ?? 0;
        
        // Nouveaux recensements du jour
        $nouveauxRecensements = UniteCommerciale::with(['collecteur', 'zone'])
            ->whereDate('date_recensement', today())
            ->orderBy('date_recensement', 'desc')
            ->take(5)
            ->get();
        
        // Collecte par zone (mois en cours) - version légère (évite le eager-loading massif)
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $paiementsParZone = DB::table('zones_fiscales as z')
            ->leftJoin('unites_commerciales as u', function ($join) use ($communeId) {
                $join->on('u.zone_id', '=', 'z.id')
                     ->where('u.actif', true);
                if ($communeId) {
                    $join->where('u.commune_id', $communeId);
                }
            })
            ->leftJoin('paiements as p', function ($join) use ($startOfMonth, $endOfMonth, $communeId) {
                $join->on('p.unite_id', '=', 'u.id')
                    ->where('p.statut', '=', 'valide')
                    ->whereBetween('p.date_paiement', [$startOfMonth, $endOfMonth]);
                if ($communeId) {
                    $join->where('p.commune_id', $communeId);
                }
            })
            ->when($communeId, function ($query) use ($communeId) {
                $query->where('z.commune_id', $communeId);
            })
            ->groupBy('z.id', 'z.nom')
            ->orderBy('z.nom')
            ->select(
                'z.id',
                'z.nom',
                DB::raw('COUNT(DISTINCT u.id) as total_unites'),
                DB::raw('COUNT(DISTINCT CASE WHEN p.id IS NOT NULL THEN u.id END) as unites_payantes'),
                DB::raw('COALESCE(SUM(p.montant), 0) as montant_collecte')
            )
            ->get()
            ->map(function ($row) {
                $row->total_unites = (int) ($row->total_unites ?? 0);
                $row->unites_payantes = (int) ($row->unites_payantes ?? 0);
                $row->montant_collecte = (float) ($row->montant_collecte ?? 0);
                return $row;
            });

        // Évolution des recettes sur les 12 derniers mois (filtré par commune)
        $evolutionRecettes = [];
        $labelsEvolution = [];
        $qEvol = Paiement::where('statut', 'valide');
        if ($communeId) {
            $qEvol->where('commune_id', $communeId);
        }
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $montant = (clone $qEvol)->whereMonth('date_paiement', $date->month)
                ->whereYear('date_paiement', $date->year)
                ->sum('montant');
            $evolutionRecettes[] = $montant;
            $labelsEvolution[] = $date->format('M Y');
        }

        // Génération des données pour le graphique 'Évolution des Recettes (6 mois)'
        $evolutionRecettes6Mois = array_slice($evolutionRecettes, -6);
        $labelsEvolution6Mois = array_slice($labelsEvolution, -6);

        // Répartition des recettes par zone (mois actuel)
        $repartitionZones = [];
        $labelsZones = [];
        $couleursZones = ['#1e3a8a', '#f59e0b', '#3b82f6', '#10b981', '#f97316', '#8b5cf6', '#14b8a6'];
        
        foreach ($paiementsParZone as $zone) {
            if (($zone->montant_collecte ?? 0) > 0) {
                $repartitionZones[] = $zone->montant_collecte;
                $labelsZones[] = $zone->nom;
            }
        }

        // Top 10 Collecteurs Performants (de la commune du maire)
        $qTopCollecteurs = User::where('role', 'collecteur');
        if ($communeId) {
            $qTopCollecteurs->where('commune_id', $communeId);
        }
        $topCollecteurs = $qTopCollecteurs
            ->withSum(['paiementsCollectes' => function ($q) use ($communeId) {
                $q->where('statut', 'valide');
                if ($communeId) {
                    $q->where('commune_id', $communeId);
                }
            }], 'montant')
            ->withCount(['paiementsCollectes' => function ($q) use ($communeId) {
                $q->where('statut', 'valide');
                if ($communeId) {
                    $q->where('commune_id', $communeId);
                }
            }])
            ->orderBy('paiements_collectes_sum_montant', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'nom' => $c->name,
                    'montant' => $c->paiements_collectes_sum_montant ?? 0,
                    'paiements' => $c->paiements_collectes_count ?? 0,
                ];
            });

        // Performance par Zone Fiscale (zone, taux) — uniquement zones de la commune
        $performanceZones = collect();
        $qPerf = DB::table('zones_fiscales')
            ->leftJoin('unites_commerciales', function ($join) use ($communeId) {
                $join->on('zones_fiscales.id', '=', 'unites_commerciales.zone_id')
                     ->where('unites_commerciales.actif', true);
                if ($communeId) {
                    $join->where('unites_commerciales.commune_id', $communeId);
                }
            })
            ->leftJoin('paiements', function ($j) use ($communeId) {
                $j->on('unites_commerciales.id', '=', 'paiements.unite_id')
                    ->where('paiements.statut', '=', 'valide');
                if ($communeId) {
                    $j->where('paiements.commune_id', $communeId);
                }
            });
        if ($communeId) {
            $qPerf->where('zones_fiscales.commune_id', $communeId);
        }
        $zones = $qPerf
            ->select(
                'zones_fiscales.id',
                'zones_fiscales.nom',
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes'),
                DB::raw('COUNT(DISTINCT paiements.id) as nombre_paiements'),
                DB::raw('COUNT(DISTINCT unites_commerciales.id) as total_unites')
            )
            ->groupBy('zones_fiscales.id', 'zones_fiscales.nom')
            ->orderBy('total_recettes', 'desc')
            ->get();
        foreach ($zones as $zone) {
            $totalU = (int) ($zone->total_unites ?? 0);
            $nbP = (int) ($zone->nombre_paiements ?? 0);
            $taux = $totalU > 0 ? round(($nbP / $totalU) * 100, 1) : 0;
            $performanceZones->push([
                'zone' => $zone->nom,
                'taux' => $taux,
            ]);
        }

        // Répartition des recettes par type de taxe (filtré par commune)
        $repartitionTaxes = collect();
        $qTaxes = DB::table('taxes')
            ->leftJoin('unites_commerciales', function ($join) use ($communeId) {
                $join->on('taxes.id', '=', 'unites_commerciales.taxe_id')
                     ->where('unites_commerciales.actif', true);
                if ($communeId) {
                    $join->where('unites_commerciales.commune_id', $communeId);
                }
            })
            ->leftJoin('paiements', function ($j) use ($communeId) {
                $j->on('unites_commerciales.id', '=', 'paiements.unite_id')
                    ->where('paiements.statut', '=', 'valide');
                if ($communeId) {
                    $j->where('paiements.commune_id', $communeId);
                }
            });
        if ($communeId) {
            $qTaxes->where('taxes.commune_id', $communeId);
        }
        $taxes = $qTaxes
            ->select(
                'taxes.id',
                'taxes.nom',
                DB::raw('COALESCE(SUM(paiements.montant), 0) as total_recettes')
            )
            ->groupBy('taxes.id', 'taxes.nom')
            ->orderBy('total_recettes', 'desc')
            ->get();
        foreach ($taxes as $taxe) {
            $repartitionTaxes->push([
                'nom' => $taxe->nom,
                'montant' => $taxe->total_recettes ?? 0,
            ]);
        }

        // Distribution des unités par zone (uniquement zones de la commune)
        $distributionUnites = collect();
        $qDist = DB::table('zones_fiscales')
            ->leftJoin('unites_commerciales', function ($join) use ($communeId) {
                $join->on('zones_fiscales.id', '=', 'unites_commerciales.zone_id')
                     ->where('unites_commerciales.actif', true);
                if ($communeId) {
                    $join->where('unites_commerciales.commune_id', $communeId);
                }
            });
        if ($communeId) {
            $qDist->where('zones_fiscales.commune_id', $communeId);
        }
        $unitesParZone = $qDist
            ->select(
                'zones_fiscales.id',
                'zones_fiscales.nom',
                DB::raw('COUNT(unites_commerciales.id) as nombre_unites')
            )
            ->groupBy('zones_fiscales.id', 'zones_fiscales.nom')
            ->orderBy('nombre_unites', 'desc')
            ->get();
        foreach ($unitesParZone as $zone) {
            $distributionUnites->push([
                'zone' => $zone->nom,
                'count' => $zone->nombre_unites,
            ]);
        }

        // Statut de paiement des unités (déjà ok)
        $totalUnitesActives = UniteCommerciale::where('actif', true)->count();
        $unitesPayantesActives = UniteCommerciale::where('actif', true)
            ->whereHas('paiements', function($q) {
                $q->where('statut', 'valide');
            })
            ->count();
        $unitesNonPayantesActives = $totalUnitesActives - $unitesPayantesActives;
        $statutPaiement = [
            'payantes' => $unitesPayantesActives,
            'non_payantes' => $unitesNonPayantesActives,
            'total' => $totalUnitesActives
        ];

        return view('maire.dashboard', compact(
            'totalUnites',
            'unitesEnRegle',
            'unitesEnRetard', 
            'unitesNonPayees',
            'totalPaiementsJour',
            'totalPaiementsMois',
            'totalCollecteurs',
            'totalZones',
            'nouveauxRecensements',
            'paiementsParZone',
            'evolutionRecettes',
            'labelsEvolution',
            'repartitionZones',
            'labelsZones',
            'couleursZones',
            'topCollecteurs',
            'performanceZones',
            'repartitionTaxes',
            'unitesParZone',
            'statutPaiement',
            'distributionUnites',
            'evolutionRecettes6Mois',
            'labelsEvolution6Mois'
        ));
    }

    /**
     * Statistiques live du dashboard maire (JSON)
     */
    public function maireLiveStats()
    {
        $communeId = auth()->user()->commune_id ?? \App\Models\Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->value('id') ?? 0;

        $qUnites = UniteCommerciale::query();
        $qPaiements = Paiement::query()->where('statut', 'valide');
        if ($communeId) {
            $qUnites->where('commune_id', $communeId);
            $qPaiements->where('commune_id', $communeId);
        } else {
            $qUnites->withoutGlobalScopes();
        }

        $totalUnites = $qUnites->count();
        $unitesEnRegle = (clone $qUnites)->whereHas('paiements', function ($q) use ($communeId) {
            $q->whereMonth('date_paiement', now()->month)->whereYear('date_paiement', now()->year)->where('statut', 'valide');
            if ($communeId) {
                $q->where('commune_id', $communeId);
            }
        })->count();
        $unitesEnRetard = (clone $qUnites)->whereHas('paiements', function ($q) use ($communeId) {
            $q->where('statut', 'valide');
            if ($communeId) {
                $q->where('commune_id', $communeId);
            }
        })->whereDoesntHave('paiements', function ($q) use ($communeId) {
            $q->whereMonth('date_paiement', now()->month)->whereYear('date_paiement', now()->year)->where('statut', 'valide');
            if ($communeId) {
                $q->where('commune_id', $communeId);
            }
        })->count();
        $unitesNonPayees = (clone $qUnites)->whereDoesntHave('paiements', function ($q) use ($communeId) {
            $q->where('statut', 'valide');
            if ($communeId) {
                $q->where('commune_id', $communeId);
            }
        })->count();

        $qCollecteurs = User::where('role', 'collecteur');
        $qZones = ZoneFiscale::query();
        if ($communeId) {
            $qCollecteurs->where('commune_id', $communeId);
            $qZones->where('commune_id', $communeId);
        } else {
            $qZones->withoutGlobalScopes();
        }

        return response()->json([
            'totalUnites' => $totalUnites,
            'unitesEnRegle' => $unitesEnRegle,
            'unitesEnRetard' => $unitesEnRetard,
            'unitesNonPayees' => $unitesNonPayees,
            'totalPaiementsJour' => (clone $qPaiements)->whereDate('date_paiement', today())->sum('montant'),
            'totalPaiementsMois' => (clone $qPaiements)->whereMonth('date_paiement', now()->month)->whereYear('date_paiement', now()->year)->sum('montant'),
            'totalCollecteurs' => $qCollecteurs->count(),
            'totalZones' => $qZones->count(),
            'updatedAt' => now()->toIso8601String(),
        ]);
    }

    /**
     * Dashboard du gestionnaire
     */
    private function gestionnaireDashboard()
    {
        // Statistiques globales avec des calculs plus précis
        $stats = [
            'total_unites' => UniteCommerciale::where('actif', true)->count(),
            'total_paiements' => Paiement::whereMonth('date_paiement', now()->month)
                                         ->whereYear('date_paiement', now()->year)
                                         ->count(),
            'montant_total' => Paiement::whereMonth('date_paiement', now()->month)
                                      ->whereYear('date_paiement', now()->year)
                                      ->sum('montant'),
            'total_collecteurs' => User::where('role', 'collecteur')->count()
        ];

        // Derniers paiements (7 derniers jours)
        $derniersPaiements = Paiement::with(['unite', 'collecteur'])
            ->where('date_paiement', '>=', now()->subDays(7))
            ->orderBy('date_paiement', 'desc')
            ->limit(5)
            ->get();

        // Dernières unités commerciales recensées (30 derniers jours)
        $dernieresUnites = UniteCommerciale::with(['zone', 'taxe'])
            ->where('date_recensement', '>=', now()->subDays(30))
            ->orderBy('date_recensement', 'desc')
            ->limit(5)
            ->get();

        // Derniers recensements (identique aux dernières unités pour la cohérence)
        $derniersRecensements = $dernieresUnites;

        // Derniers logs système
        $derniersLogs = Log::with('user')
            ->whereIn('action', ['create_paiement', 'create_unite', 'update_unite', 'delete_taxe'])
            ->latest()
            ->limit(5)
            ->get();

        // Statistiques par zone (paiements du mois en cours)
        $statsParZone = ZoneFiscale::withCount(['unitesCommerciales' => function($query) {
                $query->where('actif', true);
            }])
            ->get()
            ->map(function($zone) {
                $zone->paiements_sum_montant = Paiement::whereHas('unite', function($query) use ($zone) {
                    $query->where('zone_id', $zone->id);
                })
                ->whereMonth('date_paiement', now()->month)
                ->whereYear('date_paiement', now()->year)
                ->sum('montant');
                
                return $zone;
            });

        // Performance des collecteurs (mois en cours)
        $statsParCollecteur = User::where('role', 'collecteur')
            ->get()
            ->map(function($collecteur) {
                $collecteur->paiements_count = Paiement::where('collecteur_id', $collecteur->id)
                    ->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->count();
                    
                $collecteur->montant_total = Paiement::where('collecteur_id', $collecteur->id)
                    ->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->sum('montant');
                    
                return $collecteur;
            })
            ->sortByDesc('montant_total');

        // Alertes de retard de paiement
        $unitesEnRetard = $this->getOverdueUnitsCount();

        $commune = auth()->user()->commune;

        return view('gestionnaire.dashboard', compact(
            'stats',
            'derniersPaiements',
            'dernieresUnites',
            'derniersRecensements',
            'derniersLogs',
            'statsParZone',
            'statsParCollecteur',
            'unitesEnRetard',
            'commune'
        ));
    }

    /**
     * Dashboard du collecteur
     */
    public function collecteurDashboard()
    {
        $user = auth()->user();
        
        // Optimiser les requêtes avec select spécifique et eager loading
        // Unités de la zone du collecteur (seulement les colonnes nécessaires)
        $unitesZone = UniteCommerciale::select('id', 'nom_proprietaire', 'prenom_proprietaire', 'nature', 'type_commerce', 'zone_id', 'actif')
            ->where('zone_id', $user->zone_id)
            ->where('actif', true)
            ->get();
        
        // Paiements du jour (optimisé avec select)
        $paiementsJour = Paiement::select('montant')
            ->where('collecteur_id', $user->id)
            ->whereDate('date_paiement', today())
            ->where('statut', 'valide')
            ->sum('montant');
        
        // Paiements du mois (optimisé avec select)
        $paiementsMois = Paiement::select('montant')
            ->where('collecteur_id', $user->id)
            ->whereMonth('date_paiement', now()->month)
            ->whereYear('date_paiement', now()->year)
            ->where('statut', 'valide')
            ->sum('montant');
        
        // Unités non payées ce mois (optimisé avec select et eager loading)
        $unitesNonPayees = UniteCommerciale::select('id', 'nom_proprietaire', 'prenom_proprietaire', 'nature', 'nom_commerce', 'adresse', 'quartier', 'telephone', 'zone_id', 'actif')
            ->where('zone_id', $user->zone_id)
            ->whereDoesntHave('paiements', function($query) {
                $query->whereMonth('date_paiement', now()->month)
                    ->whereYear('date_paiement', now()->year)
                    ->where('statut', 'valide');
            })
            ->where('actif', true)
            ->limit(10) // Limiter à 10 pour améliorer les performances
            ->get();

        return view('collecteur.dashboard', compact(
            'unitesZone',
            'paiementsJour',
            'paiementsMois',
            'unitesNonPayees'
        ));
    }

    /**
     * Calculer le nombre d'unités en retard de paiement
     */
    private function getOverdueUnitsCount()
    {
        $today = \Carbon\Carbon::today();
        $count = 0;
        
        // Récupérer toutes les unités actives avec une taxe périodique
        $unites = UniteCommerciale::with(['taxe', 'paiements'])
            ->where('actif', true)
            ->whereHas('taxe', function($query) {
                $query->whereIn('periodicite', ['jour', 'mois', 'an']);
            })
            ->get();
            
        foreach ($unites as $unite) {
            $dernierPaiement = $unite->paiements()
                ->where('statut', 'valide')
                ->orderBy('date_paiement', 'desc')
                ->first();
                
            $enRetard = false;
            
            if (!$dernierPaiement) {
                // Si jamais payé, vérifier la période de grâce
                $dateRecensement = $unite->date_recensement ? \Carbon\Carbon::parse($unite->date_recensement) : $unite->created_at;
                $graceJours = $this->getGracePeriod($unite->taxe->periodicite);
                $joursSansPayement = $today->diffInDays($dateRecensement);
                $enRetard = $joursSansPayement > $graceJours;
            } else {
                // Calculer la prochaine échéance
                $prochaineEcheance = $this->calculateNextDueDate($dernierPaiement->date_paiement, $unite->taxe->periodicite);
                $enRetard = $today->gt($prochaineEcheance);
            }
            
            if ($enRetard) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Calculer la prochaine date d'échéance
     */
    private function calculateNextDueDate($lastPaymentDate, $periodicite)
    {
        $date = \Carbon\Carbon::parse($lastPaymentDate);
        
        switch ($periodicite) {
            case 'jour':
                return $date->addDay();
            case 'mois':
                return $date->addMonth();
            case 'an':
                return $date->addYear();
            default:
                return $date->addMonth();
        }
    }

    /**
     * Obtenir la période de grâce selon la périodicité
     */
    private function getGracePeriod($periodicite)
    {
        switch ($periodicite) {
            case 'jour':
                return 3; // 3 jours de grâce
            case 'mois':
                return 30; // 30 jours de grâce
            case 'an':
                return 365; // 1 an de grâce
            default:
                return 30;
        }
    }
} 