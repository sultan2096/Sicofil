<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Commune;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssignSandiaraData extends Command
{
    protected $signature = 'commune:assign-sandiara
                            {--dry-run : Afficher les changements sans les appliquer}';

    protected $description = 'Crée la commune Sandiara si besoin et rattache toutes les données sans commune à Sandiara (pour voir les données Sandiara)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Mode dry-run : aucun changement en base.');
        }

        // Récupérer ou créer Sandiara
        $sandiara = Commune::where('code', 'SANDIARA')->first();
        if (!$sandiara) {
            if ($dryRun) {
                $this->info('La commune Sandiara serait créée (code SANDIARA).');
            } else {
                $sandiara = Commune::create([
                    'nom' => 'Sandiara',
                    'code' => 'SANDIARA',
                    'region' => 'Thiès',
                    'departement' => 'Mbour',
                    'actif' => true,
                    'description' => 'Commune de Sandiara - Instance initiale SICOSA/SICOFIL',
                ]);
                $this->info("Commune Sandiara créée (id: {$sandiara->id}).");
            }
        } else {
            $this->info("Commune Sandiara trouvée (id: {$sandiara->id}).");
        }

        $sandiaraId = $sandiara->id ?? null;

        $tables = [
            'users' => 'Utilisateurs',
            'zones_fiscales' => 'Zones fiscales',
            'taxes' => 'Taxes',
            'unites_commerciales' => 'Unités commerciales',
            'paiements' => 'Paiements',
            'logs' => 'Logs',
        ];

        $rows = [];
        $total = 0;
        foreach ($tables as $table => $label) {
            if (!Schema::hasColumn($table, 'commune_id')) {
                $this->warn("Table {$table} : pas de colonne commune_id, ignorée.");
                continue;
            }
            $count = DB::table($table)->whereNull('commune_id')->count();
            $total += $count;
            $action = $dryRun ? '(dry-run)' : ($count > 0 ? 'Rattacher à Sandiara' : '—');
            $rows[] = [$table, 'commune_id', $count, $action];
            if (!$dryRun && $count > 0) {
                DB::table($table)->whereNull('commune_id')->update(['commune_id' => $sandiaraId]);
            }
        }

        $this->newLine();
        $this->table(['Table', 'Colonne', 'Lignes à rattacher', 'Action'], $rows);

        $this->newLine();
        if ($total > 0) {
            if ($dryRun) {
                $this->info("Au total {$total} lignes seraient rattachées à Sandiara. Relancez sans --dry-run pour appliquer.");
            } else {
                $this->info("{$total} lignes rattachées à Sandiara. Vous devriez maintenant voir les données Sandiara.");
            }
        } else {
            $this->info('Aucune donnée sans commune à rattacher. Les données Sandiara sont déjà liées ou la base est vide.');
        }

        return self::SUCCESS;
    }
}
