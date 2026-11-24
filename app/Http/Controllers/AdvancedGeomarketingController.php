<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UniteCommerciale;
use App\Models\ZoneFiscale;
use App\Models\PopulationZone;
use App\Models\RouteVoirie;
use App\Models\Batiment;
use App\Models\Log as SystemLog;

class AdvancedGeomarketingController extends Controller
{
    /**
     * Afficher le dashboard de géomarketing avancé
     */
    public function index()
    {
        // Log de l'accès
        SystemLog::createLog('geomarketing_access', [
            'user_id' => auth()->id(),
            'action' => 'Accès au géomarketing avancé',
            'timestamp' => now()
        ]);

        // Récupérer les données de base pour les filtres
        $zones = ZoneFiscale::all();
        
        // Si pas de zones, créer des données de test
        if ($zones->isEmpty()) {
            $zones = collect([
                (object)['id' => 1, 'nom' => 'Zone Centre-Ville'],
                (object)['id' => 2, 'nom' => 'Zone Louly Benteigné'],
                (object)['id' => 3, 'nom' => 'Zone Rurale Sud'],
                (object)['id' => 4, 'nom' => 'Zone Route de Thiès'],
                (object)['id' => 5, 'nom' => 'Zone Agricole']
            ]);
        }
        
        // Données de test pour les natures d'activité
        $natures = collect([
            (object)['id' => 1, 'nom' => 'Commerce'],
            (object)['id' => 2, 'nom' => 'Services'],
            (object)['id' => 3, 'nom' => 'Industrie'],
            (object)['id' => 4, 'nom' => 'Agriculture']
        ]);
        
        // Données de test pour les types de commerce
        $types = collect([
            (object)['id' => 1, 'nom' => 'Boutique générale'],
            (object)['id' => 2, 'nom' => 'Supermarché'],
            (object)['id' => 3, 'nom' => 'Restaurant'],
            (object)['id' => 4, 'nom' => 'Atelier'],
            (object)['id' => 5, 'nom' => 'Station-service']
        ]);

        return view('maire.advanced-geomarketing', compact('zones', 'natures', 'types'));
    }

    /**
     * Analyser les opportunités commerciales
     */
    public function analyzeOpportunities(Request $request)
    {
        $request->validate([
            'zone_id' => 'nullable',
            'nature_id' => 'nullable',
            'type_id' => 'nullable',
            'population_min' => 'nullable|numeric|min:0',
            'population_max' => 'nullable|numeric|min:0',
        ]);

        // Données de test pour l'analyse
        $analysis = [
            [
                'zone_id' => 1,
                'zone_name' => 'Zone Centre-Ville',
                'commercial_density' => 90.5,
                'road_accessibility' => 85.2,
                'built_potential' => 80.8,
                'population_potential' => 85.0,
                'overall_score' => 85.4,
                'recommendation' => 'Zone d\'excellence - Investissement fortement recommandé',
                'unites_count' => 45
            ],
            [
                'zone_id' => 2,
                'zone_name' => 'Zone Louly Benteigné',
                'commercial_density' => 65.3,
                'road_accessibility' => 75.1,
                'built_potential' => 70.2,
                'population_potential' => 70.5,
                'overall_score' => 70.3,
                'recommendation' => 'Zone prometteuse - Investissement recommandé',
                'unites_count' => 28
            ],
            [
                'zone_id' => 3,
                'zone_name' => 'Zone Rurale Sud',
                'commercial_density' => 45.8,
                'road_accessibility' => 60.4,
                'built_potential' => 50.1,
                'population_potential' => 55.2,
                'overall_score' => 52.9,
                'recommendation' => 'Zone modérée - Investissement possible avec prudence',
                'unites_count' => 15
            ],
            [
                'zone_id' => 4,
                'zone_name' => 'Zone Route de Thiès',
                'commercial_density' => 55.2,
                'road_accessibility' => 80.6,
                'built_potential' => 50.8,
                'population_potential' => 55.1,
                'overall_score' => 60.4,
                'recommendation' => 'Zone modérée - Investissement possible avec prudence',
                'unites_count' => 22
            ],
            [
                'zone_id' => 5,
                'zone_name' => 'Zone Agricole',
                'commercial_density' => 35.1,
                'road_accessibility' => 45.3,
                'built_potential' => 40.2,
                'population_potential' => 35.8,
                'overall_score' => 39.1,
                'recommendation' => 'Zone à faible potentiel - Investissement déconseillé',
                'unites_count' => 8
            ]
        ];

        // Appliquer les filtres si spécifiés
        if ($request->zone_id) {
            $analysis = array_filter($analysis, function($zone) use ($request) {
                return $zone['zone_id'] == $request->zone_id;
            });
        }

        if ($request->population_min && $request->population_min > 0) {
            $analysis = array_filter($analysis, function($zone) use ($request) {
                return $zone['population_potential'] >= $request->population_min;
            });
        }

        if ($request->population_max && $request->population_max > 0) {
            $analysis = array_filter($analysis, function($zone) use ($request) {
                return $zone['population_potential'] <= $request->population_max;
            });
        }

        // Réindexer le tableau
        $analysis = array_values($analysis);

        return response()->json([
            'success' => true,
            'data' => $analysis,
            'unites_count' => array_sum(array_column($analysis, 'unites_count')),
            'zones_analyzed' => count($analysis)
        ]);
    }

    /**
     * Générer des propositions d'investissement
     */
    public function generateInvestmentProposals(Request $request)
    {
        $request->validate([
            'investment_type' => 'nullable',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
        ]);

        // Données de test pour les propositions
        $proposals = [
            [
                'zone_id' => 1,
                'zone_name' => 'Zone Centre-Ville',
                'investment_type' => 'Supermarché',
                'score' => 85,
                'estimated_revenue' => 150000000,
                'investment_cost' => 80000000,
                'roi_estimate' => 87.5,
                'timeline' => '6-12 mois',
                'risks' => ['Concurrence modérée', 'Coût d\'installation élevé']
            ],
            [
                'zone_id' => 2,
                'zone_name' => 'Zone Louly Benteigné',
                'investment_type' => 'Boutique générale',
                'score' => 70,
                'estimated_revenue' => 80000000,
                'investment_cost' => 25000000,
                'roi_estimate' => 220.0,
                'timeline' => '3-6 mois',
                'risks' => ['Risques faibles']
            ],
            [
                'zone_id' => 4,
                'zone_name' => 'Zone Route de Thiès',
                'investment_type' => 'Station-service',
                'score' => 60,
                'estimated_revenue' => 120000000,
                'investment_cost' => 100000000,
                'roi_estimate' => 20.0,
                'timeline' => '8-15 mois',
                'risks' => ['Investissement important', 'Autorisations nécessaires']
            ],
            [
                'zone_id' => 1,
                'zone_name' => 'Zone Centre-Ville',
                'investment_type' => 'Restaurant',
                'score' => 80,
                'estimated_revenue' => 90000000,
                'investment_cost' => 40000000,
                'roi_estimate' => 125.0,
                'timeline' => '4-8 mois',
                'risks' => ['Concurrence existante']
            ],
            [
                'zone_id' => 3,
                'zone_name' => 'Zone Rurale Sud',
                'investment_type' => 'Atelier mécanique',
                'score' => 55,
                'estimated_revenue' => 60000000,
                'investment_cost' => 35000000,
                'roi_estimate' => 71.4,
                'timeline' => '5-10 mois',
                'risks' => ['Population limitée', 'Accessibilité réduite']
            ]
        ];

        // Filtrer par type d'investissement si spécifié
        if ($request->investment_type) {
            $proposals = array_filter($proposals, function($proposal) use ($request) {
                return strtolower($proposal['investment_type']) === strtolower($request->investment_type);
            });
        }

        // Filtrer par budget si spécifié
        if ($request->budget_min && $request->budget_min > 0) {
            $proposals = array_filter($proposals, function($proposal) use ($request) {
                return $proposal['investment_cost'] >= $request->budget_min;
            });
        }

        if ($request->budget_max && $request->budget_max > 0) {
            $proposals = array_filter($proposals, function($proposal) use ($request) {
                return $proposal['investment_cost'] <= $request->budget_max;
            });
        }

        // Réindexer et trier par score décroissant
        $proposals = array_values($proposals);
        usort($proposals, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return response()->json([
            'success' => true,
            'proposals' => $proposals
        ]);
    }

    /**
     * Analyser le détail d'une zone
     */
    public function analyzeZoneDetail($id)
    {
        $zone = ZoneFiscale::with(['unites', 'populationZones', 'routes', 'batiments'])->findOrFail($id);
        
        $analysis = $this->analyzeZone($zone);

        return response()->json([
            'success' => true,
            'zone' => $zone,
            'analysis' => $analysis
        ]);
    }

    /**
     * API pour les données de population
     */
    public function populationData()
    {
        $populationData = PopulationZone::with('zone')
            ->select('zone_id', DB::raw('SUM(population) as total_population'))
            ->groupBy('zone_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $populationData
        ]);
    }

    /**
     * API pour les données de routes
     */
    public function roadData()
    {
        $roadData = RouteVoirie::with('zone')
            ->select('zone_id', 'importance', 'type', 'etat', 'geometrie')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roadData
        ]);
    }

    /**
     * API pour les données de bâtiments
     */
    public function batiData()
    {
        $batiData = Batiment::with('zone')
            ->select('zone_id', 'usage', 'type', 'surface_m2', 'etat', 'geometrie')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $batiData
        ]);
    }

    /**
     * Calculer les scores d'opportunité
     */
    private function calculateOpportunityScores($unites, $request)
    {
        $analysis = [];

        foreach ($unites->groupBy('zone_id') as $zoneId => $zoneUnites) {
            $zone = ZoneFiscale::find($zoneId);
            if (!$zone) continue;

            // Densité commerciale
            $commercialDensity = $this->calculateCommercialDensity($zoneUnites, $zone);
            
            // Accessibilité routière
            $roadAccessibility = $this->calculateRoadAccessibility($zone);
            
            // Potentiel bâti
            $builtPotential = $this->calculateBuiltPotential($zone);
            
            // Potentiel population
            $populationPotential = $this->calculatePopulationPotential($zone, $request);

            // Score global
            $overallScore = ($commercialDensity * 0.3) + 
                           ($roadAccessibility * 0.25) + 
                           ($builtPotential * 0.25) + 
                           ($populationPotential * 0.2);

            $analysis[] = [
                'zone_id' => $zoneId,
                'zone_name' => $zone->nom,
                'commercial_density' => round($commercialDensity, 2),
                'road_accessibility' => round($roadAccessibility, 2),
                'built_potential' => round($builtPotential, 2),
                'population_potential' => round($populationPotential, 2),
                'overall_score' => round($overallScore, 2),
                'recommendation' => $this->generateRecommendation($overallScore, $zoneUnites),
                'unites_count' => $zoneUnites->count()
            ];
        }

        // Trier par score décroissant
        usort($analysis, function($a, $b) {
            return $b['overall_score'] <=> $a['overall_score'];
        });

        return $analysis;
    }

    /**
     * Calculer la densité commerciale
     */
    private function calculateCommercialDensity($unites, $zone)
    {
        $totalUnites = $unites->count();
        $zoneArea = $zone->surface ?? 1; // km²
        
        $density = $totalUnites / $zoneArea;
        
        // Normaliser sur 100
        return min(100, $density * 10);
    }

    /**
     * Calculer l'accessibilité routière
     */
    private function calculateRoadAccessibility($zone)
    {
        $routes = RouteVoirie::where('zone_id', $zone->id)->get();
        
        if ($routes->isEmpty()) return 30; // Score de base

        $primaryRoutes = $routes->where('importance', 'primary')->count();
        $secondaryRoutes = $routes->where('importance', 'secondary')->count();
        $goodConditionRoutes = $routes->where('etat', 'excellent')->count() + 
                              $routes->where('etat', 'bon')->count();

        $score = ($primaryRoutes * 20) + ($secondaryRoutes * 10) + ($goodConditionRoutes * 5);
        
        return min(100, $score);
    }

    /**
     * Calculer le potentiel bâti
     */
    private function calculateBuiltPotential($zone)
    {
        $batiments = Batiment::where('zone_id', $zone->id)->get();
        
        if ($batiments->isEmpty()) return 40; // Score de base

        $commercialBuildings = $batiments->where('usage', 'commercial')->count();
        $availableSpace = $batiments->where('etat', 'bon')->count() + 
                         $batiments->where('etat', 'excellent')->count();
        $totalSurface = $batiments->sum('surface_m2');

        $score = ($commercialBuildings * 15) + ($availableSpace * 10) + ($totalSurface / 1000);
        
        return min(100, $score);
    }

    /**
     * Calculer le potentiel population
     */
    private function calculatePopulationPotential($zone, $request)
    {
        $population = PopulationZone::where('zone_id', $zone->id)->sum('population');
        
        if ($population == 0) return 30; // Score de base

        // Appliquer les filtres de population
        if ($request->population_min && $population < $request->population_min) {
            return 20;
        }
        
        if ($request->population_max && $population > $request->population_max) {
            return 60;
        }

        // Normaliser sur 100
        return min(100, $population / 100);
    }

    /**
     * Générer des recommandations
     */
    private function generateRecommendation($score, $unites)
    {
        if ($score >= 80) {
            return "Zone d'excellence - Investissement fortement recommandé";
        } elseif ($score >= 60) {
            return "Zone prometteuse - Investissement recommandé";
        } elseif ($score >= 40) {
            return "Zone modérée - Investissement possible avec prudence";
        } else {
            return "Zone à faible potentiel - Investissement déconseillé";
        }
    }

    /**
     * Générer des propositions d'investissement
     */
    private function generateProposals($request)
    {
        $proposals = [];
        
        // Analyser les zones selon le type d'investissement
        $zones = ZoneFiscale::with(['unites', 'populationZones'])->get();
        
        foreach ($zones as $zone) {
            $score = $this->calculateInvestmentScore($zone, $request->investment_type);
            
            if ($score > 50) { // Seuil minimum
                $proposals[] = [
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->nom,
                    'investment_type' => $request->investment_type,
                    'score' => $score,
                    'estimated_revenue' => $this->estimateRevenue($zone, $request->investment_type),
                    'investment_cost' => $this->estimateCost($request->investment_type),
                    'roi_estimate' => $this->calculateROI($zone, $request->investment_type),
                    'timeline' => '6-12 mois',
                    'risks' => $this->assessRisks($zone, $request->investment_type)
                ];
            }
        }

        // Trier par score décroissant
        usort($proposals, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($proposals, 0, 10); // Top 10
    }

    /**
     * Calculer le score d'investissement
     */
    private function calculateInvestmentScore($zone, $investmentType)
    {
        $population = $zone->populationZones->sum('population');
        $existingCompetition = $zone->unites->where('type_commerce_id', $this->getTypeId($investmentType))->count();
        
        $score = 0;
        
        // Facteur population
        $score += min(40, $population / 50);
        
        // Facteur concurrence (moins de concurrence = meilleur score)
        $score += max(0, 30 - ($existingCompetition * 5));
        
        // Facteur accessibilité
        $routes = RouteVoirie::where('zone_id', $zone->id)->count();
        $score += min(30, $routes * 3);
        
        return $score;
    }

    /**
     * Obtenir l'ID du type de commerce
     */
    private function getTypeId($investmentType)
    {
        $typeMap = [
            'alimentation' => 1,
            'atelier' => 2,
            'supermarche' => 3,
            'pharmacie' => 4,
            'restaurant' => 5
        ];
        
        return $typeMap[$investmentType] ?? 1;
    }

    /**
     * Estimer les revenus
     */
    private function estimateRevenue($zone, $investmentType)
    {
        $population = $zone->populationZones->sum('population');
        
        $revenuePerPerson = [
            'alimentation' => 5000,
            'atelier' => 3000,
            'supermarche' => 8000,
            'pharmacie' => 2000,
            'restaurant' => 4000
        ];
        
        return $population * ($revenuePerPerson[$investmentType] ?? 5000);
    }

    /**
     * Estimer les coûts
     */
    private function estimateCost($investmentType)
    {
        $costs = [
            'alimentation' => 5000000,
            'atelier' => 3000000,
            'supermarche' => 15000000,
            'pharmacie' => 8000000,
            'restaurant' => 6000000
        ];
        
        return $costs[$investmentType] ?? 5000000;
    }

    /**
     * Calculer le ROI
     */
    private function calculateROI($zone, $investmentType)
    {
        $revenue = $this->estimateRevenue($zone, $investmentType);
        $cost = $this->estimateCost($investmentType);
        
        if ($cost == 0) return 0;
        
        return round((($revenue - $cost) / $cost) * 100, 2);
    }

    /**
     * Évaluer les risques
     */
    private function assessRisks($zone, $investmentType)
    {
        $risks = [];
        
        $competition = $zone->unites->where('type_commerce_id', $this->getTypeId($investmentType))->count();
        if ($competition > 3) {
            $risks[] = "Concurrence élevée";
        }
        
        $population = $zone->populationZones->sum('population');
        if ($population < 1000) {
            $risks[] = "Population insuffisante";
        }
        
        $routes = RouteVoirie::where('zone_id', $zone->id)->count();
        if ($routes < 2) {
            $risks[] = "Accessibilité limitée";
        }
        
        return empty($risks) ? ["Risques faibles"] : $risks;
    }

    /**
     * Analyser une zone spécifique
     */
    private function analyzeZone($zone)
    {
        $unites = $zone->unites;
        $population = $zone->populationZones->sum('population');
        $routes = RouteVoirie::where('zone_id', $zone->id)->get();
        $batiments = Batiment::where('zone_id', $zone->id)->get();

        return [
            'total_unites' => $unites->count(),
            'population' => $population,
            'routes_count' => $routes->count(),
            'batiments_count' => $batiments->count(),
            'commercial_density' => $this->calculateCommercialDensity($unites, $zone),
            'road_accessibility' => $this->calculateRoadAccessibility($zone),
            'built_potential' => $this->calculateBuiltPotential($zone),
            'population_potential' => $this->calculatePopulationPotential($zone, request()),
            'top_activities' => $unites->groupBy('type_commerce_id')->map->count()->sortDesc()->take(5),
            'revenue_potential' => $this->estimateRevenue($zone, 'alimentation')
        ];
    }
}
