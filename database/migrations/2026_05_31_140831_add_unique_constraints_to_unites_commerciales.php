<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer les doublons en gardant l'enregistrement le plus ancien (id le plus petit)
        $this->removeDuplicates('telephone');
        $this->removeDuplicates('email');
        $this->removeDuplicates('ninea');
        $this->removeDuplicates('nin_proprietaire');
        $this->removeDuplicates('numero_cin');
        $this->removeDuplicates('numero_immatriculation');

        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->unique('telephone', 'uc_telephone_unique');
            $table->unique('email', 'uc_email_unique');
            $table->unique('ninea', 'uc_ninea_unique');
            $table->unique('nin_proprietaire', 'uc_nin_proprietaire_unique');
            $table->unique('numero_cin', 'uc_numero_cin_unique');
            $table->unique('numero_immatriculation', 'uc_numero_immatriculation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unites_commerciales', function (Blueprint $table) {
            $table->dropUnique('uc_telephone_unique');
            $table->dropUnique('uc_email_unique');
            $table->dropUnique('uc_ninea_unique');
            $table->dropUnique('uc_nin_proprietaire_unique');
            $table->dropUnique('uc_numero_cin_unique');
            $table->dropUnique('uc_numero_immatriculation_unique');
        });
    }

    /**
     * Supprime les doublons d'une colonne en gardant l'enregistrement avec le plus petit id.
     */
    private function removeDuplicates(string $column): void
    {
        $sql = "DELETE FROM unites_commerciales t1
                USING unites_commerciales t2
                WHERE t1.id > t2.id
                  AND t1.{$column} IS NOT NULL
                  AND t1.{$column} = t2.{$column}
                  AND t1.{$column} != ''";

        DB::statement($sql);
    }
};
