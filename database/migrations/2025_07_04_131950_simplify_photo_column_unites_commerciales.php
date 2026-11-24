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
        Schema::table('unites_commerciales', function (Blueprint $table) {
            // Supprimer les anciennes colonnes photos multiples
            $table->dropColumn([
                'photo_panneau_petit',
                'photo_panneau_grand', 
                'photo_tableau',
                'photo_enseigne'
            ]);
            
            // Ajouter une seule colonne photo
            $table->string('photo')->nullable()->after('taille_enseigne_grande');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            // Supprimer la colonne photo unique
            $table->dropColumn('photo');
            
            // Restaurer les anciennes colonnes photos multiples
            $table->string('photo_panneau_petit')->nullable();
            $table->string('photo_panneau_grand')->nullable();
            $table->string('photo_tableau')->nullable();
            $table->string('photo_enseigne')->nullable();
        });
    }
};
