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
            // Supprimer les anciennes colonnes photos si elles existent
            if (Schema::hasColumn('unites_commerciales', 'photo_activite')) {
                $table->dropColumn('photo_activite');
            }
            if (Schema::hasColumn('unites_commerciales', 'photo_enseigne_petite')) {
                $table->dropColumn('photo_enseigne_petite');
            }
            if (Schema::hasColumn('unites_commerciales', 'photo_enseigne_grande')) {
                $table->dropColumn('photo_enseigne_grande');
            }
            if (Schema::hasColumn('unites_commerciales', 'photo_point_interet')) {
                $table->dropColumn('photo_point_interet');
            }
            
            // Ajouter les nouvelles colonnes photos pour panneaux publicitaires
            $table->string('photo_panneau_petit')->nullable()->after('taille_enseigne_grande');
            $table->string('photo_panneau_grand')->nullable()->after('photo_panneau_petit');
            $table->string('photo_tableau')->nullable()->after('photo_panneau_grand');
            $table->string('photo_enseigne')->nullable()->after('photo_tableau');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            // Supprimer les nouvelles colonnes
            $table->dropColumn([
                'photo_panneau_petit',
                'photo_panneau_grand', 
                'photo_tableau',
                'photo_enseigne'
            ]);
            
            // Restaurer les anciennes colonnes (optionnel)
            $table->string('photo_activite')->nullable();
            $table->string('photo_enseigne_petite')->nullable();
            $table->string('photo_enseigne_grande')->nullable();
            $table->string('photo_point_interet')->nullable();
        });
    }
}; 