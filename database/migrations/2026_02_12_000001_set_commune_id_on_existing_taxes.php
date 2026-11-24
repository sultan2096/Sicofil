<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assigne la commune Sandiara aux taxes sans commune_id.
     */
    public function up(): void
    {
        $communeId = DB::table('communes')
            ->where('code', 'SANDIARA')
            ->orWhere('nom', 'Sandiara')
            ->value('id');

        if ($communeId) {
            DB::table('taxes')->whereNull('commune_id')->update(['commune_id' => $communeId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel : remettre null pour les taxes de Sandiara
        $communeId = DB::table('communes')
            ->where('code', 'SANDIARA')
            ->orWhere('nom', 'Sandiara')
            ->value('id');

        if ($communeId) {
            DB::table('taxes')->where('commune_id', $communeId)->update(['commune_id' => null]);
        }
    }
};
