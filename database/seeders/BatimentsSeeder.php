<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Batiment;

class BatimentsSeeder extends Seeder
{
    public function run(): void
    {
        // Quelques bâtiments démo (polygones simples)
        $items = [
            [
                'nom' => 'Marché Central',
                'usage' => 'commercial',
                'surface_m2' => 2500,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.79280, 14.43590],
                        [-16.79220, 14.43590],
                        [-16.79220, 14.43540],
                        [-16.79280, 14.43540],
                        [-16.79280, 14.43590]
                    ]]
                ],
            ],
            [
                'nom' => 'Mairie de Sandiara',
                'usage' => 'public',
                'surface_m2' => 1200,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.79350, 14.43490],
                        [-16.79290, 14.43490],
                        [-16.79290, 14.43440],
                        [-16.79350, 14.43440],
                        [-16.79350, 14.43490]
                    ]]
                ],
            ],
            [
                'nom' => 'Centre de Santé',
                'usage' => 'public',
                'surface_m2' => 1600,
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.79190, 14.43630],
                        [-16.79120, 14.43630],
                        [-16.79120, 14.43580],
                        [-16.79190, 14.43580],
                        [-16.79190, 14.43630]
                    ]]
                ],
            ],
        ];

        foreach ($items as $it) {
            Batiment::updateOrCreate(
                ['nom' => $it['nom'], 'usage' => $it['usage']],
                [
                    'surface_m2' => $it['surface_m2'],
                    'geometrie' => $it['geometrie'],
                ]
            );
        }
    }
}
