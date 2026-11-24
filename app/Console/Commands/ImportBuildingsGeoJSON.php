<?php

namespace App\Console\Commands;

use App\Models\Batiment;
use Illuminate\Console\Command;

class ImportBuildingsGeoJSON extends Command
{
    protected $signature = 'geomarketing:import:buildings {path : Chemin du fichier GeoJSON} {--truncate : Vider la table avant import}';

    protected $description = 'Importer un GeoJSON de bâti dans la table batiments';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("Fichier introuvable: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            Batiment::truncate();
            $this->info('Table batiments vidée.');
        }

        $raw = file_get_contents($path);
        $json = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('GeoJSON invalide: ' . json_last_error_msg());
            return self::FAILURE;
        }

        $features = [];
        if (isset($json['type']) && strtolower($json['type']) === 'featurecollection' && isset($json['features'])) {
            $features = $json['features'];
        } elseif (isset($json[0])) {
            $features = $json;
        } else {
            $this->error('Format inattendu: FeatureCollection ou tableau de features requis.');
            return self::FAILURE;
        }

        $count = 0;
        foreach ($features as $f) {
            if (!isset($f['geometry'])) continue;
            $geom = $f['geometry'];
            $type = strtolower($geom['type'] ?? '');
            if (!in_array($type, ['polygon','multipolygon'])) continue;

            $props = $f['properties'] ?? [];
            Batiment::create([
                'nom' => $props['name'] ?? $props['nom'] ?? null,
                'usage' => $props['usage'] ?? null,
                'surface_m2' => isset($props['surface']) ? (float) $props['surface'] : null,
                'geometrie' => json_encode($geom),
            ]);
            $count++;
        }

        $this->info("Import bâti terminé: {$count} entités.");
        return self::SUCCESS;
    }
}
