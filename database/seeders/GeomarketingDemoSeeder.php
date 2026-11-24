<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PopulationZone;
use App\Models\RouteVoirie;
use App\Models\Batiment;
use App\Models\ZoneFiscale;

class GeomarketingDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les zones existantes
        $zones = ZoneFiscale::all();
        
        if ($zones->isEmpty()) {
            $this->command->warn('Aucune zone fiscale trouvée. Créez d\'abord des zones fiscales.');
            return;
        }

        // Données de population par zone
        $populationData = [
            ['zone_id' => 1, 'population' => 8000, 'annee' => 2024, 'source' => 'Recensement officiel'],
            ['zone_id' => 2, 'population' => 3000, 'annee' => 2024, 'source' => 'Recensement officiel'],
            ['zone_id' => 3, 'population' => 5000, 'annee' => 2024, 'source' => 'Recensement officiel'],
            ['zone_id' => 4, 'population' => 2000, 'annee' => 2024, 'source' => 'Recensement officiel'],
            ['zone_id' => 5, 'population' => 4000, 'annee' => 2024, 'source' => 'Recensement officiel'],
        ];

        foreach ($populationData as $data) {
            if ($zones->contains('id', $data['zone_id'])) {
                PopulationZone::create($data);
            }
        }

        // Données de routes
        $routesData = [
            [
                'nom' => 'Route N1 - Axe Principal',
                'importance' => 'primary',
                'type' => 'Route nationale',
                'longueur_km' => 8.5,
                'largeur_m' => 12.0,
                'etat' => 'bon',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [[-16.800, 14.435], [-16.780, 14.435]]
                ]
            ],
            [
                'nom' => 'Route R132 - Axe Secondaire',
                'importance' => 'secondary',
                'type' => 'Route régionale',
                'longueur_km' => 3.2,
                'largeur_m' => 8.0,
                'etat' => 'bon',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [[-16.795, 14.425], [-16.795, 14.445]]
                ]
            ],
            [
                'nom' => 'Route A1 - Accès Local',
                'importance' => 'primary',
                'type' => 'Route d\'accès',
                'longueur_km' => 2.1,
                'largeur_m' => 6.0,
                'etat' => 'moyen',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [[-16.790, 14.430], [-16.785, 14.440]]
                ]
            ],
            [
                'nom' => 'Route Locale Centre',
                'importance' => 'local',
                'type' => 'Rue urbaine',
                'longueur_km' => 1.5,
                'largeur_m' => 4.0,
                'etat' => 'bon',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [[-16.792, 14.433], [-16.788, 14.437]]
                ]
            ]
        ];

        foreach ($routesData as $data) {
            RouteVoirie::create($data);
        }

        // Données de bâti
        $batimentsData = [
            [
                'nom' => 'Centre Commercial Sandiara',
                'usage' => 'commercial',
                'type' => 'Centre commercial',
                'surface_m2' => 2500.00,
                'hauteur_m' => 8.0,
                'etat' => 'excellent',
                'annee_construction' => 2020,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.793, 14.433],
                        [-16.789, 14.433],
                        [-16.789, 14.437],
                        [-16.793, 14.437],
                        [-16.793, 14.433]
                    ]]
                ]
            ],
            [
                'nom' => 'Résidence Les Palmiers',
                'usage' => 'residential',
                'type' => 'Immeuble résidentiel',
                'surface_m2' => 800.00,
                'hauteur_m' => 12.0,
                'etat' => 'bon',
                'annee_construction' => 2018,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.797, 14.427],
                        [-16.793, 14.427],
                        [-16.793, 14.431],
                        [-16.797, 14.431],
                        [-16.797, 14.427]
                    ]]
                ]
            ],
            [
                'nom' => 'Atelier Mécanique Auto',
                'usage' => 'industrial',
                'type' => 'Atelier',
                'surface_m2' => 400.00,
                'hauteur_m' => 6.0,
                'etat' => 'bon',
                'annee_construction' => 2019,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.785, 14.438],
                        [-16.781, 14.438],
                        [-16.781, 14.442],
                        [-16.785, 14.442],
                        [-16.785, 14.438]
                    ]]
                ]
            ],
            [
                'nom' => 'Pharmacie du Centre',
                'usage' => 'commercial',
                'type' => 'Pharmacie',
                'surface_m2' => 150.00,
                'hauteur_m' => 4.0,
                'etat' => 'excellent',
                'annee_construction' => 2021,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.791, 14.434],
                        [-16.789, 14.434],
                        [-16.789, 14.436],
                        [-16.791, 14.436],
                        [-16.791, 14.434]
                    ]]
                ]
            ],
            [
                'nom' => 'Restaurant Le Terroir',
                'usage' => 'commercial',
                'type' => 'Restaurant',
                'surface_m2' => 300.00,
                'hauteur_m' => 5.0,
                'etat' => 'bon',
                'annee_construction' => 2017,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.788, 14.432],
                        [-16.784, 14.432],
                        [-16.784, 14.436],
                        [-16.788, 14.436],
                        [-16.788, 14.432]
                    ]]
                ]
            ]
        ];

        foreach ($batimentsData as $data) {
            Batiment::create($data);
        }

        $this->command->info('Données de démonstration géomarketing créées avec succès !');
        $this->command->info('- ' . count($populationData) . ' enregistrements de population');
        $this->command->info('- ' . count($routesData) . ' routes créées');
        $this->command->info('- ' . count($batimentsData) . ' bâtiments créés');
    }
}



