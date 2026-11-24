<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\User;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Envoie une notification par email au maire et gestionnaires
     * quand un nouveau paiement est enregistré
     */
    public static function notifyNewPaiement(Paiement $paiement)
    {
        try {
            // Récupérer les destinataires (maire et gestionnaires)
            $recipients = User::whereIn('role', ['maire', 'gestionnaire'])
                ->where('email', '!=', '')
                ->get();

            if ($recipients->isEmpty()) {
                return false;
            }

            // Préparer les données pour l'email
            $data = [
                'paiement' => $paiement,
                'unite' => $paiement->unite,
                'collecteur' => $paiement->collecteur,
                'date_notification' => Carbon::now()->format('d/m/Y H:i'),
                'montant_formatted' => number_format($paiement->montant, 0, ',', ' ') . ' FCFA'
            ];

            // Envoyer l'email à chaque destinataire
            foreach ($recipients as $recipient) {
                Mail::send('emails.new-paiement', $data, function($message) use ($recipient, $data) {
                    $message->to($recipient->email, $recipient->name)
                            ->subject('Nouveau Paiement Enregistré - SICOSA Sandiara');
                });
            }

            // Enregistrer le log de notification
            Log::create([
                'action' => 'notification_email_sent',
                'description' => 'Notification de nouveau paiement envoyée aux administrateurs',
                'user_id' => Auth::id(),
                'details' => json_encode([
                    'paiement_id' => $paiement->id,
                    'recipients_count' => $recipients->count(),
                    'recipients' => $recipients->pluck('email')->toArray()
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            // Enregistrer l'erreur dans les logs
            Log::create([
                'action' => 'notification_email_error',
                'description' => 'Erreur lors de l\'envoi de notification par email',
                'user_id' => Auth::id(),
                'details' => json_encode([
                    'error' => $e->getMessage(),
                    'paiement_id' => $paiement->id ?? null
                ])
            ]);

            return false;
        }
    }

    /**
     * Envoie une notification quand un collecteur se connecte
     */
    public static function notifyCollecteurLogin(User $collecteur)
    {
        try {
            // Récupérer les destinataires (maire et gestionnaires)
            $recipients = User::whereIn('role', ['maire', 'gestionnaire'])
                ->where('email', '!=', '')
                ->get();

            if ($recipients->isEmpty()) {
                return false;
            }

            // Préparer les données pour l'email
            $data = [
                'collecteur' => $collecteur,
                'date_connexion' => Carbon::now()->format('d/m/Y H:i'),
                'zone' => $collecteur->zone
            ];

            // Envoyer l'email à chaque destinataire
            foreach ($recipients as $recipient) {
                Mail::send('emails.collecteur-login', $data, function($message) use ($recipient, $data) {
                    $message->to($recipient->email, $recipient->name)
                            ->subject('Connexion Collecteur - SICOSA Sandiara');
                });
            }

            // Enregistrer le log de notification
            Log::create([
                'action' => 'collecteur_login_notification',
                'description' => 'Notification de connexion collecteur envoyée aux administrateurs',
                'user_id' => $collecteur->id,
                'details' => json_encode([
                    'collecteur_id' => $collecteur->id,
                    'recipients_count' => $recipients->count(),
                    'recipients' => $recipients->pluck('email')->toArray()
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            // Enregistrer l'erreur dans les logs
            Log::create([
                'action' => 'collecteur_login_notification_error',
                'description' => 'Erreur lors de l\'envoi de notification de connexion collecteur',
                'user_id' => $collecteur->id,
                'details' => json_encode([
                    'error' => $e->getMessage(),
                    'collecteur_id' => $collecteur->id
                ])
            ]);

            return false;
        }
    }

    /**
     * Affiche la page de gestion des notifications
     */
    public function index()
    {
        // Vérifier les permissions
        if (!in_array(auth()->user()->role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupérer les logs de notifications
        $notificationLogs = Log::where('action', 'like', '%notification%')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notificationLogs'));
    }

    /**
     * Teste l'envoi d'une notification
     */
    public function test(Request $request)
    {
        // Vérifier les permissions
        if (!in_array(auth()->user()->role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès non autorisé.');
        }

        try {
            // Créer un paiement de test
            $testPaiement = new Paiement([
                'montant' => 10000,
                'date_paiement' => Carbon::now(),
                'mode_paiement' => 'cash',
                'statut' => 'valide'
            ]);

            // Envoyer la notification de test
            $result = self::notifyNewPaiement($testPaiement);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification de test envoyée avec succès'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification de test'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}

