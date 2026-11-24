<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ZoneFiscale;

class ZoneFiscaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'nom' => 'Loulibentegne',
                'description' => 'Zone fiscale de Loulibentegne - Zone Nord de Sandiara (Vert) - Couvre tout l\'espace bleu',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.825, 14.455],  // Nord-Ouest du contour bleu
                        [-16.810, 14.460],  // Courbe Nord-Ouest
                        [-16.790, 14.465],  // Nord
                        [-16.770, 14.460],  // Nord-Est du contour bleu
                        [-16.760, 14.440],  // Est
                        [-16.770, 14.410],  // Limite Sud-Est de cette zone
                        [-16.790, 14.420],  // Limite Centre-Est de cette zone
                        [-16.810, 14.430],  // Limite Centre-Ouest de cette zone
                        [-16.825, 14.455]   // Retour au point de départ
                    ]]
                ],
            ],
            [
                'nom' => 'Sandiara Centre',
                'description' => 'Zone fiscale de Sandiara Centre - Zone Centrale de Sandiara (Rose) - Couvre tout l\'espace bleu',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.810, 14.430],  // Limite Nord-Ouest de cette zone
                        [-16.790, 14.420],  // Limite Nord-Est de cette zone
                        [-16.770, 14.410],  // Limite Est de cette zone
                        [-16.755, 14.400],  // Est-Sud-Est du contour bleu
                        [-16.770, 14.350],  // Limite Sud-Est de cette zone
                        [-16.790, 14.340],  // Limite Centre-Sud de cette zone
                        [-16.810, 14.350],  // Limite Sud-Ouest de cette zone
                        [-16.810, 14.430]   // Retour au point de départ
                    ]]
                ],
            ],
            [
                'nom' => 'Ndioukhthiorokh',
                'description' => 'Zone fiscale de Ndioukhthiorokh - Zone Sud de Sandiara (Bleu) - Couvre tout l\'espace bleu',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.810, 14.350],  // Limite Nord-Ouest de cette zone
                        [-16.790, 14.340],  // Limite Nord-Est de cette zone
                        [-16.770, 14.350],  // Limite Est de cette zone
                        [-16.765, 14.290],  // Sud-Est du contour bleu
                        [-16.790, 14.285],  // Sud
                        [-16.830, 14.295],  // Sud-Ouest du contour bleu
                        [-16.810, 14.350]   // Retour au point de départ
                    ]]
                ],
            ],
        ];

        foreach ($zones as $zone) {
            // Mettre à jour ou créer la zone
            ZoneFiscale::updateOrCreate(
                ['nom' => $zone['nom']],
                $zone
            );
        }
    }
}
