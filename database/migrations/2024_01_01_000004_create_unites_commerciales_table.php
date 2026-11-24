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
        Schema::create('unites_commerciales', function (Blueprint $table) {
            $table->id();
            $table->string('nom_proprietaire');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('ninea')->nullable(); // Numéro d'Identification Nationale des Entreprises et Associations
            $table->enum('categorie', ['activite_commerciale', 'point_interet']);
            $table->string('nature'); // Boutique, Atelier, Foirail, etc.
            $table->string('type_commerce'); // Alimentation générale, Mécanique, etc.
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('adresse')->nullable();
            $table->foreignId('zone_id')->constrained('zones_fiscales')->onDelete('cascade');
            $table->foreignId('taxe_id')->nullable()->constrained('taxes')->onDelete('set null');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unites_commerciales');
    }
}; 