<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NatureActivite;
use Illuminate\Support\Facades\DB;

class UpdateLocationToFoncier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:location-to-foncier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour les natures d\'activité "Location" en "Foncier"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mise à jour de "Location" vers "Foncier"...');
        
        // Mettre à jour la table nature_activites
        $updated = DB::table('nature_activites')
            ->where('nom', 'Location')
            ->update(['nom' => 'Foncier', 'description' => 'Terrains et propriétés foncières (parcelles, bâtiments)']);
        
        if ($updated > 0) {
            $this->info("✓ {$updated} nature(s) d'activité mise(s) à jour");
        } else {
            $this->warn('⚠ Aucune nature "Location" trouvée. Vérifiez si elle existe déjà ou si elle a déjà été renommée.');
        }
        
        // Mettre à jour les unités commerciales qui utilisent "Location"
        $updatedUnites = DB::table('unites_commerciales')
            ->where('nature', 'Location')
            ->update(['nature' => 'Foncier']);
        
        if ($updatedUnites > 0) {
            $this->info("✓ {$updatedUnites} unité(s) commerciale(s) mise(s) à jour");
        }
        
        $this->info('Mise à jour terminée !');
        return 0;
    }
}

