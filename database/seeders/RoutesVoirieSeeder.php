<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RouteVoirie;

class RoutesVoirieSeeder extends Seeder
{
    public function run(): void
    {
        // Quelques segments de routes démo autour de Sandiara (coords approximatives)
        $features = [
            [
                'nom' => 'RN1 - Axe principal',
                'importance' => 'primary',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-16.9600, 14.7400],
                        [-16.9550, 14.7450],
                        [-16.9500, 14.7500],
                        [-16.9450, 14.7550]
                    ]
                ],
            ],
            [
                'nom' => 'Route secondaire Nord',
                'importance' => 'secondary',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-16.9525, 14.7480],
                        [-16.9485, 14.7485],
                        [-16.9445, 14.7490]
                    ]
                ],
            ],
            [
                'nom' => 'Rue Marché',
                'importance' => 'tertiary',
                'geometrie' => [
                    'type' => 'LineString',
                    'coordinates' => [
                        [-16.9530, 14.7460],
                        [-16.9510, 14.7445],
                        [-16.9490, 14.7430]
                    ]
                ],
            ],
        ];

        foreach ($features as $f) {
            // Idempotent: éviter les doublons si relancé
            RouteVoirie::updateOrCreate(
                ['nom' => $f['nom'], 'importance' => $f['importance']],
                ['geometrie' => $f['geometrie']]
            );
        }
    }
}
