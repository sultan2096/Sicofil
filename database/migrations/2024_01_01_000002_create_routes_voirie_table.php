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
        Schema::create('routes_voirie', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('importance', ['primary', 'secondary', 'tertiary', 'local'])->default('local');
            $table->string('type')->nullable();
            $table->decimal('longueur_km', 8, 2)->nullable();
            $table->decimal('largeur_m', 5, 2)->nullable();
            $table->enum('etat', ['excellent', 'bon', 'moyen', 'mauvais'])->default('bon');
            $table->json('geometrie')->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones_fiscales')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['importance', 'zone_id']);
            $table->index('etat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes_voirie');
    }
};



