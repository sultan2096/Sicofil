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
        Schema::table('taxes', function (Blueprint $table) {
            // Ajouter un champ pour indiquer si c'est une taxe foncière (montant par m²)
            $table->boolean('is_fonciere')->default(false)->after('montant');
            $table->decimal('montant_par_m2', 10, 2)->nullable()->after('is_fonciere')->comment('Montant par m² pour la taxe foncière');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn(['is_fonciere', 'montant_par_m2']);
        });
    }
};

