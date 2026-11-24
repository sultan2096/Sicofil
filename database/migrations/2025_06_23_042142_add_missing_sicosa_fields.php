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
            // Champs manquants pour SICOSA (basé sur les colonnes existantes vues)
            $table->string('nom_complet')->nullable();
            $table->string('nom_commercial')->nullable();
            
            // Point d'intérêt commercial
            $table->enum('nature_point_interet', ['Location', 'Publicité'])->nullable();
            $table->string('type_point_interet')->nullable();
            
            // Enseignes et panneaux (renommage pour correspondre au questionnaire)
            $table->boolean('enseigne_petit_panneau')->default(false);
            $table->boolean('enseigne_et_petit_panneau')->default(false);
            $table->enum('taille_enseigne', ['moins de 1 m2', 'entre 1 m2 et 3 m2', 'entre 3 m2 et 5 m2', 'plus de 5 m2'])->nullable();
            $table->enum('taille_grands_panneaux', ['moins de 1 m2', 'entre 1 m2 et 3 m2', 'entre 3 m2 et 5m2', 'plus de 5 m2'])->nullable();
            
            // Photos supplémentaires
            $table->string('photo_panneau')->nullable();
            
            // Informations propriétaire point d'intérêt
            $table->string('numero_identification_proprietaire_poi')->nullable();
            $table->string('prenom_proprietaire_poi')->nullable();
            $table->string('nom_proprietaire_poi')->nullable();
            $table->string('telephone_proprietaire_poi')->nullable();
            $table->enum('sexe_proprietaire_poi', ['Masculin', 'Féminin'])->nullable();
            
            // Géolocalisation point d'intérêt
            $table->decimal('latitude_poi', 10, 6)->nullable();
            $table->decimal('longitude_poi', 10, 6)->nullable();
            $table->decimal('altitude_poi', 8, 2)->nullable();
            $table->decimal('precision_poi', 8, 2)->nullable();
            
            // Champ pour identifier le numéro national du propriétaire (renommage)
            $table->string('numero_identification_proprietaire')->nullable();
            $table->string('telephone_proprietaire')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropColumn([
                'nom_complet',
                'nom_commercial',
                'nature_point_interet',
                'type_point_interet',
                'enseigne_petit_panneau',
                'enseigne_et_petit_panneau',
                'taille_enseigne',
                'taille_grands_panneaux',
                'photo_panneau',
                'numero_identification_proprietaire_poi',
                'prenom_proprietaire_poi',
                'nom_proprietaire_poi',
                'telephone_proprietaire_poi',
                'sexe_proprietaire_poi',
                'latitude_poi',
                'longitude_poi',
                'altitude_poi',
                'precision_poi',
                'numero_identification_proprietaire',
                'telephone_proprietaire'
            ]);
        });
    }
};
