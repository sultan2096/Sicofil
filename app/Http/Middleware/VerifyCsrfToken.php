<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Les formulaires de connexion sont exclus pour éviter 419 (session/cookie sur localhost vs 127.0.0.1).
     *
     * @var array<int, string>
     */
    protected $except = [
        'maire/login',
        'gestionnaire/login',
        'admin/login',
        'collecteur/login',
        'multi-communes/login',
        'admin-sicofil/login',
    ];
}
