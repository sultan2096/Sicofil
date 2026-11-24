<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NatureActivite;

class NatureActiviteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $natures = [
            // Activités commerciales
            [
                'nom' => 'Boutique ou Magasin',
                'description' => 'Commerce de détail général, alimentation, mercerie, etc.',
                'categorie' => 'activite_commerciale',
                'ordre' => 1,
                'actif' => true
            ],
            [
                'nom' => 'Atelier',
                'description' => 'Ateliers de production et services artisanaux',
                'categorie' => 'activite_commerciale',
                'ordre' => 2,
                'actif' => true
            ],
            [
                'nom' => 'Agriculture',
                'description' => 'Élevage et activités agricoles',
                'categorie' => 'activite_commerciale',
                'ordre' => 3,
                'actif' => true
            ],
            [
                'nom' => 'Transport',
                'description' => 'Services de transport de personnes et marchandises',
                'categorie' => 'activite_commerciale',
                'ordre' => 4,
                'actif' => true
            ],
            [
                'nom' => 'Étal',
                'description' => 'Vente sur étal de marché',
                'categorie' => 'activite_commerciale',
                'ordre' => 5,
                'actif' => true
            ],
            [
                'nom' => 'Table ou Tente commerciale',
                'description' => 'Commerce ambulant sur table ou sous tente',
                'categorie' => 'activite_commerciale',
                'ordre' => 6,
                'actif' => true
            ],
            [
                'nom' => 'Cantine ou Kiosque',
                'description' => 'Restauration et vente de boissons',
                'categorie' => 'activite_commerciale',
                'ordre' => 7,
                'actif' => true
            ],
            [
                'nom' => 'Entreprise',
                'description' => 'Grandes entreprises et services industriels',
                'categorie' => 'activite_commerciale',
                'ordre' => 8,
                'actif' => true
            ],

            // Points d'intérêt commercial
            [
                'nom' => 'Foncier',
                'description' => 'Terrains et propriétés foncières (parcelles, bâtiments)',
                'categorie' => 'point_interet',
                'ordre' => 1,
                'actif' => true
            ],
            [
                'nom' => 'Publicité',
                'description' => 'Supports publicitaires et enseignes',
                'categorie' => 'point_interet',
                'ordre' => 2,
                'actif' => true
            ],
            [
                'nom' => 'Spectacles',
                'description' => 'Lieux dédiés aux événements et spectacles',
                'categorie' => 'point_interet',
                'ordre' => 3,
                'actif' => true
            ],
            [
                'nom' => 'Papiers administratifs',
                'description' => 'Services de documentation et formalités administratives',
                'categorie' => 'point_interet',
                'ordre' => 4,
                'actif' => true
            ]
        ];

        foreach ($natures as $nature) {
            NatureActivite::create($nature);
        }

        $this->command->info('Natures d\'activité créées avec succès !');
    }
}
