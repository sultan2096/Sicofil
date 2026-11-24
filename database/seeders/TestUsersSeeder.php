<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Commune;
use App\Models\ZoneFiscale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer ou créer une commune par défaut
        $commune = Commune::first();
        if (!$commune) {
            $commune = Commune::create([
                'nom' => 'Commune Test',
                'code' => 'TEST',
                'acronyme' => 'TEST',
                'region' => 'Dakar',
                'departement' => 'Dakar',
            ]);
        }

        // Récupérer ou créer une zone par défaut
        $zone = ZoneFiscale::where('commune_id', $commune->id)->first();
        if (!$zone) {
            $zone = ZoneFiscale::create([
                'nom' => 'Zone Test',
                'commune_id' => $commune->id,
            ]);
        }

        // Créer les utilisateurs professionnels
        $users = [
            [
                'name' => 'Maire SICOFIL',
                'email' => 'maire@sicofil.sn',
                'password' => Hash::make('maire123'),
                'role' => 'maire',
                'commune_id' => $commune->id,
                'zone_id' => null,
                'identifiant' => 'maire',
            ],
            [
                'name' => 'Gestionnaire SICOFIL',
                'email' => 'gestionnaire@sicofil.sn',
                'password' => Hash::make('gest123'),
                'role' => 'gestionnaire',
                'commune_id' => $commune->id,
                'zone_id' => null,
                'identifiant' => 'gestionnaire',
            ],
            [
                'name' => 'Collecteur SICOFIL',
                'email' => 'collecteur@sicofil.sn',
                'password' => Hash::make('coll123'),
                'role' => 'collecteur',
                'commune_id' => $commune->id,
                'zone_id' => $zone->id,
                'identifiant' => 'collecteur',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Utilisateurs professionnels créés avec succès.');
        $this->command->info('Emails: maire@sicofil.sn, gestionnaire@sicofil.sn, collecteur@sicofil.sn');
        $this->command->info('Mots de passe: maire123, gest123, coll123');
    }
}
