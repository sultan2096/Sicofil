<?php

namespace Database\Seeders;

use App\Models\UniteCommerciale;
use App\Models\ZoneFiscale;
use App\Models\Taxe;
use App\Models\Commune;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniteCommercialeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer les unités existantes pour éviter les doublons
        // D'abord supprimer les paiements liés
        UniteCommerciale::truncate();
        
        // Récupérer les zones et taxes existantes
        $zones = ZoneFiscale::all();
        $taxes = Taxe::all();

        if ($zones->count() == 0 || $taxes->count() == 0) {
            $this->command->warn('Aucune zone fiscale ou taxe trouvée. Veuillez d\'abord exécuter les autres seeders.');
            return;
        }

        // Coordonnées précises dans les limites de Sandiara
        // Limites: Nord: 14.44382, Sud: 14.42699, Est: -16.78059, Ouest: -16.80247
        $unitesData = [
            [
                'nom_proprietaire' => 'Mamadou Diop',
                'type_commerce' => 'Boutique générale',
                'nature' => 'Boutique',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Centre-ville Sandiara',
                'telephone' => '77 123 45 67',
                'latitude' => 14.43540,  // Centre de Sandiara
                'longitude' => -16.79153,
                'coordinates' => ['lat' => 14.43540, 'lng' => -16.79153],
                'zone_id' => $zones->first()->id,
                'taxe_id' => $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Fatou Sall',
                'type_commerce' => 'Salon de coiffure',
                'nature' => 'Salon',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Quartier Louly Benteigné',
                'telephone' => '70 987 65 43',
                'latitude' => 14.44200,  // Nord de Sandiara
                'longitude' => -16.79500,
                'coordinates' => ['lat' => 14.44200, 'lng' => -16.79500],
                'zone_id' => $zones->skip(1)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(1)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Ibrahima Ndiaye',
                'type_commerce' => 'Marché hebdomadaire',
                'nature' => 'Marché',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Place du marché Sandiara',
                'telephone' => '76 555 44 33',
                'latitude' => 14.43300,  // Centre-Sud
                'longitude' => -16.79000,
                'coordinates' => ['lat' => 14.43300, 'lng' => -16.79000],
                'zone_id' => $zones->skip(2)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(2)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Aminata Ba',
                'type_commerce' => 'Restaurant traditionnel',
                'nature' => 'Restaurant',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Route nationale Sandiara',
                'telephone' => '78 111 22 33',
                'latitude' => 14.43800,  // Nord-Centre
                'longitude' => -16.78500,
                'coordinates' => ['lat' => 14.43800, 'lng' => -16.78500],
                'zone_id' => $zones->first()->id,
                'taxe_id' => $taxes->skip(3)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Moussa Diouf',
                'type_commerce' => 'Atelier mécanique',
                'nature' => 'Atelier',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Louly Diakher',
                'telephone' => '77 666 77 88',
                'latitude' => 14.43000,  // Sud-Centre
                'longitude' => -16.78800,
                'coordinates' => ['lat' => 14.43000, 'lng' => -16.78800],
                'zone_id' => $zones->skip(1)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(4)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Khadija Thiam',
                'type_commerce' => 'Pharmacie',
                'nature' => 'Pharmacie',
                'categorie' => 'point_interet',
                'adresse' => 'Centre de santé Sandiara',
                'telephone' => '33 123 45 67',
                'latitude' => 14.43600,  // Centre-Nord
                'longitude' => -16.79200,
                'coordinates' => ['lat' => 14.43600, 'lng' => -16.79200],
                'zone_id' => $zones->first()->id,
                'taxe_id' => $taxes->skip(5)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Ousmane Fall',
                'type_commerce' => 'Station-service',
                'nature' => 'Station',
                'categorie' => 'point_interet',
                'adresse' => 'Entrée Sandiara RN1',
                'telephone' => '33 987 65 43',
                'latitude' => 14.43900,  // Proche de la route
                'longitude' => -16.79100,
                'coordinates' => ['lat' => 14.43900, 'lng' => -16.79100],
                'zone_id' => $zones->skip(2)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(6)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Aïssatou Sarr',
                'type_commerce' => 'Boulangerie',
                'nature' => 'Boulangerie',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Louly Mbaye',
                'telephone' => '70 444 55 66',
                'latitude' => 14.42900,  // Sud
                'longitude' => -16.79400,
                'coordinates' => ['lat' => 14.42900, 'lng' => -16.79400],
                'zone_id' => $zones->skip(1)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(7)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Cheikh Sy',
                'type_commerce' => 'Épicerie',
                'nature' => 'Épicerie',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Louly Sarr',
                'telephone' => '77 888 99 00',
                'latitude' => 14.44100,  // Nord
                'longitude' => -16.78200,
                'coordinates' => ['lat' => 14.44100, 'lng' => -16.78200],
                'zone_id' => $zones->skip(3)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(8)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Mariama Cissé',
                'type_commerce' => 'Atelier de couture',
                'nature' => 'Atelier',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Louly Mbacké',
                'telephone' => '70 333 44 55',
                'latitude' => 14.43200,  // Centre-Sud
                'longitude' => -16.80000,
                'coordinates' => ['lat' => 14.43200, 'lng' => -16.80000],
                'zone_id' => $zones->skip(4)->first()->id ?? $zones->first()->id,
                'taxe_id' => $taxes->skip(9)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Babacar Faye',
                'type_commerce' => 'Quincaillerie',
                'nature' => 'Quincaillerie',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Centre commercial Sandiara',
                'telephone' => '33 777 88 99',
                'latitude' => 14.43700,  // Centre
                'longitude' => -16.79050,
                'coordinates' => ['lat' => 14.43700, 'lng' => -16.79050],
                'zone_id' => $zones->first()->id,
                'taxe_id' => $taxes->skip(10)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ],
            [
                'nom_proprietaire' => 'Ndeye Awa Diallo',
                'type_commerce' => 'Cybercafé',
                'nature' => 'Cybercafé',
                'categorie' => 'point_interet',
                'adresse' => 'Près de la mairie Sandiara',
                'telephone' => '78 222 33 44',
                'latitude' => 14.43450,  // Centre administratif
                'longitude' => -16.79250,
                'coordinates' => ['lat' => 14.43450, 'lng' => -16.79250],
                'zone_id' => $zones->first()->id,
                'taxe_id' => $taxes->skip(11)->first()->id ?? $taxes->first()->id,
                'actif' => true
            ]
        ];

        foreach ($unitesData as $data) {
            $zone = $zones->firstWhere('id', $data['zone_id']) ?? $zones->first();
            $data['commune_id'] = $zone?->commune_id ?? Commune::where('code', 'SANDIARA')->value('id');
            $data['date_recensement'] = $data['date_recensement'] ?? now()->subDays(rand(5, 60));
            UniteCommerciale::create($data);
            $this->command->info("Unité commerciale créée: {$data['nom_proprietaire']} - {$data['type_commerce']} ({$data['latitude']}, {$data['longitude']})");
        }

        $this->command->info('Seeder UniteCommerciale terminé: ' . count($unitesData) . ' unités commerciales créées avec géolocalisation précise dans les limites de Sandiara.');
    }
} 