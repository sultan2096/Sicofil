<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service de réconciliation journalière des paiements.
 * Détecte les anomalies et génère des rapports de réconciliation.
 */
class PaymentReconciliationService
{
    /**
     * Effectuer la réconciliation journalière pour une date donnée.
     */
    public function reconcile(string $date = null): array
    {
        $date = $date ?: Carbon::yesterday()->format('Y-m-d');
        $report = [
            'date' => $date,
            'total_paiements' => 0,
            'total_montant' => 0,
            'par_mode' => [],
            'anomalies' => [],
            'doublons' => [],
            'paiements_sans_gps' => [],
            'paiements_hors_zone' => [],
        ];

        // Récupérer tous les paiements de la date
        $paiements = Paiement::whereDate('date_paiement', $date)
            ->with(['collecteur', 'unite.zone'])
            ->get();

        $report['total_paiements'] = $paiements->count();
        $report['total_montant'] = $paiements->sum('montant');

        // Regroupement par mode de paiement
        $report['par_mode'] = $paiements->groupBy('mode_paiement')
            ->map(fn($group) => [
                'count' => $group->count(),
                'montant' => $group->sum('montant'),
            ])
            ->toArray();

        // Détection des anomalies
        foreach ($paiements as $paiement) {
            // Paiements sans géolocalisation (pour les collecteurs)
            if ($paiement->collecteur && $paiement->collecteur->role === 'collecteur') {
                if (!$paiement->payment_latitude || !$paiement->payment_longitude) {
                    $report['paiements_sans_gps'][] = [
                        'id' => $paiement->id,
                        'collecteur' => $paiement->collecteur->name,
                        'montant' => $paiement->montant,
                    ];
                }

                // Paiements hors zone du collecteur
                if ($paiement->unite && $paiement->unite->zone_id !== $paiement->collecteur->zone_id) {
                    $report['paiements_hors_zone'][] = [
                        'id' => $paiement->id,
                        'collecteur' => $paiement->collecteur->name,
                        'unite' => $paiement->unite->nom_proprietaire,
                        'zone_unite' => $paiement->unite->zone->nom ?? 'N/A',
                        'zone_collecteur' => $paiement->collecteur->zone->nom ?? 'N/A',
                    ];
                }
            }

            // Référence de transaction manquante pour les paiements mobile money
            if (in_array($paiement->mode_paiement, ['wave', 'orange_money', 'free_money']) && !$paiement->reference_transaction) {
                $report['anomalies'][] = [
                    'type' => 'missing_reference',
                    'paiement_id' => $paiement->id,
                    'mode' => $paiement->mode_paiement,
                    'montant' => $paiement->montant,
                ];
            }
        }

        // Détection des doublons potentiels (même unité, même date, même montant, même collecteur)
        $doublons = DB::select("
            SELECT unite_id, collecteur_id, date_paiement, montant, COUNT(*) as count
            FROM paiements
            WHERE date_paiement = ?
            GROUP BY unite_id, collecteur_id, date_paiement, montant
            HAVING COUNT(*) > 1
        ", [$date]);

        foreach ($doublons as $doublon) {
            $report['doublons'][] = [
                'unite_id' => $doublon->unite_id,
                'collecteur_id' => $doublon->collecteur_id,
                'date' => $doublon->date_paiement,
                'montant' => $doublon->montant,
                'count' => $doublon->count,
            ];
        }

        // Logger le rapport
        Log::createLog('daily_reconciliation', [
            'date' => $date,
            'report' => $report,
        ]);

        return $report;
    }

    /**
     * Réconciliation automatique lancée par cron (tous les jours à minuit).
     */
    public function autoReconcile(): void
    {
        $report = $this->reconcile();

        // Envoyer une alerte si des anomalies graves sont détectées
        if (count($report['doublons']) > 0 || count($report['paiements_hors_zone']) > 0) {
            $this->sendAlert($report);
        }
    }

    /**
     * Envoyer une alerte en cas d'anomalies graves.
     */
    private function sendAlert(array $report): void
    {
        // À implémenter avec le service de notification
        // Pour l'instant, on loggue
        Log::createLog('reconciliation_alert', [
            'date' => $report['date'],
            'doublons_count' => count($report['doublons']),
            'hors_zone_count' => count($report['paiements_hors_zone']),
        ]);
    }

    /**
     * Générer un rapport de réconciliation pour une période.
     */
    public function generatePeriodReport(string $dateDebut, string $dateFin): array
    {
        $reports = [];
        $current = Carbon::parse($dateDebut);
        $end = Carbon::parse($dateFin);

        while ($current->lte($end)) {
            $reports[$current->format('Y-m-d')] = $this->reconcile($current->format('Y-m-d'));
            $current->addDay();
        }

        return $reports;
    }
}
