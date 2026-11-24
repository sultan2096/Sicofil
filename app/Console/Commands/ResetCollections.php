<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\Log;
use App\Models\User;
use App\Models\Taxe;
use App\Models\ZoneFiscale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sicosa:reset-collections {--force : Force l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise toutes les données de collecte (unités, paiements, logs)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== RÉINITIALISATION DES DONNÉES DE COLLECTE SICOSA ===');
        $this->newLine();

        // Afficher l'état initial
        $this->displayCurrentState();

        // Demander confirmation si pas de --force
        if (!$this->option('force')) {
            $this->warn('⚠️  ATTENTION: Cette opération va supprimer DÉFINITIVEMENT:');
            $this->line('✓ Toutes les unités commerciales recensées');
            $this->line('✓ Tous les paiements collectés');
            $this->line('✓ Tous les logs d\'activité');
            $this->line('✓ Toutes les photos des unités');
            $this->line('✓ Toutes les données de la carte');
            $this->newLine();

            $this->info('Les éléments CONSERVÉS:');
            $this->line('• Les comptes collecteurs');
            $this->line('• Les taxes configurées');
            $this->line('• Les zones fiscales');
            $this->line('• Les comptes gestionnaire et maire');
            $this->newLine();

            if (!$this->confirm('Voulez-vous vraiment continuer ?')) {
                $this->error('❌ Opération annulée.');
                return 1;
            }
        }

        $this->info('🔄 SUPPRESSION EN COURS...');
        $this->newLine();

        try {
            // Désactiver les vérifications de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Supprimer tous les paiements
            $paiementsCount = Paiement::count();
            Paiement::truncate();
            $this->line("✓ Supprimé $paiementsCount paiements");

            // 2. Supprimer toutes les photos des unités
            $photosDeleted = $this->deleteUnitPhotos();
            $this->line("✓ Supprimé $photosDeleted photos");

            // 3. Supprimer toutes les unités commerciales
            $unitesCount = UniteCommerciale::count();
            UniteCommerciale::truncate();
            $this->line("✓ Supprimé $unitesCount unités commerciales");

            // 4. Supprimer tous les logs d'activité
            $logsCount = Log::count();
            Log::truncate();
            $this->line("✓ Supprimé $logsCount logs");

            // 5. Réinitialiser les auto-increment
            DB::statement('ALTER TABLE paiements AUTO_INCREMENT = 1;');
            DB::statement('ALTER TABLE unites_commerciales AUTO_INCREMENT = 1;');
            DB::statement('ALTER TABLE logs AUTO_INCREMENT = 1;');
            $this->line('✓ Réinitialisé les compteurs automatiques');

            // Réactiver les vérifications de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info('✅ NETTOYAGE TERMINÉ AVEC SUCCÈS!');
            $this->newLine();

            // Afficher l'état final
            $this->displayFinalState();

            return 0;

        } catch (\Exception $e) {
            // Réactiver les vérifications en cas d'erreur
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->error('❌ ERREUR: ' . $e->getMessage());
            $this->error('La base de données pourrait être dans un état incohérent.');
            $this->error('Veuillez restaurer une sauvegarde si disponible.');

            return 1;
        }
    }

    /**
     * Afficher l'état actuel de la base de données
     */
    private function displayCurrentState()
    {
        $this->info('📊 ÉTAT INITIAL:');
        $this->table(
            ['Élément', 'Nombre'],
            [
                ['Unités commerciales', UniteCommerciale::count()],
                ['Paiements', Paiement::count()],
                ['Logs', Log::count()],
                ['Collecteurs', User::where('role', 'collecteur')->count()],
                ['Taxes', Taxe::count()],
                ['Zones fiscales', ZoneFiscale::count()],
            ]
        );
        $this->newLine();
    }

    /**
     * Afficher l'état final de la base de données
     */
    private function displayFinalState()
    {
        $this->info('📊 ÉTAT FINAL:');
        $this->table(
            ['Élément', 'Nombre', 'Statut'],
            [
                ['Unités commerciales', UniteCommerciale::count(), '🗑️  Supprimées'],
                ['Paiements', Paiement::count(), '🗑️  Supprimés'],
                ['Logs', Log::count(), '🗑️  Supprimés'],
                ['Collecteurs', User::where('role', 'collecteur')->count(), '✅ Conservés'],
                ['Taxes', Taxe::count(), '✅ Conservées'],
                ['Zones fiscales', ZoneFiscale::count(), '✅ Conservées'],
            ]
        );
        $this->newLine();

        $this->info('🎯 PROCHAINES ÉTAPES:');
        $this->line('• Les collecteurs peuvent maintenant recommencer le recensement');
        $this->line('• La carte sera vide jusqu\'aux nouveaux recensements');
        $this->line('• Les statistiques seront remises à zéro');
        $this->line('• Les comptes utilisateurs sont toujours actifs');
        $this->newLine();

        $this->info('🔗 ACCÈS:');
        $this->line('• Gestionnaire: gestionnaire01 / gestionnaire2025');
        $this->line('• Collecteurs: collecteur01-05 / collecteur2025');
        $this->line('• Maire: maire2025 / sicosa2025');
    }

    /**
     * Supprimer toutes les photos des unités
     */
    private function deleteUnitPhotos()
    {
        $deleted = 0;
        
        try {
            $files = Storage::disk('public')->files('unites');
            foreach ($files as $file) {
                Storage::disk('public')->delete($file);
                $deleted++;
            }
        } catch (\Exception $e) {
            $this->warn("Attention: Erreur lors de la suppression des photos: " . $e->getMessage());
        }

        return $deleted;
    }
} 