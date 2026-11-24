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
        Schema::table('taxes', function (Blueprint $table) {
            $table->json('categories_applicables')->nullable()->comment('Catégories d\'unités commerciales applicables (activite_commerciale, point_interet)');
            $table->json('natures_applicables')->nullable()->comment('Natures d\'activité commerciales applicables');
            $table->json('types_commerce_applicables')->nullable()->comment('Types de commerce applicables');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn(['categories_applicables', 'natures_applicables', 'types_commerce_applicables']);
        });
    }
};
