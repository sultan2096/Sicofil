<?php

namespace App\Jobs;

use App\Models\Paiement;
use App\Models\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendSmsNotification;
use App\Jobs\SendWhatsAppNotification;

class ProcessWaveWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    protected $webhookData;

    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
    }

    public function handle(): void
    {
        $data = $this->webhookData;

        // Extraire les données du webhook Wave
        $transactionId = $data['id'] ?? null;
        $reference = $data['client_reference'] ?? null;
        $montant = $data['amount'] ?? null;
        $currency = $data['currency'] ?? 'XOF';
        $status = $data['status'] ?? null;
        $telephone = $data['customer_phone'] ?? null;

        if (!$reference || !$transactionId) {
            $this->fail('Webhook Wave invalide : référence ou transaction_id manquant');
            return;
        }

        // Récupérer le paiement
        $paiement = Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            $this->logWebhook($transactionId, $reference, 'failed', 'Paiement non trouvé');
            return;
        }

        // Vérifier le montant
        if ($montant && abs($montant - $paiement->montant) > 1) {
            $this->logWebhook($transactionId, $reference, 'failed', 'Montant incorrect');
            return;
        }

        // Mettre à jour le paiement selon le statut
        if ($status === 'successful' || $status === 'completed') {
            $paiement->update([
                'statut' => 'valide',
                'transaction_id' => $transactionId,
                'commentaire' => 'Paiement Wave validé automatiquement',
            ]);

            // Envoyer SMS de confirmation
            if ($paiement->unite && $paiement->unite->telephone) {
                SendSmsNotification::dispatch(
                    $paiement->unite->telephone,
                    "✅ Paiement Wave reçu: {$paiement->montant} FCFA. Réf: {$paiement->reference}. Merci!",
                    'recu_paiement',
                    $paiement->commune_id
                );

                if (config('services.whatsapp.api_key')) {
                    SendWhatsAppNotification::dispatch(
                        $paiement->unite->telephone,
                        "Paiement Wave reçu: {$paiement->montant} FCFA. Réf: {$paiement->reference}.",
                        $paiement->commune_id
                    );
                }
            }

            $this->logWebhook($transactionId, $reference, 'success', null, $paiement->id, $paiement->commune_id);

            // Log système
            Log::createLog('webhook_wave_success', [
                'transaction_id' => $transactionId,
                'paiement_id' => $paiement->id,
                'montant' => $montant,
            ], null, $paiement->commune_id);
        } else {
            $this->logWebhook($transactionId, $reference, 'failed', "Statut Wave: {$status}", $paiement->id, $paiement->commune_id);
        }
    }

    private function logWebhook($transactionId, $reference, $statut, $error = null, $paiementId = null, $communeId = null)
    {
        DB::table('webhook_logs')->insert([
            'commune_id' => $communeId,
            'provider' => 'wave',
            'transaction_id' => $transactionId,
            'reference_externe' => $reference,
            'paiement_id' => $paiementId,
            'statut' => $statut,
            'payload' => json_encode($this->webhookData),
            'error_message' => $error,
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
