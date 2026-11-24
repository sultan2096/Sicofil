<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeCommerce;

class TypeCommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typesCommerce = [
            [
                'nom' => 'Alimentation générale',
                'description' => 'Commerce de produits alimentaires et de première nécessité',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 1
            ],
            [
                'nom' => 'Restaurant/Gargotte',
                'description' => 'Établissement de restauration et de vente de plats préparés',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 2
            ],
            [
                'nom' => 'Salon de coiffure',
                'description' => 'Salon de coiffure et d\'esthétique',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 3
            ],
            [
                'nom' => 'Couture/Tailleur',
                'description' => 'Atelier de couture et de confection',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 4
            ],
            [
                'nom' => 'Mécanique/Garage',
                'description' => 'Garage et atelier de réparation automobile',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 5
            ],
            [
                'nom' => 'Pharmacie',
                'description' => 'Officine pharmaceutique et vente de médicaments',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 6
            ],
            [
                'nom' => 'Boulangerie',
                'description' => 'Boulangerie et pâtisserie',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 7
            ],
            [
                'nom' => 'Quincaillerie',
                'description' => 'Vente d\'outils, matériaux et équipements de bricolage',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 8
            ],
            [
                'nom' => 'Télécentre',
                'description' => 'Centre de services de télécommunication et internet',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 9
            ],
            [
                'nom' => 'Transport',
                'description' => 'Activités de transport de personnes et de marchandises',
                'is_transport' => true,
                'actif' => true,
                'ordre' => 10
            ],
            [
                'nom' => 'Vêtements',
                'description' => 'Commerce de vêtements et d\'accessoires',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 11
            ],
            [
                'nom' => 'Électronique',
                'description' => 'Vente et réparation d\'appareils électroniques',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 12
            ],
            [
                'nom' => 'Services',
                'description' => 'Prestations de services divers',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 13
            ],
            [
                'nom' => 'Artisanat',
                'description' => 'Activités artisanales et productions locales',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 14
            ],
            [
                'nom' => 'Autre',
                'description' => 'Autres types de commerce non spécifiés',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 99
            ]
        ];

        foreach ($typesCommerce as $type) {
            TypeCommerce::create($type);
        }

        $this->command->info('Types de commerce créés avec succès !');
    }
}

                'description' => 'Commerce de produits alimentaires et de première nécessité',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 1
            ],
            [
                'nom' => 'Restaurant/Gargotte',
                'description' => 'Établissement de restauration et de vente de plats préparés',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 2
            ],
            [
                'nom' => 'Salon de coiffure',
                'description' => 'Salon de coiffure et d\'esthétique',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 3
            ],
            [
                'nom' => 'Couture/Tailleur',
                'description' => 'Atelier de couture et de confection',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 4
            ],
            [
                'nom' => 'Mécanique/Garage',
                'description' => 'Garage et atelier de réparation automobile',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 5
            ],
            [
                'nom' => 'Pharmacie',
                'description' => 'Officine pharmaceutique et vente de médicaments',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 6
            ],
            [
                'nom' => 'Boulangerie',
                'description' => 'Boulangerie et pâtisserie',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 7
            ],
            [
                'nom' => 'Quincaillerie',
                'description' => 'Vente d\'outils, matériaux et équipements de bricolage',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 8
            ],
            [
                'nom' => 'Télécentre',
                'description' => 'Centre de services de télécommunication et internet',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 9
            ],
            [
                'nom' => 'Transport',
                'description' => 'Activités de transport de personnes et de marchandises',
                'is_transport' => true,
                'actif' => true,
                'ordre' => 10
            ],
            [
                'nom' => 'Vêtements',
                'description' => 'Commerce de vêtements et d\'accessoires',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 11
            ],
            [
                'nom' => 'Électronique',
                'description' => 'Vente et réparation d\'appareils électroniques',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 12
            ],
            [
                'nom' => 'Services',
                'description' => 'Prestations de services divers',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 13
            ],
            [
                'nom' => 'Artisanat',
                'description' => 'Activités artisanales et productions locales',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 14
            ],
            [
                'nom' => 'Autre',
                'description' => 'Autres types de commerce non spécifiés',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 99
            ]
        ];

        foreach ($typesCommerce as $type) {
            TypeCommerce::create($type);
        }

        $this->command->info('Types de commerce créés avec succès !');
    }
}

                'description' => 'Commerce de produits alimentaires et de première nécessité',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 1
            ],
            [
                'nom' => 'Restaurant/Gargotte',
                'description' => 'Établissement de restauration et de vente de plats préparés',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 2
            ],
            [
                'nom' => 'Salon de coiffure',
                'description' => 'Salon de coiffure et d\'esthétique',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 3
            ],
            [
                'nom' => 'Couture/Tailleur',
                'description' => 'Atelier de couture et de confection',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 4
            ],
            [
                'nom' => 'Mécanique/Garage',
                'description' => 'Garage et atelier de réparation automobile',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 5
            ],
            [
                'nom' => 'Pharmacie',
                'description' => 'Officine pharmaceutique et vente de médicaments',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 6
            ],
            [
                'nom' => 'Boulangerie',
                'description' => 'Boulangerie et pâtisserie',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 7
            ],
            [
                'nom' => 'Quincaillerie',
                'description' => 'Vente d\'outils, matériaux et équipements de bricolage',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 8
            ],
            [
                'nom' => 'Télécentre',
                'description' => 'Centre de services de télécommunication et internet',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 9
            ],
            [
                'nom' => 'Transport',
                'description' => 'Activités de transport de personnes et de marchandises',
                'is_transport' => true,
                'actif' => true,
                'ordre' => 10
            ],
            [
                'nom' => 'Vêtements',
                'description' => 'Commerce de vêtements et d\'accessoires',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 11
            ],
            [
                'nom' => 'Électronique',
                'description' => 'Vente et réparation d\'appareils électroniques',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 12
            ],
            [
                'nom' => 'Services',
                'description' => 'Prestations de services divers',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 13
            ],
            [
                'nom' => 'Artisanat',
                'description' => 'Activités artisanales et productions locales',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 14
            ],
            [
                'nom' => 'Autre',
                'description' => 'Autres types de commerce non spécifiés',
                'is_transport' => false,
                'actif' => true,
                'ordre' => 99
            ]
        ];

        foreach ($typesCommerce as $type) {
            TypeCommerce::create($type);
        }

        $this->command->info('Types de commerce créés avec succès !');
    }
}
