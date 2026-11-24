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
        Schema::create('batiments', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('usage', ['commercial', 'residential', 'industrial', 'public', 'mixed'])->default('residential');
            $table->string('type')->nullable();
            $table->decimal('surface_m2', 10, 2)->nullable();
            $table->decimal('hauteur_m', 5, 2)->nullable();
            $table->enum('etat', ['excellent', 'bon', 'moyen', 'mauvais'])->default('bon');
            $table->integer('annee_construction')->nullable();
            $table->json('geometrie')->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones_fiscales')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['usage', 'zone_id']);
            $table->index('etat');
            $table->index('surface_m2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batiments');
    }
};



