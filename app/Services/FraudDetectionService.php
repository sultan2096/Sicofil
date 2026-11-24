<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service de détection de fraude dans les paiements.
 * Analyse les anomalies GPS, les doublons et les comportements suspects.
 */
class FraudDetectionService
{
    /**
     * Analyser un paiement pour détecter les fraudes potentielles.
     */
    public function analyzePayment(Paiement $paiement): array
    {
        $alerts = [];
        $riskScore = 0;

        // 1. Vérifier si le paiement a une géolocalisation
        if (!$paiement->payment_latitude || !$paiement->payment_longitude) {
            if ($paiement->collecteur && $paiement->collecteur->role === 'collecteur') {
                $alerts[] = [
                    'type' => 'missing_gps',
                    'severity' => 'high',
                    'message' => 'Paiement sans géolocalisation pour un collecteur',
                ];
                $riskScore += 30;
            }
        }

        // 2. Vérifier si le paiement est hors zone du collecteur
        if ($paiement->collecteur && $paiement->collecteur->role === 'collecteur') {
            if ($paiement->unite && $paiement->unite->zone_id !== $paiement->collecteur->zone_id) {
                $alerts[] = [
                    'type' => 'wrong_zone',
                    'severity' => 'high',
                    'message' => 'Paiement hors zone du collecteur',
                ];
                $riskScore += 40;
            }

            // 3. Vérifier la distance entre le paiement et l'unité (si géométrie disponible)
            if ($paiement->unite && $paiement->unite->latitude && $paiement->unite->longitude) {
                $distance = $this->calculateDistance(
                    $paiement->payment_latitude,
                    $paiement->payment_longitude,
                    $paiement->unite->latitude,
                    $paiement->unite->longitude
                );

                // Si la distance > 500m, suspect
                if ($distance > 0.5) {
                    $alerts[] = [
                        'type' => 'distance_anomaly',
                        'severity' => 'medium',
                        'message' => 'Paiement éloigné de ' . round($distance * 1000) . 'm de l\'unité',
                        'distance_km' => $distance,
                    ];
                    $riskScore += 20;
                }
            }
        }

        // 4. Vérifier les doublons récents (même unité, même montant, même collecteur, < 5 min)
        $recentDuplicate = Paiement::where('unite_id', $paiement->unite_id)
            ->where('collecteur_id', $paiement->collecteur_id)
            ->where('montant', $paiement->montant)
            ->where('id', '!=', $paiement->id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->first();

        if ($recentDuplicate) {
            $alerts[] = [
                'type' => 'duplicate',
                'severity' => 'high',
                'message' => 'Doublon détecté avec paiement #' . $recentDuplicate->id,
                'duplicate_id' => $recentDuplicate->id,
            ];
            $riskScore += 35;
        }

        // 5. Vérifier les paiements multiples en peu de temps (activité anormale)
        $recentPaymentsCount = Paiement::where('collecteur_id', $paiement->collecteur_id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->count();

        if ($recentPaymentsCount > 20) {
            $alerts[] = [
                'type' => 'high_frequency',
                'severity' => 'medium',
                'message' => 'Activité anormale: ' . $recentPaymentsCount . ' paiements en 10 min',
                'count' => $recentPaymentsCount,
            ];
            $riskScore += 15;
        }

        // 6. Vérifier les montants anormaux (écart > 3x la moyenne du collecteur)
        $averageAmount = Paiement::where('collecteur_id', $paiement->collecteur_id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->avg('montant') ?? 0;

        if ($averageAmount > 0 && $paiement->montant > $averageAmount * 3) {
            $alerts[] = [
                'type' => 'amount_anomaly',
                'severity' => 'medium',
                'message' => 'Montant ' . round($paiement->montant / $averageAmount) . 'x la moyenne du collecteur',
                'average' => $averageAmount,
                'ratio' => $paiement->montant / $averageAmount,
            ];
            $riskScore += 10;
        }

        // 7. Vérifier la précision GPS
        if ($paiement->payment_accuracy && $paiement->payment_accuracy > 100) {
            $alerts[] = [
                'type' => 'low_gps_accuracy',
                'severity' => 'low',
                'message' => 'Précision GPS faible: ' . $paiement->payment_accuracy . 'm',
                'accuracy' => $paiement->payment_accuracy,
            ];
            $riskScore += 5;
        }

        // Déterminer le niveau de risque
        $riskLevel = 'low';
        if ($riskScore >= 70) {
            $riskLevel = 'critical';
        } elseif ($riskScore >= 40) {
            $riskLevel = 'high';
        } elseif ($riskScore >= 20) {
            $riskLevel = 'medium';
        }

        return [
            'paiement_id' => $paiement->id,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'alerts' => $alerts,
        ];
    }

    /**
     * Scanner tous les paiements récents pour détecter les fraudes.
     */
    public function scanRecentPayments(int $hours = 24): array
    {
        $paiements = Paiement::where('created_at', '>=', Carbon::now()->subHours($hours))
            ->with(['collecteur', 'unite.zone'])
            ->get();

        $results = [];
        $criticalCount = 0;
        $highCount = 0;

        foreach ($paiements as $paiement) {
            $analysis = $this->analyzePayment($paiement);
            if ($analysis['risk_level'] === 'critical') {
                $criticalCount++;
            } elseif ($analysis['risk_level'] === 'high') {
                $highCount++;
            }
            $results[] = $analysis;
        }

        // Logger le scan
        Log::createLog('fraud_scan', [
            'period_hours' => $hours,
            'total_scanned' => count($results),
            'critical_count' => $criticalCount,
            'high_count' => $highCount,
        ]);

        return [
            'period_hours' => $hours,
            'total_scanned' => count($results),
            'critical_count' => $criticalCount,
            'high_count' => $highCount,
            'results' => $results,
        ];
    }

    /**
     * Calculer la distance en km entre deux points GPS (formule Haversine).
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Marquer un paiement comme suspect pour investigation.
     */
    public function markAsSuspicious(Paiement $paiement, string $reason): void
    {
        $paiement->update([
            'statut' => 'suspect',
            'commentaire' => ($paiement->commentaire ?? '') . ' [SUSPECT: ' . $reason . ']',
        ]);

        Log::createLog('payment_marked_suspicious', [
            'paiement_id' => $paiement->id,
            'reason' => $reason,
            'marked_by' => auth()->id(),
        ]);
    }
}
