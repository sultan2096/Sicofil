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
        Schema::table('logs', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['user_id']);
            
            // Modifier la colonne pour permettre null
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Ajouter une nouvelle contrainte de clé étrangère qui permet null
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // Supprimer la contrainte actuelle
            $table->dropForeign(['user_id']);
            
            // Remettre la colonne comme non-nullable
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            // Remettre la contrainte originale
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
