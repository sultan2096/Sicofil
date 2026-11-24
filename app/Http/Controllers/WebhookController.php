<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Log;
use App\Jobs\ProcessWaveWebhook;
use App\Jobs\ProcessOrangeMoneyWebhook;
use App\Jobs\SendSmsNotification;
use App\Jobs\SendWhatsAppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur Webhooks Mobile Money
 * Réception et traitement des notifications de paiement Wave, Orange Money, Free Money
 */
class WebhookController extends Controller
{
    /**
     * Webhook Wave Sénégal
     */
    public function waveWebhook(Request $request)
    {
        $reference = $request->input('client_reference');
        $transactionId = $request->input('id');
        $montant = $request->input('amount');
        $telephone = $request->input('customer_phone');

        // Log le webhook reçu
        DB::table('webhook_logs')->insert([
            'provider' => 'wave',
            'transaction_id' => $transactionId,
            'reference_externe' => $reference,
            'payload' => json_encode($request->all()),
            'statut' => 'pending',
            'montant' => $montant,
            'telephone_client' => $telephone,
            'webhook_received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vérifier la signature Wave
        $signature = $request->header('X-Wave-Signature');
        if (!$this->verifyWaveSignature($request->all(), $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Traiter en asynchrone via job
        ProcessWaveWebhook::dispatch($request->all());

        return response()->json(['success' => true], 200);
    }

    /**
     * Webhook Orange Money Sénégal
     */
    public function orangeMoneyWebhook(Request $request)
    {
        $reference = $request->input('merchant_reference') ?? $request->input('reference');
        $transactionId = $request->input('order_id') ?? $request->input('transaction_id');
        $montant = $request->input('amount');
        $telephone = $request->input('customer_phone') ?? $request->input('msisdn');

        // Log le webhook reçu
        DB::table('webhook_logs')->insert([
            'provider' => 'orange_money',
            'transaction_id' => $transactionId,
            'reference_externe' => $reference,
            'payload' => json_encode($request->all()),
            'statut' => 'pending',
            'montant' => $montant,
            'telephone_client' => $telephone,
            'webhook_received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vérifier le token Orange Money
        $token = $request->header('X-Orange-Token');
        if (!$this->verifyOrangeMoneyToken($token)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Traiter en asynchrone
        ProcessOrangeMoneyWebhook::dispatch($request->all());

        return response()->json(['success' => true], 200);
    }

    /**
     * Webhook Free Money Sénégal
     */
    public function freeMoneyWebhook(Request $request)
    {
        $reference = $request->input('reference');
        $transactionId = $request->input('transaction_id');
        $montant = $request->input('amount');
        $telephone = $request->input('customer_phone') ?? $request->input('msisdn');

        // Log le webhook reçu
        DB::table('webhook_logs')->insert([
            'provider' => 'free_money',
            'transaction_id' => $transactionId,
            'reference_externe' => $reference,
            'payload' => json_encode($request->all()),
            'statut' => 'pending',
            'montant' => $montant,
            'telephone_client' => $telephone,
            'webhook_received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Traiter le webhook Free Money
        $this->processFreeMoneyWebhook($request->all());

        return response()->json(['success' => true], 200);
    }

    /**
     * Vérifier la signature Wave
     */
    private function verifyWaveSignature($payload, $signature)
    {
        $secret = config('services.wave.webhook_secret');
        $computed = hash_hmac('sha256', json_encode($payload), $secret);
        return hash_equals($computed, $signature);
    }

    /**
     * Vérifier le token Orange Money
     */
    private function verifyOrangeMoneyToken($token)
    {
        $validToken = config('services.orange_money.webhook_token');
        return hash_equals($validToken, $token);
    }

    /**
     * Traiter un webhook Free Money (synchrone pour l'instant)
     */
    private function processFreeMoneyWebhook($data)
    {
        // Récupérer la référence du paiement
        $reference = $data['reference'] ?? null;
        if (!$reference) {
            return;
        }

        $paiement = Paiement::where('reference', $reference)->first();
        if (!$paiement) {
            return;
        }

        // Mise à jour selon le statut
        if (isset($data['status']) && $data['status'] === 'success') {
            $paiement->update([
                'statut' => 'valide',
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);

            // Envoyer SMS de confirmation
            if ($paiement->unite && $paiement->unite->telephone) {
                SendSmsNotification::dispatch(
                    $paiement->unite->telephone,
                    "Paiement reçu: {$paiement->montant} FCFA. Réf: {$paiement->reference}. Merci!",
                    'recu_paiement',
                    $paiement->commune_id
                );

                if (config('services.whatsapp.api_key')) {
                    SendWhatsAppNotification::dispatch(
                        $paiement->unite->telephone,
                        "Paiement reçu: {$paiement->montant} FCFA. Réf: {$paiement->reference}.",
                        $paiement->commune_id
                    );
                }
            }

            // Log
            DB::table('webhook_logs')
                ->where('reference_externe', $reference)
                ->update([
                    'paiement_id' => $paiement->id,
                    'statut' => 'success',
                    'processed_at' => now(),
                ]);
        }
    }
}
