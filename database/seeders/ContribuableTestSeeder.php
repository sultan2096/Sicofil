<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Contribuable;
use App\Models\UniteCommerciale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ContribuableTestSeeder extends Seeder
{
    public function run(): void
    {
        $commune = Commune::where('actif', true)->first();
        if (!$commune) {
            $this->command->warn('Aucune commune active trouvée.');
            return;
        }

        // Chercher une unité commerciale existante
        $unite = UniteCommerciale::where('commune_id', $commune->id)->first();
        if (!$unite) {
            $this->command->warn('Aucune unité commerciale trouvée.');
            return;
        }

        $contribuable = Contribuable::updateOrCreate(
            ['telephone' => '771234567'],
            [
                'commune_id' => $commune->id,
                'unite_commerciale_id' => $unite->id,
                'nom_complet' => 'Moussa Diop',
                'email' => 'moussa.diop@example.com',
                'password' => Hash::make('contrib123'),
                'actif' => true,
                'verified_at' => now(),
            ]
        );

        $this->command->info("Contribuable test créé : telephone=771234567, OTP=dispo via login");
    }
}
