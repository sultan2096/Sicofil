<?php

namespace App\Console\Commands;

use App\Models\ZoneFiscale;
use Illuminate\Console\Command;

/**
 * Import zones fiscales depuis GeoJSON (après conversion Shapefile → GeoJSON via QGIS ou ogr2ogr)
 */
class ImportGeoJSONZones extends Command
{
    protected $signature = 'sicofil:import:geojson-zones 
        {path : Chemin du fichier .json ou .geojson} 
        {--commune= : ID de la commune} 
        {--truncate : Vider les zones avant import}
        {--dry-run : Simuler sans insérer}';

    protected $description = 'Importer des zones fiscales depuis un GeoJSON';

    public function handle(): int
    {
        $path = $this->argument('path');
        $communeId = $this->option('commune');
        $truncate = $this->option('truncate');
        $dryRun = $this->option('dry-run');

        if (!is_file($path)) {
            $this->error("Fichier introuvable: {$path}");
            return self::FAILURE;
        }

        if (!$communeId) {
            $commune = \App\Models\Commune::first();
            if (!$commune) {
                $this->error('Aucune commune trouvée. Utilisez --commune=ID');
                return self::FAILURE;
            }
            $communeId = $commune->id;
            $this->info("Commune: {$commune->nom} (ID: {$communeId})");
        }

        $json = json_decode(file_get_contents($path), true);
        if (!$json || !isset($json['features'])) {
            $this->error('GeoJSON invalide (FeatureCollection attendu)');
            return self::FAILURE;
        }

        if ($truncate && !$dryRun) {
            ZoneFiscale::where('commune_id', $communeId)->delete();
            $this->info('Zones existantes supprimées.');
        }

        $count = 0;
        foreach ($json['features'] as $f) {
            $geom = $f['geometry'] ?? null;
            if (!$geom || !in_array(strtolower($geom['type'] ?? ''), ['polygon', 'multipolygon'])) {
                continue;
            }
            $props = $f['properties'] ?? [];
            $nom = $props['nom'] ?? $props['name'] ?? $props['NOM'] ?? $props['libelle'] ?? 'Zone ' . ($count + 1);

            if (!$dryRun) {
                ZoneFiscale::create([
                    'commune_id' => $communeId,
                    'nom' => $nom,
                    'geometrie' => $geom,
                ]);
            }
            $count++;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Import terminé: {$count} zones.");
        return self::SUCCESS;
    }
}
