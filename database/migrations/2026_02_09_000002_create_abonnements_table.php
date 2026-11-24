<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Gestion des abonnements SaaS des communes
     */
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->constrained('communes')->onDelete('cascade');
            $table->enum('statut', ['actif', 'suspendu', 'expire', 'test'])->default('test');
            $table->enum('formule', ['basique', 'standard', 'premium'])->default('standard');
            $table->decimal('montant_fixe_annuel', 10, 2)->default(0)->comment('Montant fixe annuel FCFA');
            $table->decimal('pourcentage_recettes', 5, 2)->default(0)->comment('% des recettes communales');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->date('date_expiration')->nullable();
            $table->integer('nb_collecteurs_max')->default(10);
            $table->integer('nb_unites_max')->nullable()->comment('null = illimité');
            $table->boolean('acces_geomarketing')->default(true);
            $table->boolean('acces_rapports_avances')->default(true);
            $table->boolean('support_premium')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('commune_id');
            $table->index('statut');
            $table->index('date_expiration');
        });

        // Facturation & paiements d'abonnement
        Schema::create('factures_abonnement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abonnement_id')->constrained('abonnements')->onDelete('cascade');
            $table->foreignId('commune_id')->constrained('communes')->onDelete('cascade');
            $table->string('numero_facture')->unique();
            $table->enum('periode', ['mensuel', 'trimestriel', 'annuel'])->default('annuel');
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->decimal('montant_fixe', 10, 2)->default(0);
            $table->decimal('montant_variable', 10, 2)->default(0)->comment('% recettes');
            $table->decimal('montant_total', 10, 2);
            $table->enum('statut', ['en_attente', 'payee', 'en_retard', 'annulee'])->default('en_attente');
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('commune_id');
            $table->index('numero_facture');
            $table->index('statut');
            $table->index('date_echeance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures_abonnement');
        Schema::dropIfExists('abonnements');
    }
};
