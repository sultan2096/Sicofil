<?php

namespace App\Console\Commands;

use App\Services\PaymentReconciliationService;
use App\Services\FraudDetectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Commande pour la réconciliation journalière automatique des paiements.
 * À exécuter via cron tous les jours à minuit.
 */
class DailyPaymentReconciliation extends Command
{
    protected $signature = 'payments:reconcile-daily {--date= : Date à réconcilier (Y-m-d). Par défaut hier.}';
    protected $description = 'Réconciliation journalière des paiements et détection de fraude';

    public function handle()
    {
        $date = $this->option('date') ?: now()->subDay()->format('Y-m-d');

        $this->info("Réconciliation pour la date : {$date}");
        $this->newLine();

        $reconciliationService = new PaymentReconciliationService();
        $fraudService = new FraudDetectionService();

        // Réconciliation
        $this->info('⏳ Réconciliation en cours...');
        $report = $reconciliationService->reconcile($date);

        $this->info("✓ Total paiements : {$report['total_paiements']}");
        $this->info("✓ Total montant : " . number_format($report['total_montant'], 0, ',', ' ') . " FCFA");

        foreach ($report['par_mode'] as $mode => $data) {
            $this->info("  - {$mode}: {$data['count']} paiements, " . number_format($data['montant'], 0, ',', ' ') . " FCFA");
        }

        if (!empty($report['anomalies'])) {
            $this->warn("⚠ Anomalies détectées : " . count($report['anomalies']));
        }

        if (!empty($report['doublons'])) {
            $this->warn("⚠ Doublons détectés : " . count($report['doublons']));
        }

        if (!empty($report['paiements_sans_gps'])) {
            $this->warn("⚠ Paiements sans GPS : " . count($report['paiements_sans_gps']));
        }

        if (!empty($report['paiements_hors_zone'])) {
            $this->warn("⚠ Paiements hors zone : " . count($report['paiements_hors_zone']));
        }

        $this->newLine();

        // Détection de fraude sur les paiements de la date
        $this->info('⏳ Détection de fraude...');
        $fraudReport = $fraudService->scanRecentPayments(48); // 48h pour inclure la date

        $this->info("✓ Paiements scannés : {$fraudReport['total_scanned']}");
        $this->info("✓ Risques critiques : {$fraudReport['critical_count']}");
        $this->info("✓ Risques élevés : {$fraudReport['high_count']}");

        if ($fraudReport['critical_count'] > 0 || $fraudReport['high_count'] > 0) {
            $this->warn("⚠ Alertes de fraude générées");
        }

        $this->newLine();
        $this->info('✅ Réconciliation terminée avec succès.');

        return 0;
    }
}
