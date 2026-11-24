<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tables pour webhooks Mobile Money et notifications
     */
    public function up(): void
    {
        // Webhooks entrants (Wave, Orange Money, Free Money)
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->onDelete('cascade');
            $table->foreignId('paiement_id')->nullable()->constrained('paiements')->onDelete('set null');
            $table->enum('provider', ['wave', 'orange_money', 'free_money'])->comment('Opérateur Mobile Money');
            $table->string('transaction_id')->nullable()->comment('ID transaction opérateur');
            $table->string('reference_externe')->nullable()->comment('Référence externe (Wave/OM)');
            $table->text('payload')->comment('Données JSON du webhook');
            $table->enum('statut', ['pending', 'success', 'failed', 'duplicate'])->default('pending');
            $table->string('montant')->nullable();
            $table->string('telephone_client')->nullable();
            $table->timestamp('webhook_received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            // Index
            $table->index('provider');
            $table->index('transaction_id');
            $table->index('reference_externe');
            $table->index('statut');
            $table->index('webhook_received_at');
        });

        // Notifications SMS / WhatsApp / Email
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('contribuable_id')->nullable()->constrained('contribuables')->onDelete('set null');
            $table->foreignId('paiement_id')->nullable()->constrained('paiements')->onDelete('set null');
            $table->enum('type', ['sms', 'whatsapp', 'email', 'push'])->comment('Canal de notification');
            $table->enum('categorie', [
                'recu_paiement',
                'rappel_taxe',
                'alerte_retard',
                'confirmation_recensement',
                'info_generale',
                'webhook_mobile_money'
            ])->comment('Type de notification');
            $table->string('destinataire')->comment('Téléphone, email ou device_token');
            $table->string('objet')->nullable();
            $table->text('message');
            $table->enum('statut', ['en_attente', 'envoye', 'echec', 'lu'])->default('en_attente');
            $table->timestamp('envoye_at')->nullable();
            $table->timestamp('lu_at')->nullable();
            $table->text('reponse_api')->nullable()->comment('Réponse du fournisseur SMS/WhatsApp');
            $table->string('id_externe')->nullable()->comment('ID retourné par le fournisseur');
            $table->integer('tentatives')->default(0);
            $table->text('erreur')->nullable();
            $table->timestamps();

            // Index
            $table->index('commune_id');
            $table->index('type');
            $table->index('categorie');
            $table->index('destinataire');
            $table->index('statut');
            $table->index('envoye_at');
        });

        // Journal d'audit central SICOFIL (toutes communes)
        Schema::create('audit_sicofil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('niveau', ['info', 'warning', 'error', 'critical'])->default('info');
            $table->string('action')->comment('Type d\'action');
            $table->string('module')->comment('Module concerné');
            $table->text('details')->nullable()->comment('JSON des détails');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Index
            $table->index('commune_id');
            $table->index('user_id');
            $table->index('niveau');
            $table->index('action');
            $table->index('module');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_sicofil');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('webhook_logs');
    }
};
