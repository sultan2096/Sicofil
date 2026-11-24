<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collector_tracking', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->after('collector_id')->constrained('communes')->onDelete('set null');
            $table->index('commune_id');
        });
    }

    public function down(): void
    {
        Schema::table('collector_tracking', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
            $table->dropIndex(['commune_id']);
            $table->dropColumn('commune_id');
        });
    }
};
