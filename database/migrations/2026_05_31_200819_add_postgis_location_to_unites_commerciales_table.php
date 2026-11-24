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
        // Activer PostGIS si ce n'est pas déjà fait
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        // Ajouter la colonne location de type geometry(Point, 4326)
        DB::statement('ALTER TABLE unites_commerciales ADD COLUMN IF NOT EXISTS location geometry(Point, 4326)');

        // Créer un index spatial
        DB::statement('CREATE INDEX IF NOT EXISTS idx_unites_location ON unites_commerciales USING GIST(location)');

        // Migrer les données depuis latitude/longitude vers location
        DB::statement("UPDATE unites_commerciales SET location = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326) WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
