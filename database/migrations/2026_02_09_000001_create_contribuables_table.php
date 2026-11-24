<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table des contribuables (commerçants) pour le portail public
     */
    public function up(): void
    {
        Schema::create('contribuables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_id')->constrained('communes')->onDelete('cascade');
            $table->foreignId('unite_commerciale_id')->nullable()->constrained('unites_commerciales')->onDelete('set null');
            $table->string('telephone', 20)->unique()->comment('Identifiant de connexion principal');
            $table->string('email')->nullable();
            $table->string('nom_complet');
            $table->string('password');
            $table->string('otp_code', 6)->nullable()->comment('Code OTP pour vérification');
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable()->comment('Date de vérification du téléphone');
            $table->boolean('actif')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();

            // Index
            $table->index('commune_id');
            $table->index('telephone');
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribuables');
    }
};
