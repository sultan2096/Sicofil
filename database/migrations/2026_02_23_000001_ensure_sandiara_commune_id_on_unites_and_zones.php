<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assure que les unités commerciales et zones fiscales de Sandiara
     * ont bien commune_id renseigné pour qu'elles s'affichent sur la carte.
     */
    public function up(): void
    {
        $communeId = DB::table('communes')
            ->where('code', 'SANDIARA')
            ->orWhere('nom', 'Sandiara')
            ->value('id');

        if (!$communeId) {
            return;
        }

        // Zones fiscales sans commune_id → Sandiara (données historiques SICOSA)
        DB::table('zones_fiscales')->whereNull('commune_id')->update(['commune_id' => $communeId]);

        // Unités commerciales sans commune_id → Sandiara
        DB::table('unites_commerciales')->whereNull('commune_id')->update(['commune_id' => $communeId]);

        // Taxes sans commune_id (au cas où 2026_02_12 n'a pas été exécutée)
        DB::table('taxes')->whereNull('commune_id')->update(['commune_id' => $communeId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne pas annuler pour éviter de cacher à nouveau les données
    }
};
