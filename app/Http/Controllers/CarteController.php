<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarteController extends Controller
{
    public function index()
    {
        return view('carte.index');
    }
    
    public function getZonesData()
    {
        try {
            $allZones = [];
            $tables = ['sandiaracentre', 'ndioukhthiorokh', 'loulibentegne'];
            
            foreach ($tables as $table) {
                try {
                    $zones = DB::select("
                        SELECT 
                            '{$table}' as zone_name,
                            ST_AsGeoJSON(geom) as geometry,
                            ST_Area(ST_Transform(geom, 32628)) / 1000000 as area_km2
                        FROM {$table}
                    ");
                    
                    foreach ($zones as $zone) {
                        $allZones[] = $zone;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            return response()->json([
                'status' => 'success',
                'zones_count' => count($allZones),
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
