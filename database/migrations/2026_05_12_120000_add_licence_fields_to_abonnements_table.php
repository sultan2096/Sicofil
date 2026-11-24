<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            if (!Schema::hasColumn('abonnements', 'frequence_paiement')) {
                $table->string('frequence_paiement', 30)->default('annuel')->after('pourcentage_recettes');
            }
            if (!Schema::hasColumn('abonnements', 'montant_achat_licence')) {
                $table->decimal('montant_achat_licence', 12, 2)->default(0)->after('frequence_paiement');
            }
            if (!Schema::hasColumn('abonnements', 'frais_maintenance')) {
                $table->decimal('frais_maintenance', 12, 2)->default(0)->after('montant_achat_licence');
            }
        });
    }

    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            foreach (['frais_maintenance', 'montant_achat_licence', 'frequence_paiement'] as $column) {
                if (Schema::hasColumn('abonnements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
