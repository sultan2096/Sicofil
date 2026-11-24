<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour les webhooks Mobile Money (Wave, Orange Money, Free Money).
 * Ces endpoints reçoivent les notifications de paiement depuis les opérateurs.
 */
class MobileMoneyWebhookController extends Controller
{
    /**
     * Webhook Wave - Notification de paiement réussi/échoué.
     */
    public function wave(Request $request)
    {
        return $this->handleWebhook($request, 'wave');
    }

    /**
     * Webhook Orange Money - Notification de paiement réussi/échoué.
     */
    public function orangeMoney(Request $request)
    {
        return $this->handleWebhook($request, 'orange_money');
    }

    /**
     * Webhook Free Money - Notification de paiement réussi/échoué.
     */
    public function freeMoney(Request $request)
    {
        return $this->handleWebhook($request, 'free_money');
    }

    /**
     * Traitement générique des webhooks Mobile Money.
     */
    private function handleWebhook(Request $request, string $provider)
    {
        // Validation des données webhook
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'reference' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:success,failed,pending',
            'phone' => 'required|string',
            'timestamp' => 'required|integer',
            'signature' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }

        $data = $request->all();
        $transactionId = $data['transaction_id'];
        $reference = $data['reference'];
        $status = $data['status'];
        $amount = $data['amount'];
        $phone = $data['phone'];

        // Vérifier la signature si configurée (sécurité)
        if (isset($data['signature']) && !$this->verifySignature($data, $provider)) {
            Log::createLog('webhook_signature_failed', [
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Trouver le paiement correspondant par référence
        $paiement = Paiement::where('reference_transaction', $reference)->first();

        if (!$paiement) {
            Log::createLog('webhook_payment_not_found', [
                'provider' => $provider,
                'reference' => $reference,
                'transaction_id' => $transactionId,
            ]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Vérifier que le montant correspond
        if (abs($paiement->montant - $amount) > 0.01) {
            Log::createLog('webhook_amount_mismatch', [
                'provider' => $provider,
                'paiement_id' => $paiement->id,
                'expected' => $paiement->montant,
                'received' => $amount,
            ]);
            return response()->json(['error' => 'Amount mismatch'], 400);
        }

        // Mettre à jour le paiement selon le statut
        try {
            DB::beginTransaction();

            if ($status === 'success') {
                $paiement->update([
                    'statut' => 'valide',
                    'transaction_id' => $transactionId,
                    'mode_paiement' => $provider,
                    'updated_at' => now(),
                ]);

                Log::createLog('payment_validated_via_webhook', [
                    'provider' => $provider,
                    'paiement_id' => $paiement->id,
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                ]);

            } elseif ($status === 'failed') {
                $paiement->update([
                    'statut' => 'echoue',
                    'transaction_id' => $transactionId,
                    'mode_paiement' => $provider,
                    'updated_at' => now(),
                ]);

                Log::createLog('payment_failed_via_webhook', [
                    'provider' => $provider,
                    'paiement_id' => $paiement->id,
                    'transaction_id' => $transactionId,
                    'reason' => $data['reason'] ?? 'Unknown',
                ]);

            } elseif ($status === 'pending') {
                // Statut en attente - ne rien changer, juste logger
                Log::createLog('payment_pending_via_webhook', [
                    'provider' => $provider,
                    'paiement_id' => $paiement->id,
                    'transaction_id' => $transactionId,
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'paiement_id' => $paiement->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::createLog('webhook_processing_error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Vérifier la signature du webhook (si configurée).
     * À implémenter selon les spécifications de chaque opérateur.
     */
    private function verifySignature(array $data, string $provider): bool
    {
        // Clés secrètes à configurer dans .env
        $secret = config('services.' . $provider . '.webhook_secret');

        if (!$secret) {
            // Si pas de secret configuré, on accepte (à changer en prod)
            return true;
        }

        // Implémentation selon le protocole de signature de l'opérateur
        // Exemple générique pour HMAC-SHA256
        $signature = $data['signature'] ?? '';
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Endpoint de test pour simuler un webhook (dev uniquement).
     */
    public function testWebhook(Request $request)
    {
        if (app()->environment() !== 'local') {
            abort(403, 'Test endpoint only available in local environment');
        }

        return $this->handleWebhook($request, $request->input('provider', 'wave'));
    }
}
