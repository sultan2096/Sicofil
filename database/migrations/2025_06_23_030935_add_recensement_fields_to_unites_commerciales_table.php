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
            // Informations propriétaire étendues
            $table->string('prenom_proprietaire')->nullable()->after('nom_proprietaire');
            $table->enum('sexe_proprietaire', ['masculin', 'feminin'])->nullable()->after('prenom_proprietaire');
            $table->string('nin_proprietaire')->nullable()->after('sexe_proprietaire');
            
            // Localisation étendue
            $table->decimal('altitude', 10, 6)->nullable()->after('longitude');
            $table->decimal('precision', 8, 2)->nullable()->after('altitude');
            
            // Types de commerce détaillés
            $table->string('sous_type_commerce')->nullable()->after('type_commerce');
            
            // Champs spécifiques aux points d'intérêt publicitaires
            $table->boolean('enseigne_petite')->default(false)->after('sous_type_commerce');
            $table->boolean('enseigne_grande')->default(false)->after('enseigne_petite');
            $table->enum('taille_enseigne_petite', ['moins_1m2', 'entre_1_3m2', 'entre_3_5m2', 'plus_5m2'])->nullable()->after('enseigne_grande');
            $table->enum('taille_enseigne_grande', ['moins_1m2', 'entre_1_3m2', 'entre_3_5m2', 'plus_5m2'])->nullable()->after('taille_enseigne_petite');
            
            // Collecteur qui a recensé l'unité
            $table->foreignId('collecteur_id')->nullable()->constrained('users')->onDelete('set null')->after('zone_id');
            
            // Champs pour les photos (chemins vers les fichiers)
            $table->string('photo_activite')->nullable()->after('taille_enseigne_grande');
            $table->string('photo_enseigne_petite')->nullable()->after('photo_activite');
            $table->string('photo_enseigne_grande')->nullable()->after('photo_enseigne_petite');
            $table->string('photo_point_interet')->nullable()->after('photo_enseigne_grande');
            
            // Date de recensement
            $table->timestamp('date_recensement')->nullable()->after('photo_point_interet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropForeign(['collecteur_id']);
            $table->dropColumn([
                'prenom_proprietaire',
                'sexe_proprietaire', 
                'nin_proprietaire',
                'altitude',
                'precision',
                'sous_type_commerce',
                'enseigne_petite',
                'enseigne_grande',
                'taille_enseigne_petite',
                'taille_enseigne_grande',
                'collecteur_id',
                'photo_activite',
                'photo_enseigne_petite',
                'photo_enseigne_grande',
                'photo_point_interet',
                'date_recensement'
            ]);
        });
    }
};
