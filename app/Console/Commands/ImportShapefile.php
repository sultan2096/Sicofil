<?php

namespace App\Console\Commands;

use App\Models\ZoneFiscale;
use App\Models\Commune;
use Illuminate\Console\Command;

/**
 * Import de Shapefiles (zones fiscales) - SRID 4326
 * Utilise php-shapefile (pur PHP) ou ogr2ogr (GDAL) si disponible
 */
class ImportShapefile extends Command
{
    protected $signature = 'sicofil:import:shapefile 
        {path : Chemin du fichier .shp (ou dossier contenant .shp)} 
        {--commune= : ID de la commune} 
        {--truncate : Vider les zones avant import}
        {--dry-run : Simuler sans insérer}';

    protected $description = 'Importer des zones fiscales depuis un Shapefile (SRID 4326)';

    public function handle(): int
    {
        $path = $this->argument('path');
        $communeId = $this->option('commune');
        $truncate = $this->option('truncate');
        $dryRun = $this->option('dry-run');

        if (!$communeId) {
            $commune = Commune::first();
            if (!$commune) {
                $this->error('Aucune commune trouvée. Créez-en une ou utilisez --commune=ID');
                return self::FAILURE;
            }
            $communeId = $commune->id;
            $this->info("Utilisation de la commune: {$commune->nom} (ID: {$communeId})");
        }

        $shpPath = $this->resolveShapefilePath($path);
        if (!$shpPath) {
            $this->error("Shapefile introuvable: {$path}");
            $this->info("Placez les fichiers .shp, .shx, .dbf, .prj dans le même dossier.");
            return self::FAILURE;
        }

        // Méthode 1: ogr2ogr (GDAL) si disponible
        $geoJsonPath = $this->convertToGeoJSON($shpPath);
        if ($geoJsonPath) {
            return $this->importFromGeoJSON($geoJsonPath, $communeId, $truncate, $dryRun);
        }

        // Méthode 2: php-shapefile (pur PHP)
        if (class_exists(\Shapefile\ShapefileReader::class)) {
            return $this->importWithPhpShapefile($shpPath, $communeId, $truncate, $dryRun);
        }

        // Méthode 3: GeoJSON manuel
        $this->warn('ogr2ogr (GDAL) non trouvé. Installez: composer require gasparesganga/php-shapefile');
        $this->line('Ou: ogr2ogr -f GeoJSON zones.json "' . basename($shpPath) . '"');
        $this->line('Puis: php artisan sicofil:import:geojson-zones zones.json --commune=' . $communeId);
        return self::FAILURE;
    }

    protected function importWithPhpShapefile(string $shpPath, int $communeId, bool $truncate, bool $dryRun): int
    {
        try {
            $reader = new \Shapefile\ShapefileReader($shpPath);
        } catch (\Throwable $e) {
            $this->error('Erreur lecture shapefile: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($truncate && !$dryRun) {
            ZoneFiscale::where('commune_id', $communeId)->delete();
            $this->info('Zones existantes supprimées.');
        }

        $count = 0;
        $baseName = pathinfo($shpPath, PATHINFO_FILENAME);

        while ($record = $reader->fetchRecord()) {
            $geoJson = $record->getGeoJSON();
            $geom = json_decode($geoJson, true);
            if (!$geom || !in_array(strtolower($geom['type'] ?? ''), ['polygon', 'multipolygon'])) {
                continue;
            }
            $props = $record->getDataArray();
            $nom = $props['nom'] ?? $props['name'] ?? $props['NOM'] ?? $props['libelle'] ?? $baseName . ' ' . ($count + 1);

            if (!$dryRun) {
                ZoneFiscale::create([
                    'commune_id' => $communeId,
                    'nom' => (string) $nom,
                    'geometrie' => $geom,
                ]);
            }
            $count++;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Import terminé: {$count} zones.");
        return self::SUCCESS;
    }

    protected function resolveShapefilePath(string $path): ?string
    {
        $base = base_path();
        $candidates = [
            $path,
            $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path),
        ];
        foreach ($candidates as $p) {
            if (is_file($p) && str_ends_with(strtolower($p), '.shp')) {
                return realpath($p) ?: $p;
            }
        }
        foreach ($candidates as $p) {
            if (is_dir($p)) {
                $files = glob($p . DIRECTORY_SEPARATOR . '*.shp');
                return $files[0] ?? null;
            }
        }
        return null;
    }

    protected function convertToGeoJSON(string $shpPath): ?string
    {
        $outPath = storage_path('app/temp_import_' . uniqid() . '.json');
        $cmd = sprintf('ogr2ogr -f GeoJSON -t_srs EPSG:4326 %s %s 2>/dev/null', escapeshellarg($outPath), escapeshellarg($shpPath));
        exec($cmd, $output, $code);
        if ($code === 0 && file_exists($outPath)) {
            return $outPath;
        }
        return null;
    }

    protected function importFromGeoJSON(string $path, int $communeId, bool $truncate, bool $dryRun): int
    {
        $json = json_decode(file_get_contents($path), true);
        @unlink($path); // Nettoyage

        if (!$json || !isset($json['features'])) {
            $this->error('GeoJSON invalide');
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
            $nom = $props['nom'] ?? $props['name'] ?? $props['NOM'] ?? 'Zone ' . ($count + 1);

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
