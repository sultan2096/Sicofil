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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collecteur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('unite_id')->constrained('unites_commerciales')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement');
            $table->enum('mode_paiement', ['cash', 'wave', 'orange_money']);
            $table->enum('statut', ['en_attente', 'valide', 'annule'])->default('valide');
            $table->text('commentaire')->nullable();
            $table->string('reference_transaction')->nullable(); // Pour Wave/Orange Money
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
}; 