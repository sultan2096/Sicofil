<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Log as SystemLog;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role - Peut accepter plusieurs rôles séparés par des virgules
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Accès non autorisé. Veuillez vous connecter.');
        }

        $user = Auth::user();
        
        // Supporter plusieurs rôles (paramètres middleware) + compat comma-separated en fallback
        // Ex: role:maire,gestionnaire => ["maire", "gestionnaire"]
        // Ex: role:"maire,gestionnaire" => ["maire,gestionnaire"] (on re-split)
        $allowedRolesLower = [];
        foreach ($roles as $rolePart) {
            foreach (explode(',', (string) $rolePart) as $r) {
                $r = strtolower(trim($r));
                if ($r !== '') {
                    $allowedRolesLower[] = $r;
                }
            }
        }
        $allowedRolesLower = array_values(array_unique($allowedRolesLower));
        
        $userRole = strtolower(trim((string) ($user->role ?? '')));
        
        if (!in_array($userRole, $allowedRolesLower)) {
            abort(403, 'Accès refusé pour ce rôle.');
        }

        return $next($request);
    }

    /**
     * Effectuer des vérifications de sécurité supplémentaires
     */
    private function performSecurityChecks(Request $request, $user)
    {
        // Vérifier les tentatives de force brute
        $this->checkBruteForce($request, $user);
        
        // Vérifier les patterns d'attaque dans l'URL
        $this->checkMaliciousPatterns($request, $user);
        
        // Vérifier la cohérence de l'User-Agent
        $this->checkUserAgentConsistency($request, $user);
    }

    /**
     * Vérifier les tentatives de force brute
     */
    private function checkBruteForce(Request $request, $user)
    {
        $ip = $request->ip();
        $recentAttempts = SystemLog::where('action', 'security_violation')
            ->where('details->ip', $ip)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentAttempts >= 5) {
            SystemLog::createLog('security_violation', [
                'type' => 'brute_force_detected',
                'ip' => $ip,
                'user_id' => $user->id,
                'attempts_count' => $recentAttempts,
                'timestamp' => now()
            ]);

            // Bloquer temporairement
            throw new \Exception('Trop de tentatives suspectes détectées. Accès temporairement bloqué.');
        }
    }

    /**
     * Vérifier les patterns malveillants dans l'URL
     */
    private function checkMaliciousPatterns(Request $request, $user)
    {
        $url = $request->fullUrl();
        $maliciousPatterns = [
            '/\.\./i',           // Directory traversal
            '/union\s+select/i', // SQL injection
            '/<script/i',        // XSS
            '/javascript:/i',    // XSS
            '/eval\(/i',         // Code injection
            '/exec\(/i',         // Command injection
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                SystemLog::createLog('security_violation', [
                    'type' => 'malicious_pattern_detected',
                    'pattern' => $pattern,
                    'url' => $url,
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                    'timestamp' => now()
                ]);

                throw new \Exception('Pattern malveillant détecté dans la requête.');
            }
        }
    }

    /**
     * Vérifier la cohérence de l'User-Agent
     */
    private function checkUserAgentConsistency(Request $request, $user)
    {
        $currentUserAgent = $request->userAgent();
        $lastUserAgent = $request->session()->get('last_user_agent');

        if ($lastUserAgent && $lastUserAgent !== $currentUserAgent) {
            SystemLog::createLog('security_warning', [
                'type' => 'user_agent_change',
                'user_id' => $user->id,
                'old_user_agent' => $lastUserAgent,
                'new_user_agent' => $currentUserAgent,
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);
        }

        $request->session()->put('last_user_agent', $currentUserAgent);
    }

    /**
     * Vérifier la validité de la session
     */
    private function isValidSession(Request $request, $user)
    {
        // Vérifier l'âge de la session
        $sessionStart = $request->session()->get('login_time');
        if (!$sessionStart) {
            $request->session()->put('login_time', now());
            return true;
        }

        // Session expire après 8 heures d'inactivité
        if (now()->diffInHours($sessionStart) > 8) {
            SystemLog::createLog('session_expired', [
                'user_id' => $user->id,
                'session_duration' => now()->diffInHours($sessionStart),
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);
            return false;
        }

        return true;
    }
} 