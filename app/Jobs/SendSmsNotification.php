<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Job pour envoyer des SMS via API (ex: Twilio, InfoBip, ou fournisseur local)
 */
class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 600];

    protected $telephone;
    protected $message;
    protected $categorie;
    protected $communeId;

    public function __construct($telephone, $message, $categorie = 'info_generale', $communeId = null)
    {
        $this->telephone = $telephone;
        $this->message = $message;
        $this->categorie = $categorie;
        $this->communeId = $communeId;
    }

    public function handle(): void
    {
        // Créer l'entrée de notification
        $notificationId = DB::table('notifications')->insertGetId([
            'commune_id' => $this->communeId,
            'type' => 'sms',
            'categorie' => $this->categorie,
            'destinataire' => $this->telephone,
            'message' => $this->message,
            'statut' => 'en_attente',
            'tentatives' => $this->attempts(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Appel API SMS (exemple avec un fournisseur générique)
            $response = $this->sendViaSmsProvider($this->telephone, $this->message);

            // Mise à jour si succès
            DB::table('notifications')->where('id', $notificationId)->update([
                'statut' => 'envoye',
                'envoye_at' => now(),
                'reponse_api' => json_encode($response),
                'id_externe' => $response['message_id'] ?? null,
                'updated_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Mise à jour si échec
            DB::table('notifications')->where('id', $notificationId)->update([
                'statut' => 'echec',
                'erreur' => $e->getMessage(),
                'tentatives' => $this->attempts(),
                'updated_at' => now(),
            ]);

            // Relancer le job si pas encore 3 tentatives
            if ($this->attempts() < 3) {
                $this->release($this->backoff[$this->attempts() - 1]);
            }
        }
    }

    /**
     * Envoyer via fournisseur SMS
     * À adapter selon le fournisseur (Twilio, InfoBip, Orange SMS API, etc.)
     */
    private function sendViaSmsProvider($telephone, $message)
    {
        $provider = config('services.sms.provider', 'twilio');

        switch ($provider) {
            case 'twilio':
                return $this->sendViaTwilio($telephone, $message);
            case 'infobip':
                return $this->sendViaInfoBip($telephone, $message);
            case 'orange_sms':
                return $this->sendViaOrangeSMS($telephone, $message);
            default:
                // Mode développement : simuler l'envoi
                if (config('app.env') === 'local') {
                    \Log::info("SMS simulé vers {$telephone}: {$message}");
                    return ['success' => true, 'message_id' => 'SIM-' . uniqid()];
                }
                throw new \Exception('Fournisseur SMS non configuré');
        }
    }

    private function sendViaTwilio($telephone, $message)
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.phone_number');

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'To' => '+221' . $telephone,
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Erreur Twilio: ' . $response->body());
    }

    private function sendViaInfoBip($telephone, $message)
    {
        $apiKey = config('services.infobip.api_key');
        $baseUrl = config('services.infobip.base_url');

        $response = Http::withHeaders([
            'Authorization' => 'App ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$baseUrl}/sms/2/text/advanced", [
            'messages' => [
                [
                    'from' => 'SICOFIL',
                    'destinations' => [
                        ['to' => '221' . $telephone]
                    ],
                    'text' => $message
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Erreur InfoBip: ' . $response->body());
    }

    private function sendViaOrangeSMS($telephone, $message)
    {
        // API Orange SMS Sénégal
        $token = config('services.orange_sms.access_token');
        $senderAddress = config('services.orange_sms.sender_address');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post('https://api.orange.com/smsmessaging/v1/outbound/' . urlencode($senderAddress) . '/requests', [
            'outboundSMSMessageRequest' => [
                'address' => 'tel:+221' . $telephone,
                'senderAddress' => $senderAddress,
                'outboundSMSTextMessage' => [
                    'message' => $message
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Erreur Orange SMS: ' . $response->body());
    }
}
