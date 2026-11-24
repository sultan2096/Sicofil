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
        Schema::create('population_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones_fiscales')->onDelete('cascade');
            $table->integer('population');
            $table->integer('annee')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['zone_id', 'annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('population_zones');
    }
};



