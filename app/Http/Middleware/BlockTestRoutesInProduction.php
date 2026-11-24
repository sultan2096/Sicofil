<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche l'accès aux routes de test/debug en production
 */
class BlockTestRoutesInProduction
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $isTestRoute = str_starts_with($path, 'test') || str_starts_with($path, 'debug');

        if ($isTestRoute && !in_array(app()->environment(), ['local', 'development', 'testing'])) {
            abort(404);
        }

        return $next($request);
    }
}
