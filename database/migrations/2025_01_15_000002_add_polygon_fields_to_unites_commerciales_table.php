<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            // Ajouter le champ superficie (en m²) pour les terrains
            $table->decimal('superficie', 12, 2)->nullable()->after('adresse')->comment('Superficie en m²');
            
            // Ajouter le champ pour stocker la géométrie polygonale (JSON)
            $table->json('geometrie_polygon')->nullable()->after('superficie')->comment('Géométrie polygonale au format GeoJSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropColumn(['superficie', 'geometrie_polygon']);
        });
    }
};

