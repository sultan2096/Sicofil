<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ZoneFiscaleSeeder::class,
            PopulationZoneSeeder::class,
            RoutesVoirieSeeder::class,
            BatimentsSeeder::class,
            UserSeeder::class,
            TaxeSeeder::class,
            UniteCommercialeSeeder::class,
            PaiementSeeder::class,
            DemoSicofilSeeder::class,
        ]);
    }
}
