<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajout du lien entre paiements et contribuables (pour télépaiement)
     */
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->foreignId('contribuable_id')->nullable()->after('collecteur_id')
                ->constrained('contribuables')->onDelete('set null')
                ->comment('ID du contribuable (pour paiements en ligne)');
            
            $table->string('reference')->nullable()->after('commentaire')
                ->comment('Référence unique pour télépaiement');
            
            $table->string('transaction_id')->nullable()->after('reference')
                ->comment('ID transaction Wave/Orange Money');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['contribuable_id']);
            $table->dropColumn(['contribuable_id', 'reference', 'transaction_id']);
        });
    }
};
