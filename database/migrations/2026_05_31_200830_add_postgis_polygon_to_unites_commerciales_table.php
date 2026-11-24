<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter la colonne geometrie de type geometry(Polygon, 4326)
        DB::statement('ALTER TABLE unites_commerciales ADD COLUMN IF NOT EXISTS geometrie geometry(Polygon, 4326)');

        // Créer un index spatial
        DB::statement('CREATE INDEX IF NOT EXISTS idx_unites_geometrie ON unites_commerciales USING GIST(geometrie)');

        // Migrer les données depuis geometrie_polygon (JSON) vers geometrie (PostGIS)
        // On met à jour ligne par ligne car ST_GeomFromGeoJSON nécessite un objet GeoJSON valide
        $unites = DB::table('unites_commerciales')
            ->whereNotNull('geometrie_polygon')
            ->select('id', 'geometrie_polygon')
            ->get();

        foreach ($unites as $unite) {
            $geojson = json_decode($unite->geometrie_polygon, true);
            if (is_array($geojson) && isset($geojson['coordinates'])) {
                $geojsonStr = json_encode($geojson);
                if ($geojsonStr) {
                    DB::statement(
                        "UPDATE unites_commerciales SET geometrie = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?",
                        [$geojsonStr, $unite->id]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropColumn('geometrie');
        });
    }
};
