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
            $table->text('description')->nullable()->after('activite_produit');
            $table->string('base_calcul')->nullable()->after('description');
            $table->decimal('taux', 5, 2)->nullable()->after('base_calcul');
            $table->date('date_debut')->nullable()->after('taux');
            $table->date('date_fin')->nullable()->after('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn(['description', 'base_calcul', 'taux', 'date_debut', 'date_fin']);
        });
    }
};
