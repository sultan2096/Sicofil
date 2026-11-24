<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Abonnement;
use Illuminate\Database\Seeder;

/**
 * Crée la commune "Nouvelle commune" dans la région de Thiès + son abonnement.
 * Après création, vous pourrez affecter zones fiscales, taxes et utilisateurs
 * (maire, gestionnaire, collecteurs) depuis l'interface de la commune.
 */
class CommuneThiesSeeder extends Seeder
{
    public function run(): void
    {
        $commune = Commune::firstOrCreate(
            ['code' => 'THIES'],
            [
                'nom' => 'Thiès',
                'acronyme' => 'SICOTH',
                'region' => 'Thiès',
                'departement' => 'Thiès',
                'actif' => true,
                'description' => 'Commune de Thiès. Interface SICOTH — mêmes fonctionnalités que SICOSA, données de Thiès uniquement.',
            ]
        );
        // Mettre à jour acronyme et nom si la commune existait déjà (créée sans acronyme)
        if ($commune->acronyme !== 'SICOTH' || $commune->nom === 'Nouvelle commune') {
            $commune->update(['acronyme' => 'SICOTH', 'nom' => 'Thiès']);
        }

        if (!$commune->abonnement) {
            Abonnement::create([
                'commune_id' => $commune->id,
                'statut' => 'actif',
                'formule' => 'standard',
                'date_debut' => now(),
                'date_expiration' => now()->addYear(),
                'montant_fixe_annuel' => 0,
                'pourcentage_recettes' => 0,
            ]);
            $this->command->info('Abonnement créé pour « Nouvelle commune ».');
        }

        $this->command->info("Commune « {$commune->nom} » (région Thiès) prête. Code: {$commune->code}.");
    }
}
