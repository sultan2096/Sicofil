<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddSecurityHeaders
{
    /**
     * Ajoute les en-têtes de sécurité recommandés à chaque réponse.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ignorer les assets statiques pour ne pas surcharger
        if ($this->isStaticAsset($request)) {
            return $response;
        }

        // 1. Content-Security-Policy (CSP)
        // Politique pragmatique : sécurisée mais permissive pour une app Laravel Blade existante
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'",
            "img-src 'self' data: blob: https://*.tile.openstreetmap.org",
            "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com data:",
            "connect-src 'self' ws: wss:",
            "frame-src 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "worker-src 'self' blob:",
            "media-src 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // 2. Anti-clickjacking (doublon avec CSP frame-ancestors pour compatibilité)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Empêche le MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 4. Contrôle des informations transmises au referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Permissions Policy (anciennement Feature-Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()');

        // 6. HSTS (uniquement en HTTPS et hors local)
        if ($request->isSecure() || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        // 7. Empêche l'exposition de l'info de version
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Détermine si la requête cible un asset statique.
     */
    private function isStaticAsset(Request $request): bool
    {
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
        $path = $request->path();

        foreach ($staticExtensions as $ext) {
            if (str_ends_with($path, '.' . $ext)) {
                return true;
            }
        }

        return false;
    }
}
