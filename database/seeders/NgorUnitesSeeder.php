<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\UniteCommerciale;
use App\Models\ZoneFiscale;
use Illuminate\Database\Seeder;

class NgorUnitesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupérer ou créer la commune Ngor
        $commune = Commune::firstOrCreate(
            ['nom' => 'Ngor'],
            [
                'code' => 'NGOR',
                'region' => 'Dakar',
                'departement' => 'Dakar',
                'actif' => true,
            ]
        );

        $this->command->info("Commune : {$commune->nom} (ID: {$commune->id})");

        // 2. Récupérer ou créer une zone fiscale par défaut pour Ngor
        $zone = ZoneFiscale::firstOrCreate(
            ['nom' => 'Zone par défaut Ngor', 'commune_id' => $commune->id],
            [
                'description' => 'Zone fiscale par défaut pour la commune de Ngor',
                'commune_id'  => $commune->id,
            ]
        );

        $this->command->info("Zone fiscale : {$zone->nom} (ID: {$zone->id})");

        // 3. Données brutes des unités commerciales recensées à Ngor
        $unites = [
            ['nom_proprietaire' => 'Aliou Dieye',      'telephone' => '771234560',  'nature' => 'foirail',                    'latitude' => 14.7424192,            'longitude' => -17.5135171],
            ['nom_proprietaire' => 'Fatou Mass Camara', 'telephone' => '778901134',  'nature' => 'atelier',                    'latitude' => 14.741692904096265,    'longitude' => -17.516607004631105],
            ['nom_proprietaire' => 'Saliou Diagne',     'telephone' => '701204560',  'nature' => 'cantine ou kiosque',          'latitude' => 14.7424192,            'longitude' => -17.5135171],
            ['nom_proprietaire' => 'Ablaye Diop',       'telephone' => '757001134',  'nature' => 'stationnement',               'latitude' => 14.741692904096265,    'longitude' => -17.516607004631105],
            ['nom_proprietaire' => 'Moussa Ndiaye',     'telephone' => '775551234',  'nature' => 'boutique ou magasin',         'latitude' => 14.743120,              'longitude' => -17.514820],
            ['nom_proprietaire' => 'Awa Sarr',          'telephone' => '764449876',  'nature' => 'table ou tente commerciale',  'latitude' => 14.741950,              'longitude' => -17.512310],
            ['nom_proprietaire' => 'Ousmane Seck',      'telephone' => '782223344',  'nature' => 'atelier',                    'latitude' => 14.745600,              'longitude' => -17.519100],
            ['nom_proprietaire' => 'Khady Fall',        'telephone' => '708881122',  'nature' => 'etal',                       'latitude' => 14.742880,              'longitude' => -17.515400],
            ['nom_proprietaire' => 'Cheikh Tidiane Sy', 'telephone' => '751114455',  'nature' => 'entreprise ou service',       'latitude' => 14.740500,              'longitude' => -17.511200],
            ['nom_proprietaire' => 'Aminata Diallo',    'telephone' => '773336677',  'nature' => 'atelier',                    'latitude' => 14.744150,              'longitude' => -17.517350],
            ['nom_proprietaire' => 'Babacar Wade',      'telephone' => '769990011',  'nature' => 'atelier',                    'latitude' => 14.742100,              'longitude' => -17.513900],
            ['nom_proprietaire' => 'Mariama Ba',        'telephone' => '786665544',  'nature' => 'spectacles',                 'latitude' => 14.741250,              'longitude' => -17.516120],
            ['nom_proprietaire' => 'Ibrahima Faye',     'telephone' => '707778899',  'nature' => 'boutique ou magasin',         'latitude' => 14.743800,              'longitude' => -17.514150],
            ['nom_proprietaire' => 'Penda Sow',         'telephone' => '772228833',  'nature' => 'cantine ou kiosque',          'latitude' => 14.742550,              'longitude' => -17.513050],
        ];

        $inserted = 0;
        $skipped  = 0;

        foreach ($unites as $u) {
            $exists = UniteCommerciale::where('nom_proprietaire', $u['nom_proprietaire'])
                ->where('telephone', $u['telephone'])
                ->where('commune_id', $commune->id)
                ->first();

            if ($exists) {
                $this->command->warn("Déjà existant : {$u['nom_proprietaire']} — SKIP");
                $skipped++;
                continue;
            }

            UniteCommerciale::create([
                'nom_proprietaire' => $u['nom_proprietaire'],
                'telephone'        => $u['telephone'],
                'nature'           => $u['nature'],
                'type_commerce'    => $u['nature'], // par défaut = nature
                'categorie'        => 'activite_commerciale',
                'adresse'          => 'Ngor',
                'latitude'         => $u['latitude'],
                'longitude'        => $u['longitude'],
                'coordinates'      => ['lat' => $u['latitude'], 'lng' => $u['longitude']],
                'commune_id'       => $commune->id,
                'zone_id'          => $zone->id,
                'actif'            => true,
            ]);

            $this->command->info("Inséré : {$u['nom_proprietaire']}");
            $inserted++;
        }

        $this->command->newLine();
        $this->command->info("=== RÉSULTAT ===");
        $this->command->info("{$inserted} unité(s) insérée(s)");
        $this->command->info("{$skipped} unité(s) déjà existante(s) ignorée(s)");
    }
}
