<?php

namespace App\Console\Commands;

use App\Models\Commune;
use App\Models\UniteCommerciale;
use App\Models\ZoneFiscale;
use App\Models\Taxe;
use Illuminate\Console\Command;

class DiagnosticSandiaraData extends Command
{
    protected $signature = 'sandiara:diagnostic';
    protected $description = 'Vérifie les données de la commune Sandiara (zones, unités, taxes) pour la carte.';

    public function handle(): int
    {
        $commune = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first();
        if (!$commune) {
            $this->error('Commune Sandiara introuvable dans la table communes.');
            return 1;
        }

        $this->info("Commune Sandiara : id={$commune->id}, nom={$commune->nom}, code={$commune->code}");

        $zones = ZoneFiscale::withoutGlobalScopes()->where('commune_id', $commune->id)->count();
        $unitesActif = UniteCommerciale::withoutGlobalScopes()->where('commune_id', $commune->id)->where('actif', true)->count();
        $unitesTotal = UniteCommerciale::withoutGlobalScopes()->where('commune_id', $commune->id)->count();
        $taxes = Taxe::withoutGlobalScopes()->where('commune_id', $commune->id)->count();

        $this->table(
            ['Donnée', 'Nombre'],
            [
                ['Zones fiscales', $zones],
                ['Unités commerciales (actives)', $unitesActif],
                ['Unités commerciales (total)', $unitesTotal],
                ['Taxes', $taxes],
            ]
        );

        if ($zones === 0) {
            $this->warn('Aucune zone fiscale pour Sandiara. Exécutez : php artisan db:seed --class=ZoneFiscaleSeeder');
        }
        if ($unitesActif === 0 && $unitesTotal === 0) {
            $this->warn('Aucune unité commerciale pour Sandiara. Exécutez : php artisan db:seed --class=UniteCommercialeSeeder');
        }
        if ($taxes === 0) {
            $this->warn('Aucune taxe pour Sandiara. Exécutez : php artisan db:seed --class=TaxeSeeder');
        }
        if ($zones > 0 && $unitesActif > 0) {
            $this->info('Les données Sandiara sont présentes. La carte devrait afficher les points.');
        }

        return 0;
    }
}
