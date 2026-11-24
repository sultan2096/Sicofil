<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZoneFiscale;
use Illuminate\Support\Facades\DB;

class SyncZoneGeometriesFromPostGIS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zones:sync-geometries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les géométries des zones depuis PostGIS vers la table zones_fiscales';

    /**
     * Mapping des noms de zones vers les tables PostGIS
     */
    protected $zoneToTableMap = [
        'Loulibentegne' => 'public.polygone_loulibentegne',
        'Sandiara Centre' => 'public.polygone_sandiaracentre',
        'Ndioukh thiorokh' => 'public.polygone_ndioukhthiorokh',
        'Ndioukhthiorokh' => 'public.polygone_ndioukhthiorokh',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Synchronisation des géométries depuis PostGIS...');
        $this->newLine();

        $zones = ZoneFiscale::all();
        $syncedCount = 0;
        $errorCount = 0;

        foreach ($zones as $zone) {
            $this->info("Traitement de la zone: '{$zone->nom}' (ID: {$zone->id})");

            // Trouver la table PostGIS correspondante
            $tableName = null;
            foreach ($this->zoneToTableMap as $zoneName => $table) {
                if (stripos($zone->nom, $zoneName) !== false || stripos($zoneName, $zone->nom) !== false) {
                    $tableName = $table;
                    break;
                }
            }

            // Essayer aussi avec le nom normalisé (sans espaces, en minuscules)
            if (!$tableName) {
                $normalizedZoneName = strtolower(str_replace(' ', '', trim($zone->nom)));
                $normalizedTableMap = [
                    'loulibentegne' => 'public.polygone_loulibentegne',
                    'sandiaracentre' => 'public.polygone_sandiaracentre',
                    'ndioukhthiorokh' => 'public.polygone_ndioukhthiorokh',
                ];
                
                if (isset($normalizedTableMap[$normalizedZoneName])) {
                    $tableName = $normalizedTableMap[$normalizedZoneName];
                }
            }

            if (!$tableName) {
                $this->warn("  ⚠ Table PostGIS non trouvée pour '{$zone->nom}'");
                $errorCount++;
                continue;
            }

            $this->info("  Table PostGIS: {$tableName}");

            try {
                // Récupérer la géométrie depuis PostGIS
                // Récupérer la première géométrie (les zones ont généralement une seule géométrie)
                $sql = "
                    SELECT ST_AsGeoJSON(geom)::json as geojson
                    FROM {$tableName}
                    WHERE geom IS NOT NULL
                    LIMIT 1
                ";

                $result = DB::select($sql);

                if (empty($result) || !isset($result[0]->geojson)) {
                    $this->warn("  ⚠ Aucune géométrie trouvée dans {$tableName}");
                    $errorCount++;
                    continue;
                }

                $geojson = $result[0]->geojson;
                
                // Décoder si c'est une chaîne
                if (is_string($geojson)) {
                    $geojson = json_decode($geojson, true);
                }

                if (!$geojson || !isset($geojson['type'])) {
                    $this->warn("  ⚠ Géométrie invalide dans {$tableName}");
                    $errorCount++;
                    continue;
                }

                // Vérifier que c'est une géométrie valide (Polygon ou MultiPolygon)
                if (!in_array($geojson['type'], ['Polygon', 'MultiPolygon', 'Point', 'LineString', 'MultiLineString'])) {
                    $this->warn("  ⚠ Type de géométrie non supporté: {$geojson['type']}");
                    $errorCount++;
                    continue;
                }

                // Stocker la géométrie
                $zone->update(['geometrie' => $geojson]);
                $this->info("  ✓ Géométrie synchronisée (type: {$geojson['type']})");
                $syncedCount++;

            } catch (\Exception $e) {
                $this->error("  ✗ Erreur lors de la synchronisation: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Synchronisation terminée:");
        $this->info("  ✓ {$syncedCount} zone(s) synchronisée(s)");
        if ($errorCount > 0) {
            $this->warn("  ⚠ {$errorCount} erreur(s)");
        }

        return 0;
    }
}

