<?php

namespace App\Console\Commands;

use App\Models\RouteVoirie;
use Illuminate\Console\Command;

class ImportRoutesGeoJSON extends Command
{
    protected $signature = 'geomarketing:import:roads {path : Chemin du fichier GeoJSON} {--truncate : Vider la table avant import}';

    protected $description = 'Importer un GeoJSON de routes/voirie dans la table routes_voirie';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("Fichier introuvable: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            RouteVoirie::truncate();
            $this->info('Table routes_voirie vidée.');
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
            $features = $json; // tableau de features
        } else {
            $this->error('Format inattendu: FeatureCollection ou tableau de features requis.');
            return self::FAILURE;
        }

        $count = 0;
        foreach ($features as $f) {
            if (!isset($f['geometry'])) continue;
            $geom = $f['geometry'];
            $type = strtolower($geom['type'] ?? '');
            if (!in_array($type, ['linestring','multilinestring'])) continue; // ignorer autres types

            $props = $f['properties'] ?? [];
            RouteVoirie::create([
                'nom' => $props['name'] ?? $props['nom'] ?? null,
                'importance' => $props['importance'] ?? $props['highway'] ?? null,
                'geometrie' => json_encode($geom),
            ]);
            $count++;
        }

        $this->info("Import routes terminé: {$count} entités.");
        return self::SUCCESS;
    }
}
