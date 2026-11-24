<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 600];

    public function __construct(
        protected string $telephone,
        protected string $message,
        protected ?int $communeId = null
    ) {
    }

    public function handle(): void
    {
        $notificationId = DB::table('notifications')->insertGetId([
            'commune_id' => $this->communeId,
            'type' => 'whatsapp',
            'categorie' => 'info_generale',
            'destinataire' => $this->telephone,
            'message' => $this->message,
            'statut' => 'en_attente',
            'tentatives' => $this->attempts(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = $this->sendViaWhatsAppProvider($this->telephone, $this->message);

            DB::table('notifications')->where('id', $notificationId)->update([
                'statut' => 'envoye',
                'envoye_at' => now(),
                'reponse_api' => json_encode($response),
                'id_externe' => $response['messages'][0]['id'] ?? null,
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            DB::table('notifications')->where('id', $notificationId)->update([
                'statut' => 'echec',
                'erreur' => $e->getMessage(),
                'tentatives' => $this->attempts(),
                'updated_at' => now(),
            ]);

            if ($this->attempts() < 3) {
                $this->release($this->backoff[$this->attempts() - 1]);
            }
        }
    }

    private function sendViaWhatsAppProvider(string $telephone, string $message): array
    {
        $apiKey = config('services.whatsapp.api_key');
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        if (empty($apiKey) || empty($phoneNumberId)) {
            if (config('app.env') === 'local') {
                \Log::info("WhatsApp simulé vers {$telephone}: {$message}");
                return ['messages' => [['id' => 'SIM-' . uniqid()]]];
            }
            throw new \Exception('Fournisseur WhatsApp non configuré');
        }

        $response = Http::withToken($apiKey)->post(
            "https://graph.facebook.com/v17.0/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to' => '221' . $telephone,
                'type' => 'text',
                'text' => ['body' => $message],
            ]
        );

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Erreur WhatsApp: ' . $response->body());
    }
}
