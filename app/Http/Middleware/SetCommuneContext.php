<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour gérer le contexte communal
 * - Permet à l'admin SICOFIL de changer de commune
 * - Stocke la commune active en session
 */
class SetCommuneContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $role = strtolower((string) $user->role);

        // Si l'admin SICOFIL change de commune via paramètre GET
        if ($role === 'admin_sicofil' && $request->has('commune_id')) {
            $communeId = $request->get('commune_id');
            
            // Stocker en session (les deux clés pour cohérence avec AppliesCommuneScope)
            session([
                'active_commune_id' => $communeId,
                'commune_scope_id' => $communeId,
            ]);
            
            // Rediriger pour nettoyer l'URL
            if ($request->method() === 'GET') {
                return redirect($request->url());
            }
        }

        // Définir la commune active
        if ($role === 'admin_sicofil') {
            $activeCommuneId = session('active_commune_id');
            $request->merge(['active_commune_id' => $activeCommuneId]);
        } else {
            $request->merge(['active_commune_id' => $user->commune_id]);
        }

        return $next($request);
    }
}
