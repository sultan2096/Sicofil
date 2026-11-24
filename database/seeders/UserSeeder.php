<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ZoneFiscale;
use App\Models\Commune;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $communeSandiara = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first();

        // Créer/mettre à jour le maire (idempotent)
        User::updateOrCreate(
            ['identifiant' => 'maire2025'],
            [
                'name' => 'Aliou Gning',
                'email' => 'maire@sicosa.sn',
                'password' => Hash::make('sicosa2025'),
                'role' => 'maire',
                'commune_id' => $communeSandiara?->id,
                'zone_id' => null,
            ]
        );

        // Créer/mettre à jour un gestionnaire (idempotent)
        User::updateOrCreate(
            ['identifiant' => 'gestionnaire01'],
            [
                'name' => 'Gestionnaire Principal',
                'email' => 'gestionnaire@sicosa.sn',
                'password' => Hash::make('gestionnaire2025'),
                'role' => 'gestionnaire',
                'commune_id' => $communeSandiara?->id,
                'zone_id' => null,
            ]
        );

        // Créer des collecteurs (un par zone)
        $zones = ZoneFiscale::all();
        $collecteurs = [
            ['name' => 'Gueyki Faye', 'identifiant' => 'collecteur01', 'email' => 'collecteur01@sicosa.sn'],
            ['name' => 'Ibrahima Diop', 'identifiant' => 'collecteur02', 'email' => 'collecteur02@sicosa.sn'],
            ['name' => 'Fatou Sow', 'identifiant' => 'collecteur03', 'email' => 'collecteur03@sicosa.sn'],
            ['name' => 'Mamadou Ba', 'identifiant' => 'collecteur04', 'email' => 'collecteur04@sicosa.sn'],
            ['name' => 'Aissatou Ndiaye', 'identifiant' => 'collecteur05', 'email' => 'collecteur05@sicosa.sn'],
        ];

        foreach ($zones as $index => $zone) {
            if (isset($collecteurs[$index])) {
                User::updateOrCreate(
                    ['identifiant' => $collecteurs[$index]['identifiant']],
                    [
                        'name' => $collecteurs[$index]['name'],
                        'email' => $collecteurs[$index]['email'],
                        'password' => Hash::make('collecteur2025'),
                        'role' => 'collecteur',
                        'commune_id' => $zone->commune_id ?? $communeSandiara?->id,
                        'zone_id' => $zone->id,
                    ]
                );
            }
        }

        // Administrateur SICOFIL — Gestion multi-communes (voit toutes les communes)
        User::updateOrCreate(
            ['identifiant' => 'admin_sicofil'],
            [
                'name' => 'Admin SICOFIL',
                'email' => 'admin@sicofil.sn',
                'password' => Hash::make('sicofil2025'),
                'role' => 'admin_sicofil',
                'commune_id' => null,
                'zone_id' => null,
            ]
        );
    }
} 