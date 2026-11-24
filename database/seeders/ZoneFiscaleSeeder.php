<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ZoneFiscale;
use App\Models\Commune;

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
                'description' => 'Zone fiscale de Loulibentegne - Zone Nord de Sandiara (Vert) - Villages: Kouthie Wolof, Kibik, Ngomène, Louly Mbafay, Louly Benteigné, Louly Sindiane, Mbourokh, Nianiar, Louly Ngogom',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.920000, 14.450000],  // Point ouest (réduit pour laisser place au rose)
                        [-16.910000, 14.460000],  // Point ouest-nord (réduit pour laisser place au rose)
                        [-16.900000, 14.470000],  // Point ouest-nord (réduit pour laisser place au rose)
                        [-16.890000, 14.480000],  // Point ouest-nord (réduit pour laisser place au rose)
                        [-16.880000, 14.490000],  // Point ouest-nord (réduit pour laisser place au rose)
                        [-16.870000, 14.500000],  // Point nord-ouest (réduit pour laisser place au rose)
                        [-16.850000, 14.500000],  // Point nord
                        [-16.800000, 14.490000],  // Point nord-est (forme irrégulière)
                        [-16.750000, 14.480000],  // Point est
                        [-16.760000, 14.460000],  // Point est-sud (forme irrégulière)
                        [-16.780000, 14.450000],  // Point centre-est (réduit pour laisser place au rose)
                        [-16.820000, 14.440000],  // Point centre (réduit pour laisser place au rose)
                        [-16.860000, 14.445000],  // Point centre-ouest (réduit pour laisser place au rose)
                        [-16.900000, 14.450000],  // Point ouest-centre (forme lisse)
                        [-16.920000, 14.450000]   // Retour au point de départ
                    ]]
                ],
            ],
            [
                'nom' => 'Sandiara Centre',
                'description' => 'Zone fiscale de Sandiara Centre - Zone Centrale de Sandiara (Rose) - Villages: Faylar 1, Faylar 2, Sandiara Sérère, Sandiara Wolof, Garage Diakher, Soukhem, Godaguène, Ndiouk Fissel',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.860000, 14.445000],  // Point nord-ouest (joint le vert)
                        [-16.820000, 14.440000],  // Point nord-centre (joint le vert)
                        [-16.780000, 14.450000],  // Point nord-est (joint le vert)
                        [-16.760000, 14.460000],  // Point est-nord (forme irrégulière)
                        [-16.750000, 14.480000],  // Point est
                        [-16.760000, 14.420000],  // Point est-sud (forme irrégulière)
                        [-16.780000, 14.400000],  // Point sud-est (forme irrégulière)
                        [-16.820000, 14.390000],  // Point sud-centre (joint le bleu)
                        [-16.860000, 14.400000],  // Point sud-ouest (joint le bleu)
                        [-16.880000, 14.410000],  // Point ouest-sud (réduit pour laisser place au bleu)
                        [-16.900000, 14.420000],  // Point ouest (réduit pour laisser place au bleu)
                        [-16.920000, 14.430000],  // Point ouest-centre (réduit pour laisser place au bleu)
                        [-16.940000, 14.440000],  // Point ouest-nord (agrandi au détriment du vert)
                        [-16.960000, 14.445000],  // Point ouest-nord (agrandi au détriment du vert)
                        [-16.860000, 14.445000]   // Retour au point de départ
                    ]]
                ],
            ],
            [
                'nom' => 'Ndioukhthiorokh',
                'description' => 'Zone fiscale de Ndioukh Thiorokh - Zone Sud de Sandiara (Bleu) - Villages: Ndioukh Thiorokh, Samane, Louly Ndia, Keur Diba, Soussane, Ndollor',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.860000, 14.400000],  // Point nord-ouest (joint le rose)
                        [-16.820000, 14.390000],  // Point nord-centre (joint le rose)
                        [-16.780000, 14.400000],  // Point nord-est (joint le rose)
                        [-16.760000, 14.420000],  // Point est-nord (forme irrégulière)
                        [-16.750000, 14.480000],  // Point est
                        [-16.760000, 14.380000],  // Point est-sud (forme irrégulière)
                        [-16.780000, 14.360000],  // Point sud-est (forme irrégulière)
                        [-16.820000, 14.350000],  // Point sud-centre (forme irrégulière)
                        [-16.860000, 14.360000],  // Point sud-ouest (forme lisse)
                        [-16.900000, 14.370000],  // Point ouest-sud (forme lisse)
                        [-16.920000, 14.380000],  // Point ouest (forme lisse)
                        [-16.940000, 14.390000],  // Point ouest-centre (forme lisse)
                        [-16.960000, 14.400000],  // Point ouest-centre (forme lisse)
                        [-16.880000, 14.410000],  // Point ouest-nord (agrandi au détriment du rose)
                        [-16.860000, 14.400000]   // Retour au point de départ
                    ]]
                ],
            ],
        ];
        
        $sandiara = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first();
        foreach ($zones as $zone) {
            $zone['commune_id'] = $sandiara?->id;
            ZoneFiscale::updateOrCreate(
                ['nom' => $zone['nom']],
                $zone
            );
        }
    }
}