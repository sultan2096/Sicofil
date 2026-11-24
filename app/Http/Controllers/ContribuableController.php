<?php

namespace App\Http\Controllers;

use App\Models\Contribuable;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Jobs\SendSmsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur Portail Contribuable
 * Permet aux commerçants de consulter leurs taxes et payer en ligne
 */
class ContribuableController extends Controller
{
    /**
     * Page d'accueil du portail contribuable
     */
    public function index()
    {
        return view('contribuable.index');
    }

    /**
     * Formulaire de connexion/inscription par téléphone
     */
    public function showLogin()
    {
        return view('contribuable.login');
    }

    /**
     * Envoyer un code OTP pour connexion
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string|min:9',
        ], [
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.min' => 'Le numéro doit contenir au moins 9 chiffres.',
        ]);

        $telephone = preg_replace('/[^0-9]/', '', $request->telephone);

        if (!preg_match('/^(77|78|76|70|75)[0-9]{7}$/', $telephone)) {
            return response()->json([
                'success' => false,
                'message' => 'Format invalide. Ex: 77 123 45 67',
            ], 422);
        }

        // Commune par défaut (Sandiara ou première disponible)
        $defaultCommune = \App\Models\Commune::where('actif', true)->first();
        if (!$defaultCommune) {
            return response()->json(['success' => false, 'message' => 'Aucune commune configurée.'], 500);
        }

        // 1. Chercher un contribuable existant
        $contribuable = Contribuable::where('telephone', $telephone)->first();

        // 2. Si pas de contribuable, chercher une unité commerciale avec ce numéro
        if (!$contribuable) {
            $unite = UniteCommerciale::where('telephone', $telephone)->first();

            if (!$unite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nous n\'avons aucun contribuable enregistré avec ce numéro.',
                ], 422);
            }

            // Créer automatiquement le compte contribuable lié à l'unité
            $contribuable = Contribuable::create([
                'commune_id'          => $unite->commune_id ?? $defaultCommune->id,
                'unite_commerciale_id'=> $unite->id,
                'telephone'           => $telephone,
                'nom_complet'         => $unite->nom_complet_proprietaire ?: ('Commerçant ' . substr($telephone, -4)),
                'password'            => Hash::make(bin2hex(random_bytes(16))),
                'actif'               => false,
            ]);
        }

        // Générer le code OTP
        $otpCode = $contribuable->generateOtp();

        // Envoyer par SMS (job asynchrone) - catégorie info_generale (compatible table notifications)
        SendSmsNotification::dispatch(
            $telephone,
            "SICOFIL - Code de connexion: {$otpCode}. Valide 10 minutes.",
            'info_generale',
            $contribuable->commune_id ?? $defaultCommune->id
        );

        $response = [
            'success' => true,
            'message' => 'Code envoyé par SMS au ' . $telephone,
        ];

        // En local/sandbox sans SMS configuré : retourner l'OTP pour faciliter les tests
        if (app()->environment('local') && empty(config('services.twilio.account_sid')) && empty(config('services.infobip.api_key'))) {
            $response['otp_dev'] = $otpCode;
            $response['message'] .= ' (Mode dev: utilisez le code ci-dessus)';
        }

        return response()->json($response);
    }

    /**
     * Vérifier le code OTP et connecter le contribuable
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $telephone = preg_replace('/[^0-9]/', '', $request->telephone);
        $contribuable = Contribuable::where('telephone', $telephone)->first();

        if (!$contribuable) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Numéro non trouvé.'], 422);
            }
            return back()->withErrors(['otp' => 'Numéro non trouvé.']);
        }

        if (!$contribuable->isValidOtp($request->otp)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Code incorrect ou expiré.'], 422);
            }
            return back()->withErrors(['otp' => 'Code incorrect ou expiré.']);
        }

        // Marquer comme vérifié et connecter
        $contribuable->markAsVerified();
        $contribuable->actif = true;
        $contribuable->last_login_at = now();
        $contribuable->last_login_ip = $request->ip();
        $contribuable->save();

        Auth::guard('contribuable')->login($contribuable);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('contribuable.dashboard'),
            ]);
        }

        return redirect()->route('contribuable.dashboard');
    }

    /**
     * Dashboard du contribuable
     */
    public function dashboard()
    {
        $contribuable = Auth::guard('contribuable')->user();

        // Récupérer l'unité commerciale liée
        $unite = $contribuable->uniteCommerciale;

        // Historique des paiements
        $paiements = $contribuable->paiements()
            ->with(['unite', 'collecteur'])
            ->orderByDesc('date_paiement')
            ->paginate(10);

        // Calcul du montant dû
        $montantDu = 0;
        if ($unite && $unite->taxe) {
            $dernierPaiement = $paiements->first();
            $derniereMiseAJour = $dernierPaiement ? $dernierPaiement->date_paiement : $unite->created_at;
            
            // Calculer les mois écoulés
            $moisEcoules = now()->diffInMonths($derniereMiseAJour);
            $montantDu = $unite->taxe->montant * $moisEcoules;
        }

        return view('contribuable.dashboard', compact('contribuable', 'unite', 'paiements', 'montantDu'));
    }

    /**
     * Page de paiement (formulaire similaire au collecteur, sans avance)
     */
    public function paymentForm()
    {
        $contribuable = Auth::guard('contribuable')->user();
        $unite = $contribuable->uniteCommerciale;

        if (!$unite) {
            return redirect()->route('contribuable.dashboard')
                ->withErrors(['error' => 'Aucune unité commerciale liée. Associez votre commerce d\'abord.']);
        }

        // Taxes quotidiennes pour la commune du contribuable
        $taxes = \App\Models\Taxe::where('commune_id', $contribuable->commune_id)
            ->where('periodicite', 'jour')
            ->orderBy('nom')
            ->get();

        // Centre de carte (coordonnées de la commune)
        $commune = $contribuable->commune;
        $mapCenter = $commune && $commune->latitude && $commune->longitude
            ? [$commune->latitude, $commune->longitude]
            : [14.43540, -16.79153];

        // Historique des paiements du contribuable
        $historique = $contribuable->paiements()
            ->with(['unite', 'collecteur'])
            ->orderByDesc('date_paiement')
            ->paginate(10, ['*'], 'historique_page');

        return view('contribuable.paiement', compact('contribuable', 'unite', 'taxes', 'historique', 'mapCenter'));
    }

    /**
     * Enregistrer un paiement contribuable (journalier, sans avance)
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'taxe_id' => 'required|exists:taxes,id',
            'montant' => 'required|numeric|min:0',
            'date_paiement' => 'required|date|before_or_equal:today',
            'mode_paiement' => 'required|in:cash,wave,orange_money',
            'reference_transaction' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:500',
            // Géolocalisation
            'payment_latitude' => 'nullable|numeric|between:-90,90',
            'payment_longitude' => 'nullable|numeric|between:-180,180',
            'payment_accuracy' => 'nullable|numeric|min:0',
        ]);

        $contribuable = Auth::guard('contribuable')->user();
        $unite = $contribuable->uniteCommerciale;

        if (!$unite) {
            return back()->withErrors(['error' => 'Aucune unité commerciale liée à votre compte.']);
        }

        $taxe = \App\Models\Taxe::find($request->taxe_id);

        // Vérifier que le montant correspond à la taxe
        if ($taxe && abs($request->montant - $taxe->montant) > 0.01) {
            return back()->withErrors([
                'montant' => "Le montant ne correspond pas à la taxe. Montant attendu : " . number_format($taxe->montant, 0, ',', ' ') . " FCFA"
            ])->withInput();
        }

        $paiementData = [
            'commune_id' => $contribuable->commune_id,
            'contribuable_id' => $contribuable->id,
            'unite_id' => $unite->id,
            'taxe_id' => $request->taxe_id,
            'montant' => $request->montant,
            'montant_avance' => 0,
            'montant_restant' => 0,
            'date_paiement' => $request->date_paiement,
            'mode_paiement' => $request->mode_paiement,
            'reference_transaction' => $request->reference_transaction,
            'commentaire' => $request->commentaire,
            'statut' => 'valide',
            'reference' => 'REF-C-' . strtoupper(uniqid()),
        ];

        // Ajouter la géolocalisation si fournie
        if ($request->filled('payment_latitude') && $request->filled('payment_longitude')) {
            $paiementData['payment_latitude'] = $request->payment_latitude;
            $paiementData['payment_longitude'] = $request->payment_longitude;
            $paiementData['payment_accuracy'] = $request->payment_accuracy ?? 0;
            $paiementData['payment_method'] = 'GPS';
        }

        $paiement = Paiement::create($paiementData);

        // Log (contribuable n'est pas un User → user_id = null)
        \App\Models\Log::create([
            'user_id'       => null,
            'commune_id'    => $contribuable->commune_id,
            'action'        => 'payment_contribuable',
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'details'       => [
                'paiement_id'     => $paiement->id,
                'contribuable_id' => $contribuable->id,
                'unite_id'        => $unite->id,
                'montant'         => $paiement->montant,
                'mode'            => $paiement->mode_paiement,
            ],
            'commentaire'   => null,
        ]);

        return redirect()->route('contribuable.paiement')
            ->with('success', 'Paiement enregistré avec succès.')
            ->with('paiement_id', $paiement->id)
            ->with('show_ticket_option', true);
    }

    /**
     * Initier un paiement en ligne
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:100',
            'mode_paiement' => 'required|in:wave,orange_money,free_money',
        ]);

        $contribuable = Auth::guard('contribuable')->user();
        $unite = $contribuable->uniteCommerciale;

        if (!$unite) {
            return back()->withErrors(['error' => 'Aucune unité commerciale liée à votre compte.']);
        }

        // Créer un paiement en attente
        $paiement = Paiement::create([
            'commune_id' => $contribuable->commune_id,
            'unite_id' => $unite->id,
            'contribuable_id' => $contribuable->id,
            'montant' => $request->montant,
            'mode_paiement' => $request->mode_paiement,
            'statut' => 'en_attente_validation',
            'date_paiement' => now(),
            'reference' => 'REF-' . strtoupper(uniqid()),
            'commentaire' => 'Paiement en ligne initie par contribuable',
        ]);

        // Appeler l'API du provider (Wave/Orange Money)
        $paymentUrl = $this->initiateProviderPayment($paiement, $request->mode_paiement, $contribuable->telephone);

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
            'reference' => $paiement->reference,
        ]);
    }

    /**
     * Initier le paiement chez le provider (Wave, Orange Money)
     */
    private function initiateProviderPayment($paiement, $provider, $telephone)
    {
        switch ($provider) {
            case 'wave':
                return $this->initiateWavePayment($paiement, $telephone);
            case 'orange_money':
                return $this->initiateOrangeMoneyPayment($paiement, $telephone);
            case 'free_money':
                return $this->initiateFreeMoneyPayment($paiement, $telephone);
        }

        return null;
    }

    private function initiateWavePayment($paiement, $telephone)
    {
        $apiKey = config('services.wave.api_key');
        $apiSecret = config('services.wave.api_secret');
        $baseUrl = rtrim(config('services.wave.base_url'), '/');
        $mode = config('services.wave.mode', 'sandbox');

        if (empty($apiKey) || empty($apiSecret)) {
            Log::warning('Wave non configuré: clés manquantes.');
            return $this->fallbackPaymentUrl($paiement, 'wave');
        }

        $payload = [
            'amount' => (int) $paiement->montant,
            'currency' => 'XOF',
            'client_reference' => $paiement->reference,
            'customer_phone' => $telephone,
            'success_url' => $this->paymentReturnUrl($paiement, 'success'),
            'error_url' => $this->paymentReturnUrl($paiement, 'failed'),
            'metadata' => [
                'paiement_id' => $paiement->id,
                'commune_id' => $paiement->commune_id,
                'mode' => 'wave',
            ],
        ];

        try {
            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->acceptJson()
                ->post($baseUrl . '/v1/checkout/sessions', $payload);

            if ($response->failed()) {
                Log::warning('Wave init failed', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackPaymentUrl($paiement, 'wave');
            }

            $data = $response->json();
            $paymentUrl = $data['checkout_url'] ?? $data['payment_url'] ?? $data['url'] ?? null;
            $transactionId = $data['id'] ?? $data['transaction_id'] ?? null;

            if ($transactionId) {
                $paiement->update(['transaction_id' => $transactionId]);
            }

            return $paymentUrl ?: $this->fallbackPaymentUrl($paiement, 'wave');
        } catch (\Throwable $e) {
            Log::error('Wave init exception', ['error' => $e->getMessage()]);
            return $this->fallbackPaymentUrl($paiement, 'wave', $mode === 'sandbox');
        }
    }

    private function initiateOrangeMoneyPayment($paiement, $telephone)
    {
        $clientId = config('services.orange_money.client_id');
        $clientSecret = config('services.orange_money.client_secret');
        $merchantCode = config('services.orange_money.merchant_code');
        $baseUrl = rtrim(config('services.orange_money.base_url'), '/');

        if (empty($clientId) || empty($clientSecret) || empty($merchantCode)) {
            Log::warning('Orange Money non configuré: clés manquantes.');
            return $this->fallbackPaymentUrl($paiement, 'orange_money');
        }

        $payload = [
            'amount' => (int) $paiement->montant,
            'reference' => $paiement->reference,
            'customer_phone' => $telephone,
            'merchant_code' => $merchantCode,
            'success_url' => $this->paymentReturnUrl($paiement, 'success'),
            'cancel_url' => $this->paymentReturnUrl($paiement, 'cancel'),
            'description' => 'Paiement SICOFIL',
        ];

        try {
            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->acceptJson()
                ->post($baseUrl . '/payment', $payload);

            if ($response->failed()) {
                Log::warning('Orange Money init failed', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackPaymentUrl($paiement, 'orange_money');
            }

            $data = $response->json();
            $paymentUrl = $data['payment_url'] ?? $data['url'] ?? null;
            $transactionId = $data['transaction_id'] ?? null;

            if ($transactionId) {
                $paiement->update(['transaction_id' => $transactionId]);
            }

            return $paymentUrl ?: $this->fallbackPaymentUrl($paiement, 'orange_money');
        } catch (\Throwable $e) {
            Log::error('Orange Money init exception', ['error' => $e->getMessage()]);
            return $this->fallbackPaymentUrl($paiement, 'orange_money');
        }
    }

    private function initiateFreeMoneyPayment($paiement, $telephone)
    {
        $apiKey = config('services.free_money.api_key');
        $merchantId = config('services.free_money.merchant_id');
        $baseUrl = rtrim(config('services.free_money.base_url'), '/');

        if (empty($apiKey) || empty($merchantId)) {
            Log::warning('Free Money non configuré: clés manquantes.');
            return $this->fallbackPaymentUrl($paiement, 'free_money');
        }

        $payload = [
            'amount' => (int) $paiement->montant,
            'reference' => $paiement->reference,
            'customer_phone' => $telephone,
            'merchant_id' => $merchantId,
            'success_url' => $this->paymentReturnUrl($paiement, 'success'),
            'cancel_url' => $this->paymentReturnUrl($paiement, 'cancel'),
        ];

        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->acceptJson()
                ->post($baseUrl . '/payment', $payload);

            if ($response->failed()) {
                Log::warning('Free Money init failed', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallbackPaymentUrl($paiement, 'free_money');
            }

            $data = $response->json();
            $paymentUrl = $data['payment_url'] ?? $data['url'] ?? null;
            $transactionId = $data['transaction_id'] ?? null;

            if ($transactionId) {
                $paiement->update(['transaction_id' => $transactionId]);
            }

            return $paymentUrl ?: $this->fallbackPaymentUrl($paiement, 'free_money');
        } catch (\Throwable $e) {
            Log::error('Free Money init exception', ['error' => $e->getMessage()]);
            return $this->fallbackPaymentUrl($paiement, 'free_money');
        }
    }

    private function paymentReturnUrl($paiement, $status)
    {
        return url('/contribuable/dashboard') . '?payment=' . $paiement->reference . '&status=' . $status;
    }

    private function fallbackPaymentUrl($paiement, $provider, $sandbox = true)
    {
        $paiement->update([
            'commentaire' => 'Paiement initie (' . $provider . ')' . ($sandbox ? ' - mode test' : ''),
        ]);

        return url('/contribuable/dashboard') . '?payment=' . $paiement->reference . '&provider=' . $provider . '&status=pending';
    }

    /**
     * Télécharger le reçu (PDF / impression)
     */
    public function receipt(Paiement $paiement)
    {
        $contribuable = Auth::guard('contribuable')->user();
        if (!$contribuable || $paiement->contribuable_id !== $contribuable->id) {
            abort(403, 'Accès non autorisé.');
        }

        $paiement->load(['unite.zone', 'collecteur.zone']);
        $qrData = $this->generateQRData($paiement);

        return view('paiements.ticket', compact('paiement', 'qrData'));
    }

    /**
     * Contacter la mairie (support / réclamation)
     */
    public function contactMairie(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $contribuable = Auth::guard('contribuable')->user();

        DB::table('notifications')->insert([
            'commune_id' => $contribuable?->commune_id,
            'contribuable_id' => $contribuable?->id,
            'type' => 'email',
            'categorie' => 'info_generale',
            'destinataire' => 'mairie',
            'objet' => 'Message portail contribuable',
            'message' => $request->message,
            'statut' => 'en_attente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Votre message a bien été envoyé.');
    }

    private function generateQRData(Paiement $paiement)
    {
        $data = [
            'type' => 'SICOFIL_RECU',
            'id' => str_pad($paiement->id, 6, '0', STR_PAD_LEFT),
            'date' => $paiement->date_paiement?->format('d/m/Y H:i'),
            'montant' => $paiement->montant,
            'mode' => $paiement->mode_paiement,
            'unite' => $paiement->unite->nom_complet_proprietaire ?? 'N/A',
            'zone' => $paiement->unite->zone->nom ?? 'N/A',
            'collecteur' => $paiement->collecteur->name ?? 'En ligne',
            'reference' => $paiement->reference ?? $paiement->reference_transaction ?? 'N/A',
            'verification' => url('/paiements/' . $paiement->id . '/verify'),
        ];

        return json_encode($data);
    }

    /**
     * Associer une unité commerciale au compte contribuable
     */
    public function associateUnite(Request $request)
    {
        $request->validate([
            'ninea' => 'nullable|string|max:50',
            'numero_immatriculation' => 'nullable|string|max:50',
            'telephone_commerce' => 'nullable|string|max:20',
        ]);

        $contribuable = Auth::guard('contribuable')->user();

        $query = UniteCommerciale::query()
            ->where('commune_id', $contribuable->commune_id);

        if ($request->filled('ninea')) {
            $query->where('ninea', $request->ninea);
        } elseif ($request->filled('numero_immatriculation')) {
            $query->where('numero_immatriculation', $request->numero_immatriculation);
        } elseif ($request->filled('telephone_commerce')) {
            $query->where('telephone', $request->telephone_commerce);
        } else {
            return back()->withErrors(['search' => 'Veuillez renseigner au moins un critère de recherche.']);
        }

        $unite = $query->first();

        if (!$unite) {
            return back()->withErrors(['search' => 'Aucune unité commerciale trouvée avec ces informations. Contactez la mairie.']);
        }

        // Vérifier que l'unité n'est pas déjà liée à un autre contribuable
        $alreadyLinked = Contribuable::where('unite_commerciale_id', $unite->id)
            ->where('id', '!=', $contribuable->id)
            ->exists();

        if ($alreadyLinked) {
            return back()->withErrors(['search' => 'Cette unité commerciale est déjà associée à un autre compte.']);
        }

        $contribuable->unite_commerciale_id = $unite->id;
        $contribuable->save();

        return back()->with('success', "Unité commerciale '{$unite->nom_commercial}' associée avec succès.");
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        Auth::guard('contribuable')->logout();
        return redirect()->route('contribuable.login');
    }
}
