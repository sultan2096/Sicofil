<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones_fiscales', function (Blueprint $table) {
            // Supprimer l'unique global sur nom (permet doublons entre communes)
            $table->dropUnique(['nom']);
            // Ajouter un unique par commune (même nom possible dans 2 communes différentes)
            $table->unique(['commune_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::table('zones_fiscales', function (Blueprint $table) {
            $table->dropUnique(['commune_id', 'nom']);
            $table->unique(['nom']);
        });
    }
};
