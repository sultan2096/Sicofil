<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Cache;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip pour les assets statiques et routes de test
        if ($this->shouldSkipSecurity($request)) {
            return $next($request);
        }

        // Vérifications de sécurité globales
        $this->performGlobalSecurityChecks($request);
        
        // Protection contre les attaques par déni de service
        $this->protectAgainstDDoS($request);
        
        // Protection CSRF pour les formulaires (seulement si nécessaire)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->validateCsrfToken($request);
        }
        
        // Vérification de l'intégrité de la session (moins fréquente)
        if (rand(1, 10) === 1) { // 1 fois sur 10
            $this->validateSessionIntegrity($request);
        }

        return $next($request);
    }

    /**
     * Déterminer si on doit ignorer les vérifications de sécurité
     */
    private function shouldSkipSecurity(Request $request)
    {
        $skipPaths = [
            'css/*',
            'js/*',
            'images/*',
            'favicon.ico',
            'test-*',
            'clear-rate-limit',
        ];

        foreach ($skipPaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Effectuer des vérifications de sécurité globales
     */
    private function performGlobalSecurityChecks(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $url = $request->fullUrl();

        // Vérifier les IPs bloquées
        if ($this->isBlockedIP($ip)) {
            Log::createLog('security_violation', [
                'type' => 'blocked_ip_access_attempt',
                'ip' => $ip,
                'url' => $url,
                'user_agent' => $userAgent,
                'timestamp' => now()
            ]);
            
            abort(403, 'Accès interdit');
        }

        // Vérifier les patterns d'attaque dans l'URL
        $this->checkMaliciousURL($request);
        
        // Vérifier la taille de la requête
        $this->checkRequestSize($request);
        
        // Vérifier les headers de sécurité
        $this->validateSecurityHeaders($request);
    }

    /**
     * Vérifier si une IP est bloquée
     */
    private function isBlockedIP($ip)
    {
        // Liste des IPs suspectes (peut être étendue)
        $blockedIPs = Cache::get('blocked_ips', []);
        
        // Si l'utilisateur est connecté comme admin ou maire, être plus tolérant
        $isPrivilegedUser = auth()->check() && in_array(auth()->user()->role, ['maire', 'admin']);
        $violationThreshold = $isPrivilegedUser ? 25 : 10; // Plus tolérant pour les admins
        
        // Vérifier les tentatives récentes
        $recentViolations = Log::where('action', 'security_violation')
            ->where('details->ip', $ip)
            ->where('created_at', '>=', now()->subHours(1))
            ->count();

        if ($recentViolations >= $violationThreshold) {
            // Pour les admins, ne pas bloquer automatiquement, juste logger
            if ($isPrivilegedUser) {
                Log::createLog('security_warning', [
                    'type' => 'admin_high_activity',
                    'ip' => $ip,
                    'violations_count' => $recentViolations,
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role,
                    'timestamp' => now()
                ]);
                return false; // Ne pas bloquer les admins
            }
            
            // Bloquer temporairement l'IP pour les autres
            $blockedIPs[] = $ip;
            Cache::put('blocked_ips', array_unique($blockedIPs), now()->addHours(24));
            return true;
        }

        return in_array($ip, $blockedIPs);
    }

    /**
     * Vérifier les URLs malveillantes
     */
    private function checkMaliciousURL(Request $request)
    {
        $url = $request->fullUrl();
        $path = $request->path();
        
        $maliciousPatterns = [
            // Injections SQL
            '/union(\s|\+)+select/i',
            '/select(\s|\+)+.*from/i',
            '/drop(\s|\+)+table/i',
            '/insert(\s|\+)+into/i',
            '/delete(\s|\+)+from/i',
            '/update(\s|\+)+.*set/i',
            
            // XSS
            '/<script/i',
            '/javascript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
            
            // Directory Traversal
            '/\.\.\//',
            '/\.\.\\\\/',
            '/etc\/passwd/i',
            '/proc\/self\/environ/i',
            
            // Command Injection
            '/;(\s)*cat(\s)+/i',
            '/;(\s)*ls(\s)+/i',
            '/;(\s)*pwd(\s)*;/i',
            '/\|(\s)*cat(\s)+/i',
            
            // File Inclusion
            '/php:\/\/input/i',
            '/php:\/\/filter/i',
            '/data:\/\//i',
            
            // Autres patterns suspects
            '/eval\(/i',
            '/exec\(/i',
            '/system\(/i',
            '/base64_decode/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $url) || preg_match($pattern, $path)) {
                Log::createLog('security_violation', [
                    'type' => 'malicious_url_detected',
                    'pattern' => $pattern,
                    'url' => $url,
                    'path' => $path,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()
                ]);

                abort(400, 'Requête malveillante détectée');
            }
        }
    }

    /**
     * Vérifier la taille de la requête
     */
    private function checkRequestSize(Request $request)
    {
        $contentLength = $request->header('Content-Length', 0);
        $maxSize = 10 * 1024 * 1024; // 10MB

        if ($contentLength > $maxSize) {
            Log::createLog('security_warning', [
                'type' => 'large_request_detected',
                'content_length' => $contentLength,
                'max_allowed' => $maxSize,
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            abort(413, 'Requête trop volumineuse');
        }
    }

    /**
     * Valider les headers de sécurité
     */
    private function validateSecurityHeaders(Request $request)
    {
        // Vérifier les headers suspects
        $suspiciousHeaders = [
            'X-Forwarded-Host',
            'X-Host',
            'X-Forwarded-Server',
        ];

        foreach ($suspiciousHeaders as $header) {
            if ($request->hasHeader($header)) {
                $value = $request->header($header);
                
                // Vérifier si la valeur contient des patterns suspects
                if (preg_match('/[<>"\'\(\)]/i', $value)) {
                    Log::createLog('security_violation', [
                        'type' => 'malicious_header_detected',
                        'header' => $header,
                        'value' => $value,
                        'ip' => $request->ip(),
                        'timestamp' => now()
                    ]);

                    abort(400, 'Header malveillant détecté');
                }
            }
        }
    }

    /**
     * Valider le token CSRF
     */
    private function validateCsrfToken(Request $request)
    {
        // Vérifier uniquement pour les requêtes POST, PUT, PATCH, DELETE
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->header('X-CSRF-TOKEN') ?: $request->input('_token');
            
            if (!$token) {
                Log::createLog('security_violation', [
                    'type' => 'missing_csrf_token',
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'timestamp' => now()
                ]);
            }
        }
    }

    /**
     * Valider l'intégrité de la session
     */
    private function validateSessionIntegrity(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $sessionIP = $request->session()->get('login_ip');
            $currentIP = $request->ip();

            // Vérifier si l'IP a changé (détection de session hijacking)
            if ($sessionIP && $sessionIP !== $currentIP) {
                Log::createLog('security_violation', [
                    'type' => 'session_hijacking_suspected',
                    'user_id' => $user->id,
                    'session_ip' => $sessionIP,
                    'current_ip' => $currentIP,
                    'timestamp' => now()
                ]);

                // Déconnecter l'utilisateur par sécurité
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(403, 'Violation de sécurité détectée. Veuillez vous reconnecter.');
            }
        }
    }

    /**
     * Protection contre les attaques DDoS
     */
    private function protectAgainstDDoS(Request $request)
    {
        $ip = $request->ip();
        $key = 'ddos_protection:' . $ip;
        
        // Limites différentes selon l'authentification
        $maxRequests = auth()->check() ? 200 : 60; // 200 pour les utilisateurs connectés, 60 pour les autres
        
        // Compter les requêtes par minute
        $requests = Cache::get($key, 0);
        
        if ($requests >= $maxRequests) {
            Log::createLog('security_violation', [
                'type' => 'ddos_attempt_detected',
                'ip' => $ip,
                'requests_count' => $requests,
                'max_allowed' => $maxRequests,
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            abort(429, 'Trop de requêtes. Veuillez patienter.');
        }

        // Incrémenter le compteur
        Cache::put($key, $requests + 1, now()->addMinute());
    }
} 