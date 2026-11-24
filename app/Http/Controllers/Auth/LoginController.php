<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Commune;
use App\Models\User;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendSmsNotification;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Permettre l'accès aux pages de login même si connecté
        $this->middleware('guest')->except([
            'logout', 'showLoginForm',
            'showMaireLogin', 'showAdminLogin', 'showCollecteurLogin', 'showAdminSicofilLogin',
            'showForgotPassword', 'submitForgotPassword',
            'loginMaire', 'loginAdmin', 'loginCollecteur', 'loginAdminSicofil',
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'identifiant';
    }

    /**
     * Show the application's login form.
     * Redirige vers la page d'accueil pour choisir l'interface (maire, gestionnaire, collecteur, admin SICOFIL).
     */
    public function showLoginForm()
    {
        return redirect()->route('welcome');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        // En local, ne pas appliquer les contrôles de sécurité pour éviter tout blocage
        if (!app()->environment('local')) {
            try {
                $this->performSecurityValidation($request);
            } catch (\Illuminate\Validation\ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Login security check skipped: ' . $e->getMessage());
            }
        }

        $request->validate([
            $this->username() => 'required|string|max:50|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:6|max:100',
        ], [
            'identifiant.required' => 'L\'identifiant est obligatoire.',
            'identifiant.regex' => 'L\'identifiant contient des caractères non autorisés.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);
    }

    /**
     * Effectuer des validations de sécurité avancées
     */
    private function performSecurityValidation(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Vérifier le rate limiting
        $this->checkRateLimit($request);

        // Vérifier les patterns malveillants dans les données
        $this->checkMaliciousInput($request);

        // Vérifier l'User-Agent
        $this->validateUserAgent($userAgent, $ip);

        // Vérifier les headers suspects
        $this->checkSuspiciousHeaders($request);
    }

    /**
     * Vérifier le rate limiting
     */
    private function checkRateLimit(Request $request)
    {
        $key = 'login.' . $request->ip();
        $maxAttempts = app()->environment('local') ? 30 : 5;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            try {
                Log::createLog('security_violation', [
                    'type' => 'rate_limit_exceeded',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'attempts' => RateLimiter::attempts($key),
                    'timestamp' => now()->toDateTimeString()
                ]);
            } catch (\Throwable $e) {
            }
            throw ValidationException::withMessages([
                'identifiant' => 'Trop de tentatives de connexion. Réessayez dans ' . $seconds . ' secondes.',
            ]);
        }
    }

    /**
     * Vérifier les entrées malveillantes
     */
    private function checkMaliciousInput(Request $request)
    {
        $inputs = $request->only(['identifiant', 'password']);
        $maliciousPatterns = [
            '/union\s+select/i',
            '/<script/i',
            '/javascript:/i',
            '/eval\(/i',
            '/exec\(/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
        ];

        foreach ($inputs as $field => $value) {
            foreach ($maliciousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    try {
                        Log::createLog('security_violation', [
                            'type' => 'malicious_input_detected',
                            'field' => $field,
                            'pattern' => $pattern,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'timestamp' => now()->toDateTimeString()
                        ]);
                    } catch (\Throwable $e) {
                    }
                    throw ValidationException::withMessages([
                        $field => 'Données non valides détectées.',
                    ]);
                }
            }
        }
    }

    /**
     * Valider l'User-Agent
     */
    private function validateUserAgent($userAgent, $ip)
    {
        // Vérifier si l'User-Agent est suspect
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scanner/i',
            '/curl/i',
            '/wget/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                try {
                    Log::createLog('security_warning', [
                        'type' => 'suspicious_user_agent',
                        'user_agent' => $userAgent,
                        'ip' => $ip,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } catch (\Throwable $e) {
                }
                break;
            }
        }
    }

    /**
     * Vérifier les headers suspects
     */
    private function checkSuspiciousHeaders(Request $request)
    {
        $suspiciousHeaders = [
            'X-Forwarded-For',
            'X-Real-IP',
            'X-Originating-IP',
        ];

        foreach ($suspiciousHeaders as $header) {
            if ($request->hasHeader($header)) {
                try {
                    Log::createLog('security_info', [
                        'type' => 'proxy_detected',
                        'header' => $header,
                        'value' => $request->header($header),
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } catch (\Throwable $e) {
                }
            }
        }
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if (RateLimiter::tooManyAttempts('login.' . $ip, 5)) {
            $seconds = RateLimiter::availableIn('login.' . $ip);
            RateLimiter::hit('login.' . $ip, 300);
            throw ValidationException::withMessages([
                'identifiant' => 'Trop de tentatives de connexion. Réessayez dans ' . $seconds . ' secondes.',
            ]);
        }

        // Récupérer l'utilisateur sans global scopes pour contourner le filtrage par commune
        // (sinon si un admin est connecté dans un autre onglet avec un scope actif,
        // les comptes d'une autre commune ne sont pas trouvés)
        $user = User::withoutGlobalScopes()->where('identifiant', $credentials['identifiant'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            RateLimiter::clear('login.' . $ip);
            $this->guard()->login($user, $request->filled('remember'));
            return true;
        }

        RateLimiter::hit('login.' . $ip, 300);
        return false;
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Enregistrer des informations de sécurité dans la session
        $request->session()->put([
            'login_time' => now(),
            'login_ip' => $request->ip(),
            'last_user_agent' => $request->userAgent(),
            'security_token' => Hash::make($user->id . $request->ip() . now()),
        ]);

        if ($user->mfa_enabled) {
            $otp = $user->generateMfaOtp();
            $request->session()->put('mfa_user_id', $user->id);
            $request->session()->put('mfa_role', $user->role);

            $this->sendMfaNotification($user, $otp);

            $this->guard()->logout();

            return redirect()->route('mfa.verify.form');
        }

        // Enregistrer le log de connexion réussie (temporairement désactivé)
        // Log::createLog('login_success', [
        //     'user_id' => $user->id,
        //     'identifiant' => $user->identifiant,
        //     'role' => $user->role,
        //     'ip' => $request->ip(),
        //     'user_agent' => $request->userAgent(),
        //     'timestamp' => now()
        // ]);

        return $this->redirectByRole($user);
    }

    public function showMfaForm(Request $request)
    {
        if (!$request->session()->has('mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa-verify');
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('mfa_user_id');
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->isValidMfaOtp($request->otp)) {
            return back()->withErrors(['otp' => 'Code incorrect ou expiré.']);
        }

        $user->clearMfaOtp();
        $request->session()->forget(['mfa_user_id', 'mfa_role']);

        $this->guard()->login($user);

        return $this->redirectByRole($user);
    }

    private function redirectByRole($user)
    {
        $role = strtolower((string) $user->role);
        switch ($role) {
            case 'maire':
                return redirect()->route('maire.dashboard');
            case 'gestionnaire':
                return redirect()->route('gestionnaire.dashboard');
            case 'collecteur':
                return redirect()->route('collecteur.dashboard');
            case 'admin_sicofil':
                return redirect()->route('admin.sicofil.dashboard');
            default:
                return redirect('/');
        }
    }

    private function sendMfaNotification($user, string $otp): void
    {
        $message = "SICOFIL - Code OTP: {$otp}. Valide 10 minutes.";

        if (!empty($user->telephone)) {
            SendSmsNotification::dispatch($user->telephone, $message, 'info_generale', $user->commune_id);
            return;
        }

        DB::table('notifications')->insert([
            'commune_id' => $user->commune_id,
            'user_id' => $user->id,
            'type' => 'email',
            'categorie' => 'info_generale',
            'destinataire' => $user->email,
            'objet' => 'Code OTP SICOFIL',
            'message' => $message,
            'statut' => 'en_attente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Enregistrer le log de déconnexion (temporairement désactivé)
        // if (auth()->check()) {
        //     Log::createLog('logout', [
        //         'user_id' => auth()->user()->id,
        //         'identifiant' => auth()->user()->identifiant,
        //         'role' => auth()->user()->role,
        //         'session_duration' => $request->session()->get('login_time') ? 
        //             now()->diffInMinutes($request->session()->get('login_time')) : 0,
        //         'ip' => $request->ip(),
        //         'timestamp' => now()
        //     ]);
        // }

        $user = auth()->user();
        $role = $user ? strtolower((string) $user->role) : null;
        $communeCode = $user?->commune?->code;

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $query = $communeCode ? ['commune' => $communeCode] : [];
        if ($role === 'maire') {
            return redirect()->route('maire.login', $query)->with('success', 'Vous avez été déconnecté avec succès.');
        }
        if ($role === 'gestionnaire') {
            return redirect()->route('gestionnaire.login', $query)->with('success', 'Vous avez été déconnecté avec succès.');
        }
        if ($role === 'collecteur') {
            return redirect()->route('collecteur.login', $query)->with('success', 'Vous avez été déconnecté avec succès.');
        }
        if ($role === 'admin_sicofil') {
            return redirect('/multi-communes/login')->with('success', 'Vous avez été déconnecté avec succès.');
        }
        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Résoudre la commune depuis la requête (?commune=CODE ou id) pour personnaliser la page de connexion.
     */
    private function resolveCommuneForLogin(Request $request): ?Commune
    {
        $code = $request->query('commune');
        if (empty($code)) {
            return null;
        }
        return Commune::where('actif', true)
            ->where(function ($q) use ($code) {
                $q->where('code', $code)
                    ->orWhereRaw('LOWER(code) = ?', [strtolower($code)]);
                if (is_numeric($code)) {
                    $q->orWhere('id', (int) $code);
                }
            })
            ->first();
    }

    /**
     * Page « Mot de passe oublié ». Si ?commune=CODE est présent, affiche le logo et le contexte de la commune
     * et le lien Retour renvoie vers la connexion de cette commune.
     */
    public function showForgotPassword(Request $request)
    {
        $commune = $this->resolveCommuneForLogin($request);
        $backUrl = $commune
            ? url('/maire/login?' . http_build_query(['commune' => $commune->code ?? $commune->id]))
            : route('login');
        return view('auth.forgot-password', compact('commune', 'backUrl'));
    }

    /**
     * Enregistrer une demande « mot de passe oublié ». La demande est loguée pour qu'un administrateur
     * puisse réinitialiser le mot de passe (Gestion des utilisateurs > Modifier l'utilisateur).
     */
    public function submitForgotPassword(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string|max:100',
        ], [
            'identifiant.required' => 'Veuillez saisir votre identifiant.',
        ]);

        $user = User::withoutGlobalScopes()
            ->where('identifiant', $request->identifiant)
            ->first();

        if ($user) {
            Log::create([
                'user_id' => $user->id,
                'commune_id' => $user->commune_id,
                'action' => 'password_reset_request',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => [
                    'identifiant' => $request->identifiant,
                    'requested_at' => now()->toIso8601String(),
                ],
                'commentaire' => 'Demande de réinitialisation du mot de passe depuis la page « Mot de passe oublié ».',
            ]);
        }
        // Ne pas révéler si l'identifiant existe ou non (sécurité)

        return back()->with('success', 'Votre demande a été enregistrée. Vous ne pouvez pas vous connecter tant qu’un administrateur n’a pas réinitialisé votre mot de passe. Contactez le gestionnaire de votre commune ou l’administrateur SICOFIL. Utilisez le lien « Retour » ci-dessous pour revenir à la page de connexion.');
    }

    /**
     * Afficher le formulaire de connexion pour le maire
     */
    public function showMaireLogin(Request $request)
    {
        $commune = $this->resolveCommuneForLogin($request);
        return view('auth.maire-login', compact('commune'));
    }

    /**
     * Traiter la connexion du maire
     */
    public function loginMaire(Request $request)
    {

        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est bien un maire
            if (strtolower((string) $user->role) !== 'maire') {
                auth()->logout();
                try {
                    Log::createLog('security_violation', [
                        'type' => 'role_mismatch',
                        'expected_role' => 'maire',
                        'actual_role' => $user->role,
                        'user_id' => $user->id,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ], null, null, $user->id);
                } catch (\Throwable $e) {
                    // Ne pas bloquer la réponse
                }
                return back()->withErrors([
                    'identifiant' => 'Ces identifiants ne correspondent pas à un compte maire.',
                ]);
            }

            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            return redirect()->route('maire.dashboard');
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Afficher le formulaire de connexion pour l'admin/gestionnaire
     */
    public function showAdminLogin(Request $request)
    {
        $commune = $this->resolveCommuneForLogin($request);
        return view('auth.admin-login', compact('commune'));
    }

    /**
     * Traiter la connexion de l'admin/gestionnaire
     */
    public function loginAdmin(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est bien un gestionnaire
            if (strtolower((string) $user->role) !== 'gestionnaire') {
                auth()->logout();
                try {
                    Log::createLog('security_violation', [
                        'type' => 'role_mismatch',
                        'expected_role' => 'gestionnaire',
                        'actual_role' => $user->role,
                        'user_id' => $user->id,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ], null, null, $user->id);
                } catch (\Throwable $e) {
                }
                return back()->withErrors([
                    'identifiant' => 'Ces identifiants ne correspondent pas à un compte gestionnaire.',
                ]);
            }

            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            return redirect()->route('gestionnaire.dashboard');
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Afficher le formulaire de connexion pour le collecteur
     */
    public function showCollecteurLogin(Request $request)
    {
        $commune = $this->resolveCommuneForLogin($request);
        return view('auth.collecteur-login', compact('commune'));
    }

    /**
     * Traiter la connexion du collecteur
     */
    public function loginCollecteur(Request $request)
    {

        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est bien un collecteur
            if (strtolower((string) $user->role) !== 'collecteur') {
                auth()->logout();
                try {
                    Log::createLog('security_violation', [
                        'type' => 'role_mismatch',
                        'expected_role' => 'collecteur',
                        'actual_role' => $user->role,
                        'user_id' => $user->id,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ], null, null, $user->id);
                } catch (\Throwable $e) {
                }
                return back()->withErrors([
                    'identifiant' => 'Ces identifiants ne correspondent pas à un compte collecteur.',
                ]);
            }

            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            return redirect()->route('collecteur.dashboard');
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Afficher le formulaire de connexion Gestion multi-communes (admin SICOFIL)
     */
    public function showAdminSicofilLogin()
    {
        return view('auth.admin-sicofil-login');
    }

    /**
     * Traiter la connexion admin SICOFIL (gestion multi-communes)
     */
    public function loginAdminSicofil(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = auth()->user();
            if (strtolower((string) $user->role) !== 'admin_sicofil') {
                auth()->logout();
                try {
                    Log::createLog('security_violation', [
                        'type' => 'role_mismatch',
                        'expected_role' => 'admin_sicofil',
                        'actual_role' => $user->role,
                        'user_id' => $user->id,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ], null, null, $user->id);
                } catch (\Throwable $e) {
                }
                return back()->withErrors([
                    'identifiant' => 'Identifiant ou mot de passe incorrect.',
                ]);
            }
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            return redirect()->route('sicofil.home');
        }
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Message d'erreur en français en cas d'échec de connexion
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'identifiant' => ['Identifiant ou mot de passe incorrect.'],
        ]);
    }
} 