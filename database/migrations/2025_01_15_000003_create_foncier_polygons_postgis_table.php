<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crée une table PostGIS pour stocker les polygones des terrains fonciers
     */
    public function up(): void
    {
        // Vérifier que PostGIS est disponible
        DB::statement("CREATE EXTENSION IF NOT EXISTS postgis;");
        
        // Créer la table dans le schéma public pour les polygones fonciers
        DB::statement("
            CREATE TABLE IF NOT EXISTS public.foncier_polygons (
                id SERIAL PRIMARY KEY,
                unite_commerciale_id INTEGER NOT NULL,
                geom GEOMETRY(POLYGON, 4326) NOT NULL,
                superficie_m2 DECIMAL(12, 2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_foncier_polygons_unite FOREIGN KEY (unite_commerciale_id) 
                    REFERENCES unites_commerciales(id) ON DELETE CASCADE
            );
        ");
        
        // Créer un index spatial pour améliorer les performances
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_foncier_polygons_geom 
            ON public.foncier_polygons USING GIST (geom);
        ");
        
        // Créer un index sur l'ID de l'unité commerciale
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_foncier_polygons_unite_id 
            ON public.foncier_polygons(unite_commerciale_id);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS public.foncier_polygons CASCADE;");
    }
};

