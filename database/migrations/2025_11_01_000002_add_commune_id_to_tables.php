<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute commune_id aux tables pour la gestion multi-communes.
     */
    public function up(): void
    {
        // 1. Ajouter commune_id à users (nullable pour admin_sicofil)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('id')->constrained('communes')->onDelete('cascade');
        });

        // 2. Ajouter commune_id à zones_fiscales
        Schema::table('zones_fiscales', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('id')->constrained('communes')->onDelete('cascade');
        });

        // 3. Ajouter commune_id à taxes
        Schema::table('taxes', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('id')->constrained('communes')->onDelete('cascade');
        });

        // 4. Ajouter commune_id à unites_commerciales
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('id')->constrained('communes')->onDelete('cascade');
        });

        // 5. Ajouter commune_id à paiements
        Schema::table('paiements', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('id')->constrained('communes')->onDelete('cascade');
        });

        // 6. Ajouter commune_id à logs (nullable pour actions globales)
        Schema::table('logs', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('id')->constrained('communes')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
        });
        Schema::table('zones_fiscales', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
        });
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
        });
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
        });
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
        });
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
        });
    }
};
