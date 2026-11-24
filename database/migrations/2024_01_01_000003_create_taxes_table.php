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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('periodicite', ['jour', 'mois', 'an', 'non_periodique']);
            $table->decimal('montant', 10, 2);
            $table->enum('nature', ['produits_domaniaux', 'taxes_municipales', 'produits_exploitation']);
            $table->string('activite_produit');
            $table->string('unite')->nullable(); // Ex: tête/jour, pompe/mois
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
}; 