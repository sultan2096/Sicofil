<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ZoneFiscale;
use App\Models\PopulationZone;

class PopulationZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = (int) date('Y');
        $zones = ZoneFiscale::whereNotNull('geometrie')->get(['id']);

        foreach ($zones as $z) {
            // Eviter les doublons (unique zone_id, annee)
            $exists = PopulationZone::where('zone_id', $z->id)->where('annee', $year)->exists();
            if ($exists) { continue; }

            PopulationZone::create([
                'zone_id' => $z->id,
                // Population démo: 500 à 8000
                'population' => random_int(500, 8000),
                'annee' => $year,
            ]);
        }
    }
}
