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
            // Champs manquants pour les unités commerciales
            $table->string('quartier')->nullable()->after('adresse');
            $table->string('nom_commerce')->nullable()->after('nature');
            $table->text('description')->nullable()->after('nom_commerce');
            $table->integer('nombre_employes')->nullable()->after('description');
            $table->decimal('chiffre_affaires_mensuel', 15, 2)->nullable()->after('nombre_employes');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif')->after('chiffre_affaires_mensuel');
            $table->text('observations')->nullable()->after('date_recensement');
            $table->unsignedBigInteger('zone_id')->nullable()->change();
            $table->unsignedBigInteger('taxe_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropColumn([
                'quartier',
                'nom_commerce',
                'description',
                'nombre_employes',
                'chiffre_affaires_mensuel',
                'statut',
                'observations'
            ]);
            $table->unsignedBigInteger('zone_id')->nullable(false)->change();
            $table->unsignedBigInteger('taxe_id')->nullable(false)->change();
        });
    }
};
