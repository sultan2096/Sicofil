<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostGISController extends Controller
{
    public function testConnection()
    {
        try {
            $postgisVersion = DB::select("SELECT PostGIS_Version() as version")[0]->version;
            
            $allTables = DB::select(
                "SELECT table_name \n                FROM information_schema.tables \n                WHERE table_schema = 'public' \n                ORDER BY table_name"
            );
            
            $spatialTables = DB::select(
                "SELECT table_name, column_name, data_type \n                FROM information_schema.columns \n                WHERE table_schema = 'public' \n                AND data_type = 'USER-DEFINED'\n                AND udt_name = 'geometry'"
            );
            
            $spatialTest = DB::select(
                "SELECT ST_AsText(ST_GeomFromText('POINT(-16.79 14.43)', 4326)) as point_test"
            )[0]->point_test;
            
            return response()->json([
                'status' => 'success',
                'message' => 'Connexion PostGIS réussie',
                'postgis_version' => $postgisVersion,
                'all_tables' => $allTables,
                'spatial_tables' => $spatialTables,
                'custom_zone_tables' => ['sandiaracentre', 'ndioukhthiorokh', 'loulibentegne'],
                'spatial_test' => $spatialTest,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()
            ], 500);
        }
    }
    
    public function getZonesFiscales()
    {
        try {
            // Filtrage strict par commune de l'utilisateur connecté (multi-communes)
            $user = auth()->user();
            $query = \App\Models\ZoneFiscale::query();
            if ($user && strtolower((string) $user->role) !== 'admin_sicofil') {
                $query->where('commune_id', $user->commune_id);
            } elseif ($user && session('commune_scope_id')) {
                $query->where('commune_id', session('commune_scope_id'));
            }
            $zones = $query->get();
            
            $allZones = [];
            
            foreach ($zones as $zone) {
                // Décoder manuellement le JSON si c'est une chaîne
                $geometry = is_string($zone->geometrie) ? json_decode($zone->geometrie, true) : $zone->geometrie;
                
                $allZones[] = [
                    'zone_name' => strtolower(str_replace(' ', '', $zone->nom)),
                    'geometry' => $geometry,
                    'area_km2' => $this->calculateArea($geometry),
                    'display_name' => $zone->nom,
                    'description' => $zone->description
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'zones_count' => count($allZones),
                'zones' => $allZones,
                'source' => 'laravel_database'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculer l'aire approximative d'un polygone
     */
    private function calculateArea($geometry)
    {
        if (!isset($geometry['coordinates'][0])) {
            return 0;
        }
        
        $coordinates = $geometry['coordinates'][0];
        $area = 0;
        $n = count($coordinates);
        
        for ($i = 0; $i < $n - 1; $i++) {
            $area += ($coordinates[$i][0] * $coordinates[$i + 1][1]) - ($coordinates[$i + 1][0] * $coordinates[$i][1]);
        }
        
        return abs($area) / 2 * 111000 * 111000 / 1000000; // Conversion approximative en km²
    }
    
    public function findZoneByPoint(Request $request)
    {
        try {
            $latitude = $request->input('lat', 14.43);
            $longitude = $request->input('lng', -16.79);
            
            $allZones = [];
            $tables = ['sandiaracentre', 'ndioukhthiorokh', 'loulibentegne'];
            
            foreach ($tables as $table) {
                try {
                    $zones = DB::select(
                        "SELECT \n                            '{$table}' as zone_name,\n                            ST_AsGeoJSON(geom) as geometry,\n                            ST_Area(ST_Transform(geom, 32628)) / 1000000 as area_km2\n                        FROM {$table}\n                        WHERE ST_Contains(geom, ST_GeomFromText('POINT(? ?)', 4326))",
                        [$longitude, $latitude]
                    );
                    
                    foreach ($zones as $zone) {
                        $allZones[] = $zone;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            return response()->json([
                'status' => 'success',
                'point' => ['lat' => $latitude, 'lng' => $longitude],
                'zones_found' => count($allZones),
                'zones' => $allZones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
