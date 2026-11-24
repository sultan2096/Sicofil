<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communes', function (Blueprint $table) {
            $table->string('acronyme', 30)->nullable()->after('code');
        });
        // Sandiara = SICOSA, Thiès (code THIES) = SICOTH
        DB::table('communes')->whereRaw("LOWER(nom) LIKE '%sandiara%'")->update(['acronyme' => 'SICOSA']);
        DB::table('communes')->where('code', 'THIES')->update(['acronyme' => 'SICOTH']);
    }

    public function down(): void
    {
        Schema::table('communes', function (Blueprint $table) {
            $table->dropColumn('acronyme');
        });
    }
};
