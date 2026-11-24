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
        Schema::create('zones_fiscales', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->json('geometrie')->nullable(); // Pour stocker les coordonnées de la zone
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones_fiscales');
    }
}; 