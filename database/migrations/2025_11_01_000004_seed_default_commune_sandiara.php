<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crée la commune Sandiara et assigne les données existantes à cette commune.
     */
    public function up(): void
    {
        $sandiaraId = DB::table('communes')->insertGetId([
            'nom' => 'Sandiara',
            'code' => 'SANDIARA',
            'region' => 'Thiès',
            'departement' => 'Mbour',
            'actif' => true,
            'description' => 'Commune de Sandiara - Instance initiale SICOSA/SICOFIL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assigner les données existantes à Sandiara
        DB::table('users')->whereNull('commune_id')->update(['commune_id' => $sandiaraId]);
        DB::table('zones_fiscales')->whereNull('commune_id')->update(['commune_id' => $sandiaraId]);
        DB::table('taxes')->whereNull('commune_id')->update(['commune_id' => $sandiaraId]);
        DB::table('unites_commerciales')->whereNull('commune_id')->update(['commune_id' => $sandiaraId]);
        DB::table('paiements')->whereNull('commune_id')->update(['commune_id' => $sandiaraId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $sandiara = DB::table('communes')->where('code', 'SANDIARA')->first();
        if ($sandiara) {
            DB::table('users')->where('commune_id', $sandiara->id)->update(['commune_id' => null]);
            DB::table('zones_fiscales')->where('commune_id', $sandiara->id)->update(['commune_id' => null]);
            DB::table('taxes')->where('commune_id', $sandiara->id)->update(['commune_id' => null]);
            DB::table('unites_commerciales')->where('commune_id', $sandiara->id)->update(['commune_id' => null]);
            DB::table('paiements')->where('commune_id', $sandiara->id)->update(['commune_id' => null]);
            DB::table('communes')->where('id', $sandiara->id)->delete();
        }
    }
};
