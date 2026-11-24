<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZoneFiscale;

class UpdateZoneVillages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zones:update-villages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour les descriptions des zones avec les nouveaux villages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mise à jour des descriptions des zones...');
        $this->newLine();

        // Afficher les zones existantes
        $existingZones = ZoneFiscale::all(['id', 'nom']);
        if ($existingZones->count() > 0) {
            $this->info('Zones existantes dans la base de données:');
            foreach ($existingZones as $zone) {
                $this->line("  - ID: {$zone->id}, Nom: '{$zone->nom}'");
            }
            $this->newLine();
        }

        $zonesData = [
            [
                'nom' => 'Loulibentegne',
                'searchNames' => ['Loulibentegne', 'loulibentegne', 'Louly Benteigné'],
                'description' => 'Zone fiscale de Loulibentegne - Zone Nord de Sandiara (Vert) - Villages: Kouthie Wolof, Kibik, Ngomène, Louly Mbafay, Louly Benteigné, Louly Sindiane, Mbourokh, Nianiar, Louly Ngogom',
            ],
            [
                'nom' => 'Sandiara Centre',
                'searchNames' => ['Sandiara Centre', 'sandiaracentre', 'Sandiara Centre'],
                'description' => 'Zone fiscale de Sandiara Centre - Zone Centrale de Sandiara (Rose) - Villages: Faylar 1, Faylar 2, Sandiara Sérère, Sandiara Wolof, Garage Diakher, Soukhem, Godaguène, Ndiouk Fissel',
            ],
            [
                'nom' => 'Ndioukh thiorokh',
                'searchNames' => ['Ndioukh thiorokh', 'Ndioukhthiorokh', 'Ndioukh Thiorokh', 'ndioukh thiorokh', 'ndioukhthiorokh'],
                'description' => 'Zone fiscale de Ndioukh Thiorokh - Zone Sud de Sandiara (Bleu) - Villages: Ndioukh Thiorokh, Samane, Louly Ndia, Keur Diba, Soussane, Ndollor',
                'geometrie' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.860000, 14.400000],
                        [-16.820000, 14.390000],
                        [-16.780000, 14.400000],
                        [-16.760000, 14.420000],
                        [-16.750000, 14.480000],
                        [-16.760000, 14.380000],
                        [-16.780000, 14.360000],
                        [-16.820000, 14.350000],
                        [-16.860000, 14.360000],
                        [-16.900000, 14.370000],
                        [-16.920000, 14.380000],
                        [-16.940000, 14.390000],
                        [-16.960000, 14.400000],
                        [-16.880000, 14.410000],
                        [-16.860000, 14.400000]
                    ]]
                ],
            ],
        ];

        // Nettoyer les doublons potentiels avant de traiter
        $this->info('Vérification des doublons...');
        $allZones = ZoneFiscale::all();
        
        // Grouper par nom normalisé (sans espaces, en minuscules)
        $grouped = [];
        foreach ($allZones as $zoneItem) {
            $normalized = strtolower(str_replace(' ', '', trim($zoneItem->nom)));
            if (!isset($grouped[$normalized])) {
                $grouped[$normalized] = [];
            }
            $grouped[$normalized][] = $zoneItem;
        }

        $deletedCount = 0;
        foreach ($grouped as $normalized => $zonesGroup) {
            if (count($zonesGroup) > 1) {
                $this->warn("  Doublons détectés pour: '{$normalized}'");
                
                // Trier par ID (garder la plus ancienne)
                usort($zonesGroup, function($a, $b) {
                    return $a->id <=> $b->id;
                });
                
                $keep = array_shift($zonesGroup);
                $this->info("    ✓ Garder: ID {$keep->id} - '{$keep->nom}'");
                
                // Supprimer les autres
                foreach ($zonesGroup as $toDelete) {
                    $this->warn("    ✗ Supprimer: ID {$toDelete->id} - '{$toDelete->nom}'");
                    $toDelete->delete();
                    $deletedCount++;
                }
            }
        }
        
        if ($deletedCount > 0) {
            $this->info("  ✓ {$deletedCount} zone(s) dupliquée(s) supprimée(s)");
        } else {
            $this->info("  ✓ Aucun doublon trouvé");
        }
        $this->newLine();

        foreach ($zonesData as $zoneData) {
            $zone = null;
            $matchedName = null;
            
            // Chercher la zone avec le nom exact d'abord
            $zone = ZoneFiscale::where('nom', $zoneData['nom'])->first();
            if ($zone) {
                $matchedName = $zoneData['nom'];
            } else {
                // Chercher avec toutes les variantes
                foreach ($zoneData['searchNames'] as $searchName) {
                    $zone = ZoneFiscale::whereRaw('LOWER(TRIM(nom)) = ?', [strtolower(trim($searchName))])->first();
                    if ($zone) {
                        $matchedName = $zone->nom;
                        $this->info("  Zone trouvée avec variante: '{$matchedName}' (recherché: '{$zoneData['nom']}')");
                        break;
                    }
                }
            }
            
            if ($zone) {
                // Mettre à jour la description
                $zone->update(['description' => $zoneData['description']]);
                
                // Mettre à jour la géométrie si fournie et si elle n'existe pas
                if (isset($zoneData['geometrie']) && empty($zone->geometrie)) {
                    $zone->update(['geometrie' => $zoneData['geometrie']]);
                    $this->info("  Géométrie ajoutée");
                }
                
                $this->info("✓ Zone '{$zone->nom}' (ID: {$zone->id}) mise à jour");
            } else {
                // Créer la zone si elle n'existe vraiment pas
                $this->warn("⚠ Zone '{$zoneData['nom']}' non trouvée, création...");
                try {
                    ZoneFiscale::create([
                        'nom' => $zoneData['nom'],
                        'description' => $zoneData['description'],
                        'geometrie' => $zoneData['geometrie'] ?? null,
                    ]);
                    $this->info("✓ Zone '{$zoneData['nom']}' créée avec succès");
                } catch (\Exception $e) {
                    $this->error("✗ Erreur lors de la création de la zone '{$zoneData['nom']}': " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('Mise à jour terminée !');
        return 0;
    }
}

