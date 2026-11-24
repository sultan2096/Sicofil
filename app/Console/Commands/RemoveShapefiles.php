<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveShapefiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *  php artisan sicosa:remove-shapefiles --schema=public           # dry-run (liste les tables)
     *  php artisan sicosa:remove-shapefiles --schema=public --execute # supprime les tables listées
     */
    protected $signature = 'sicosa:remove-shapefiles 
                            {--schema=public : Schéma PostgreSQL à scanner} 
                            {--execute : Exécuter la suppression (sinon dry-run)}';

    /**
     * The console command description.
     */
    protected $description = 'Supprimer les couches shapefiles importées (tables PostGIS candidates) du schéma choisi';

    /**
     * App tables to protect even if they contain geometry columns.
     * Adjust this list to your application models.
     */
    private array $protectedTables = [
        // Domain/app core tables to NEVER drop
        'zones_fiscales',
        'unites_commerciales',
        'paiements',
        'taxes',
        'type_commerces',
        'users',
        'logs',
        'migrations',
        // Add more if needed
    ];

    public function handle(): int
    {
        $schema = (string) $this->option('schema');
        $execute = (bool) $this->option('execute');

        $this->info("Schéma: {$schema}");
        $this->info($execute ? 'Mode: EXECUTION (DROP TABLE)' : 'Mode: DRY-RUN (aucune suppression)');

        // 1) Candidats via geometry_columns
        $fromGeometryColumns = DB::select("
            SELECT f_table_schema AS schema, f_table_name AS name
            FROM geometry_columns
            WHERE f_table_schema = ?
            ORDER BY f_table_name
        ", [$schema]);

        // 2) Candidats via colonne 'geom'
        $fromGeomColumn = DB::select("
            SELECT table_schema AS schema, table_name AS name
            FROM information_schema.columns
            WHERE column_name = 'geom'
              AND table_schema = ?
            ORDER BY table_name
        ", [$schema]);

        // 3) Candidats via colonne 'wkb_geometry'
        $fromWkbGeometry = DB::select("
            SELECT table_schema AS schema, table_name AS name
            FROM information_schema.columns
            WHERE column_name = 'wkb_geometry'
              AND table_schema = ?
            ORDER BY table_name
        ", [$schema]);

        // Fusionner et dédupliquer
        $candidates = collect(array_merge($fromGeometryColumns, $fromGeomColumn, $fromWkbGeometry))
            ->map(fn($r) => ['schema' => $r->schema, 'name' => $r->name])
            ->unique(fn($r) => $r['schema'].'.'.$r['name'])
            ->values();

        if ($candidates->isEmpty()) {
            $this->info('Aucune table candidate trouvée.');
            return self::SUCCESS;
        }

        // Filtrer celles protégées par liste blanche applicative
        $toDrop = $candidates->reject(function ($t) {
            return in_array($t['name'], $this->protectedTables, true);
        })->values();

        // Affichage
        $this->line('');
        $this->info('Tables candidates (détectées comme couches shapefile):');
        foreach ($candidates as $t) {
            $mark = in_array($t['name'], $this->protectedTables, true) ? ' (PROTÉGÉE)' : '';
            $this->line("- {$t['schema']}.{$t['name']}{$mark}");
        }

        $this->line('');
        $this->info('Tables qui seront supprimées (hors protégées):');
        if ($toDrop->isEmpty()) {
            $this->line('- Aucune (toutes sont protégées ou aucune candidate)');
        } else {
            foreach ($toDrop as $t) {
                $this->line("- {$t['schema']}.{$t['name']}");
            }
        }

        if (!$execute) {
            $this->line('');
            $this->comment('Dry-run terminé. Ajoutez --execute pour effectuer la suppression.');
            return self::SUCCESS;
        }

        if ($toDrop->isEmpty()) {
            $this->warn('Rien à supprimer.');
            return self::SUCCESS;
        }

        // Exécuter
        DB::beginTransaction();
        try {
            foreach ($toDrop as $t) {
                $sql = sprintf('DROP TABLE IF EXISTS %s.%s CASCADE;', $this->quoteIdent($t['schema']), $this->quoteIdent($t['name']));
                DB::statement($sql);
                $this->info("Supprimé: {$t['schema']}.{$t['name']}");
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erreur lors de la suppression: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info('Suppression terminée avec succès.');
        return self::SUCCESS;
    }

    private function quoteIdent(string $identifier): string
    {
        // Simple quote identifier for PostgreSQL
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}


