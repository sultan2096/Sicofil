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
            $table->foreignId('taxe_id')->nullable()->after('unite_id')
                ->constrained('taxes')->onDelete('set null')
                ->comment('ID de la taxe appliquée');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['taxe_id']);
            $table->dropColumn('taxe_id');
        });
    }
};
