<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZoneFiscale;
use Illuminate\Support\Str;

class CleanZoneDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zones:clean-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les zones dupliquées dans la base de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Nettoyage des zones dupliquées...');
        $this->newLine();

        // Trouver les zones avec des noms similaires qui pourraient être des doublons
        $allZones = ZoneFiscale::all();
        
        // Grouper par nom normalisé (sans espaces, en minuscules)
        $grouped = [];
        foreach ($allZones as $zone) {
            $normalized = strtolower(str_replace(' ', '', trim($zone->nom)));
            if (!isset($grouped[$normalized])) {
                $grouped[$normalized] = [];
            }
            $grouped[$normalized][] = $zone;
        }

        $deletedCount = 0;
        foreach ($grouped as $normalized => $zones) {
            if (count($zones) > 1) {
                $this->warn("Doublons détectés pour: '{$normalized}'");
                
                // Trier par ID (garder la plus ancienne)
                usort($zones, function($a, $b) {
                    return $a->id <=> $b->id;
                });
                
                $keep = array_shift($zones);
                $this->info("  ✓ Garder: ID {$keep->id} - '{$keep->nom}'");
                
                // Supprimer les autres
                foreach ($zones as $toDelete) {
                    $this->warn("  ✗ Supprimer: ID {$toDelete->id} - '{$toDelete->nom}'");
                    $toDelete->delete();
                    $deletedCount++;
                }
                $this->newLine();
            }
        }

        if ($deletedCount > 0) {
            $this->info("✓ {$deletedCount} zone(s) dupliquée(s) supprimée(s)");
        } else {
            $this->info("✓ Aucun doublon trouvé");
        }

        // Afficher les zones restantes
        $this->newLine();
        $this->info('Zones restantes:');
        $remainingZones = ZoneFiscale::all(['id', 'nom', 'description']);
        foreach ($remainingZones as $zone) {
            $desc = Str::limit($zone->description ?? 'Aucune description', 50);
            $this->line("  - ID: {$zone->id}, Nom: '{$zone->nom}'");
            $this->line("    Description: {$desc}");
        }

        return 0;
    }
}

