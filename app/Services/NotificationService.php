<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Log;
use Illuminate\Support\Facades\Http;

/**
 * Service de notifications SMS/WhatsApp pour les paiements.
 * Préparé pour intégration avec Orange SMS, Twilio, etc.
 */
class NotificationService
{
    /**
     * Envoyer un reçu de paiement par SMS.
     */
    public function sendReceiptSMS(Paiement $paiement, string $phoneNumber): array
    {
        $message = $this->formatReceiptMessage($paiement);

        try {
            // Placeholder pour intégration Orange SMS / Twilio
            // $response = Http::post(config('services.sms.endpoint'), [
            //     'to' => $phoneNumber,
            //     'message' => $message,
            //     'api_key' => config('services.sms.api_key'),
            // ]);

            throw new \Exception('Fournisseur SMS réel non configuré');

            Log::createLog('receipt_sms_sent', [
                'paiement_id' => $paiement->id,
                'phone' => $this->maskPhone($phoneNumber),
                'status' => $response['status'],
            ]);

            return [
                'success' => true,
                'message_id' => $response['message_id'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::createLog('receipt_sms_failed', [
                'paiement_id' => $paiement->id,
                'phone' => $this->maskPhone($phoneNumber),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envoyer un reçu de paiement par WhatsApp.
     */
    public function sendReceiptWhatsApp(Paiement $paiement, string $phoneNumber): array
    {
        $message = $this->formatReceiptMessage($paiement);

        try {
            // Placeholder pour intégration WhatsApp Business API / Twilio WhatsApp
            // $response = Http::post(config('services.whatsapp.endpoint'), [
            //     'to' => 'whatsapp:' . $phoneNumber,
            //     'message' => $message,
            //     'api_key' => config('services.whatsapp.api_key'),
            // ]);

            throw new \Exception('Fournisseur WhatsApp réel non configuré');

            Log::createLog('receipt_whatsapp_sent', [
                'paiement_id' => $paiement->id,
                'phone' => $this->maskPhone($phoneNumber),
                'status' => $response['status'],
            ]);

            return [
                'success' => true,
                'message_id' => $response['message_id'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::createLog('receipt_whatsapp_failed', [
                'paiement_id' => $paiement->id,
                'phone' => $this->maskPhone($phoneNumber),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envoyer une alerte de fraude aux gestionnaires.
     */
    public function sendFraudAlert(array $fraudData): array
    {
        $message = $this->formatFraudAlertMessage($fraudData);

        try {
            // Placeholder pour envoyer aux gestionnaires
            // Pour l'instant, on loggue seulement
            Log::createLog('fraud_alert_sent', [
                'fraud_data' => $fraudData,
                'message' => $message,
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            Log::createLog('fraud_alert_failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Formater le message de reçu.
     */
    private function formatReceiptMessage(Paiement $paiement): string
    {
        $paiement->load(['unite', 'unite.zone', 'collecteur']);

        return "SICOFIL - Reçu #" . str_pad($paiement->id, 8, '0', STR_PAD_LEFT) . "\n" .
               "Date: " . $paiement->date_paiement->format('d/m/Y H:i') . "\n" .
               "Montant: " . number_format($paiement->montant, 0, ',', ' ') . " FCFA\n" .
               "Mode: " . ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) . "\n" .
               "Unité: " . ($paiement->unite->nom_proprietaire ?? 'N/A') . "\n" .
               "Zone: " . ($paiement->unite->zone->nom ?? 'N/A') . "\n" .
               "Collecteur: " . ($paiement->collecteur->name ?? 'N/A') . "\n" .
               "Vérifiez sur: " . route('paiements.verify', $paiement);
    }

    /**
     * Formater le message d'alerte fraude.
     */
    private function formatFraudAlertMessage(array $fraudData): string
    {
        return "ALERTE FRAUDE SICOFIL\n" .
               "Paiement #" . ($fraudData['paiement_id'] ?? 'N/A') . "\n" .
               "Niveau de risque: " . strtoupper($fraudData['risk_level'] ?? 'N/A') . "\n" .
               "Score: " . ($fraudData['risk_score'] ?? 0) . "/100\n" .
               "Alertes: " . count($fraudData['alerts'] ?? []) . "\n" .
               "Action requise immédiate.";
    }

    /**
     * Masquer le numéro de téléphone (GDPR).
     */
    private function maskPhone(string $phone): string
    {
        if (strlen($phone) < 4) {
            return '***';
        }
        return substr($phone, 0, 2) . '***' . substr($phone, -2);
    }
}
