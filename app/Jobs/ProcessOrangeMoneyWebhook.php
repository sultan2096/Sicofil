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

class ProcessOrangeMoneyWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    protected $webhookData;

    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
    }

    public function handle(): void
    {
        $data = $this->webhookData;

        // Extraire les données Orange Money
        $transactionId = $data['order_id'] ?? $data['transaction_id'] ?? null;
        $reference = $data['merchant_reference'] ?? $data['reference'] ?? null;
        $montant = $data['amount'] ?? null;
        $status = $data['status'] ?? null;

        if (!$reference || !$transactionId) {
            $this->fail('Webhook Orange Money invalide');
            return;
        }

        $paiement = Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            $this->logWebhook($transactionId, $reference, 'failed', 'Paiement non trouvé');
            return;
        }

        if ($status === 'SUCCESS' || $status === 'SUCCESSFUL') {
            $paiement->update([
                'statut' => 'valide',
                'transaction_id' => $transactionId,
                'commentaire' => 'Paiement Orange Money validé automatiquement',
            ]);

            // SMS confirmation
            if ($paiement->unite && $paiement->unite->telephone) {
                SendSmsNotification::dispatch(
                    $paiement->unite->telephone,
                    "✅ Paiement Orange Money reçu: {$paiement->montant} FCFA. Réf: {$paiement->reference}.",
                    'recu_paiement',
                    $paiement->commune_id
                );

                if (config('services.whatsapp.api_key')) {
                    SendWhatsAppNotification::dispatch(
                        $paiement->unite->telephone,
                        "Paiement Orange Money reçu: {$paiement->montant} FCFA. Réf: {$paiement->reference}.",
                        $paiement->commune_id
                    );
                }
            }

            $this->logWebhook($transactionId, $reference, 'success', null, $paiement->id, $paiement->commune_id);

            Log::createLog('webhook_orange_money_success', [
                'transaction_id' => $transactionId,
                'paiement_id' => $paiement->id,
                'montant' => $montant,
            ], null, $paiement->commune_id);
        } else {
            $this->logWebhook($transactionId, $reference, 'failed', "Statut OM: {$status}", $paiement->id, $paiement->commune_id);
        }
    }

    private function logWebhook($transactionId, $reference, $statut, $error = null, $paiementId = null, $communeId = null)
    {
        DB::table('webhook_logs')->insert([
            'commune_id' => $communeId,
            'provider' => 'orange_money',
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
