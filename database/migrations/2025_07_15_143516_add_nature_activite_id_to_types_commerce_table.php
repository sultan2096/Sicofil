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
        Schema::table('types_commerce', function (Blueprint $table) {
            $table->foreignId('nature_activite_id')->nullable()->after('description')->constrained('nature_activites')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('types_commerce', function (Blueprint $table) {
            $table->dropForeign(['nature_activite_id']);
            $table->dropColumn('nature_activite_id');
        });
    }
};
