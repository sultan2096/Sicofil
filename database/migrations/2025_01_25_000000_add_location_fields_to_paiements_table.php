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
        Schema::table('paiements', function (Blueprint $table) {
            // Champs de géolocalisation pour les paiements
            $table->decimal('payment_latitude', 10, 8)->nullable()->after('reference_transaction');
            $table->decimal('payment_longitude', 11, 8)->nullable()->after('payment_latitude');
            $table->decimal('payment_accuracy', 8, 2)->nullable()->after('payment_longitude');
            $table->string('payment_method')->nullable()->after('payment_accuracy'); // 'GPS', 'Manual'
            
            // Index pour les requêtes géographiques
            $table->index(['payment_latitude', 'payment_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex(['payment_latitude', 'payment_longitude']);
            $table->dropColumn([
                'payment_latitude',
                'payment_longitude', 
                'payment_accuracy',
                'payment_method'
            ]);
        });
    }
};
