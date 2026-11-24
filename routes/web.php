<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\UniteCommercialeController;
use App\Http\Controllers\ZoneFiscaleController;
use App\Http\Controllers\TaxeController;
use App\Http\Controllers\TypeCommerceController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\CommerceParametersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoMapController;
use App\Http\Controllers\GeomarketingController;
use App\Http\Controllers\PostGISController;
use Illuminate\Http\Request;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/sicofil-access', [LoginController::class, 'showAdminSicofilLogin'])->name('sicofil.access');
Route::post('/sicofil-access', [LoginController::class, 'loginAdminSicofil'])->name('sicofil.access.post');
Route::get('/sicofil-home', [\App\Http\Controllers\AdminSicofilController::class, 'dashboard'])
    ->middleware(['auth', 'role:admin_sicofil'])
    ->name('sicofil.home');


// Redirection /home vers dashboard (pour compatibilité Laravel)
Route::get('/home', function () {
    if (auth()->check()) {
        $role = strtolower((string) auth()->user()->role);
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
                return redirect()->route('dashboard');
        }
    }
    return redirect('/');
});

// Page d'accueil - Redirection intelligente selon l'utilisateur
Route::get('/', function () {
    if (auth()->check()) {
        // Si l'utilisateur est connecté, rediriger vers son dashboard
        $role = strtolower((string) auth()->user()->role);
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
                return redirect()->route('dashboard');
        }
    }
    
    // Si l'utilisateur n'est pas connecté, afficher uniquement l'interface SICOFIL (page d'accueil)
    return view('welcome');
})->name('welcome');

// Accéder par commune : liste publique des communes (lien vers portail de chaque commune)
Route::get('/acceder-par-commune', [CommuneController::class, 'listForPublic'])->name('communes.list-public');
// Connexion par commune : page avec logo de la commune et liens distincts (Maire, Gestionnaire, Collecteur)
Route::get('/c/{code}', [CommuneController::class, 'portal'])->name('commune.portal');
// Logo des communes : route publique pour afficher les logos sur les pages non connectées (acceder-par-commune, portail, login maire)
Route::get('/communes/{commune}/logo', [CommuneController::class, 'logo'])->name('communes.logo');

// Endpoint de santé (monitoring simple)
Route::get('/health', [HealthController::class, 'index'])->name('health');

// Test de connexion (local uniquement) — pour débloquer si la connexion ne marche pas
Route::get('/test-connexion', function () {
    if (!app()->environment('local')) {
        abort(404);
    }
    $users = \App\Models\User::withoutGlobalScopes()->get(['id', 'identifiant', 'name', 'role']);
    return view('auth.test-connexion', compact('users'));
})->name('test.connexion');
Route::post('/test-connexion', function (Request $request) {
    if (!app()->environment('local')) {
        abort(404);
    }
    $identifiant = $request->input('identifiant');
    $password = $request->input('password');
    $user = \App\Models\User::withoutGlobalScopes()->where('identifiant', $identifiant)->first();
    if (!$user) {
        return back()->with('error', "Aucun utilisateur avec l'identifiant « {$identifiant} ». Lancez: php artisan db:seed --class=UserSeeder");
    }
    if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        return back()->with('error', 'Mot de passe incorrect pour « ' . $identifiant . ' ». Ex: sicosa2025, gestionnaire2025, collecteur2025, sicofil2025');
    }
    auth()->login($user);
    $request->session()->regenerate();
    $redirect = match (strtolower((string) $user->role)) {
        'maire' => route('maire.dashboard'),
        'gestionnaire' => route('gestionnaire.dashboard'),
        'collecteur' => route('collecteur.dashboard'),
        'admin_sicofil' => route('admin.sicofil.dashboard'),
        default => '/dashboard',
    };
    return redirect($redirect)->with('success', 'Connexion réussie (test).');
})->name('test.connexion.post');

// Routes d'authentification avec protection de sécurité
Route::middleware(['web'])->group(function () {
    // Pages de connexion
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // MFA OTP
    Route::get('/mfa/verify', [LoginController::class, 'showMfaForm'])->name('mfa.verify.form');
    Route::post('/mfa/verify', [LoginController::class, 'verifyMfa'])->name('mfa.verify');

    // Mot de passe oublié (optionnel : ?commune=CODE pour afficher le contexte commune et le bon lien Retour)
    Route::get('/mot-de-passe-oublie', [LoginController::class, 'showForgotPassword'])->name('password.forgot');
    Route::post('/mot-de-passe-oublie', [LoginController::class, 'submitForgotPassword'])->name('password.forgot.submit');
    
    // Route GET pour logout (redirection sécurisée) — commune : rediriger vers la page de connexion de la commune (pas l'accueil)
    Route::get('/logout', function () {
        if (!auth()->check()) {
            return redirect('/');
        }
        
        $user = auth()->user();
        $role = $user->role;
        $communeCode = $user->commune?->code;
        
        // Déconnexion de l'utilisateur
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        $query = $communeCode ? ['commune' => $communeCode] : [];
        $roleLower = strtolower((string) $role);
        switch ($roleLower) {
            case 'maire':
                return redirect()->route('maire.login', $query)->with('success', 'Vous avez été déconnecté avec succès.');
            case 'gestionnaire':
                return redirect()->route('gestionnaire.login', $query)->with('success', 'Vous avez été déconnecté avec succès.');
            case 'collecteur':
                return redirect()->route('collecteur.login', $query)->with('success', 'Vous avez été déconnecté avec succès.');
            case 'admin_sicofil':
                return redirect('/multi-communes/login')->with('success', 'Vous avez été déconnecté avec succès.');
            default:
                return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
        }
    });

    // Connexions spécifiques par rôle
    Route::get('/maire/login', [LoginController::class, 'showMaireLogin'])->name('maire.login');
    Route::post('/maire/login', [LoginController::class, 'loginMaire'])->name('maire.login.post');

    Route::get('/gestionnaire/login', [LoginController::class, 'showAdminLogin'])->name('gestionnaire.login');
    Route::post('/gestionnaire/login', [LoginController::class, 'loginAdmin'])->name('gestionnaire.login.post');
    // Alias historique pour éviter les 404 sur /admin/login
    Route::get('/admin/login', function () {
        return redirect()->route('gestionnaire.login');
    })->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'loginAdmin'])->name('admin.login.post');

    Route::get('/collecteur/login', [LoginController::class, 'showCollecteurLogin'])->name('collecteur.login');
    Route::post('/collecteur/login', [LoginController::class, 'loginCollecteur'])->name('collecteur.login.post');

    Route::get('/multi-communes/login', [LoginController::class, 'showAdminSicofilLogin'])->name('multi-communes.login');
    Route::post('/multi-communes/login', [LoginController::class, 'loginAdminSicofil'])->name('multi-communes.login.post');

    // Alias admin-sicofil/login -> multi-communes/login (éviter 404)
    Route::get('/admin-sicofil/login', function () {
        return redirect()->route('multi-communes.login');
    })->name('admin.sicofil.login');
    Route::post('/admin-sicofil/login', [LoginController::class, 'loginAdminSicofil'])->name('admin.sicofil.login.post');

    // Mot de passe oublié (déclaré une seule fois plus haut)
});

// Route de test simple sans middleware
Route::get('/debug-comptes', function () {
    $users = \App\Models\User::all();
    $html = '<h1>Comptes SICOSA</h1><ul>';
    foreach ($users as $user) {
        $html .= '<li><strong>' . $user->identifiant . '</strong> (' . $user->role . ') - ' . $user->name . '</li>';
    }
    $html .= '</ul>';
    $html .= '<br><a href="/gestionnaire/login">Connexion Gestionnaire</a>';
    return response($html, 200, ['Content-Type' => 'text/html']);
});

Route::get('/multi-communes/login', [LoginController::class, 'showAdminSicofilLogin'])->name('multi-communes.login.direct');
Route::post('/multi-communes/login', [LoginController::class, 'loginAdminSicofil'])->name('multi-communes.login.post.direct');
Route::get('/admin-sicofil/login', [LoginController::class, 'showAdminSicofilLogin'])->name('admin.sicofil.login.direct');
Route::post('/admin-sicofil/login', [LoginController::class, 'loginAdminSicofil'])->name('admin.sicofil.login.post.direct');
Route::get('/admin-sicofil/dashboard', [\App\Http\Controllers\AdminSicofilController::class, 'dashboard'])
    ->middleware(['auth', 'role:admin_sicofil'])
    ->name('admin.sicofil.dashboard.direct');
Route::get('/sicofil-login', [LoginController::class, 'showAdminSicofilLogin'])->name('sicofil.login');
Route::post('/sicofil-login', [LoginController::class, 'loginAdminSicofil'])->name('sicofil.login.post');
Route::get('/sicofil-dashboard', [\App\Http\Controllers\AdminSicofilController::class, 'dashboard'])
    ->middleware(['auth', 'role:admin_sicofil'])
    ->name('sicofil.dashboard');

// Route de diagnostic SICOSA
Route::get('/diagnostic', function () {
    return view('diagnostic');
})->name('diagnostic')->middleware('auth');

// Routes protégées avec authentification
Route::middleware(['auth'])->group(function () {
    
    // Route de test simple
    Route::get('/test-simple', function () {
        return '<h1>Test réussi!</h1><p>Utilisateur: ' . auth()->user()->name . '</p><p>Rôle: ' . auth()->user()->role . '</p><a href="/maire/dashboard">Retour dashboard</a>';
    });
    
         // Route pour optimiser les performances
    Route::get('/optimize-performance', function () {
        $results = [];
        
        try {
            // Nettoyer les caches
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            $results[] = '✅ Cache applicatif nettoyé';
            
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            $results[] = '✅ Vues compilées nettoyées';
            
            \Illuminate\Support\Facades\Artisan::call('route:cache');
            $results[] = '✅ Routes optimisées';
            
            \Illuminate\Support\Facades\Artisan::call('config:cache');
            $results[] = '✅ Configuration optimisée';
            
            $html = '<h1>🚀 OPTIMISATION RÉUSSIE</h1>';
            $html .= '<div style="background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;">';
            foreach ($results as $result) {
                $html .= '<p>' . $result . '</p>';
            }
            $html .= '</div>';
            $html .= '<p><strong>Les pages devraient maintenant se charger plus rapidement !</strong></p>';
            
        } catch (\Exception $e) {
            $html = '<h1>❌ ERREUR</h1><p>' . $e->getMessage() . '</p>';
        }
        
        $html .= '<br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔙 Retour dashboard</a>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });

    // Route pour nettoyer le cache de protection DDoS
    Route::get('/clear-rate-limit', function () {
        $ip = request()->ip();
        $key = 'ddos_protection:' . $ip;
        
        if (\Illuminate\Support\Facades\Cache::has($key)) {
            \Illuminate\Support\Facades\Cache::forget($key);
            return '<h1>✅ Cache nettoyé</h1><p>Les limitations de taux pour votre IP (' . $ip . ') ont été supprimées.</p><a href="/dashboard">Retour dashboard</a>';
        } else {
            return '<h1>ℹ️ Aucune limitation</h1><p>Aucune limitation de taux active pour votre IP (' . $ip . ').</p><a href="/dashboard">Retour dashboard</a>';
        }
    });

    // Route de test pour la création d'utilisateurs
    Route::get('/test-create-user', function () {
        $user = auth()->user();
        $html = '<h1>🧪 TEST CRÉATION UTILISATEURS</h1>';
        $html .= '<p><strong>Connecté en tant que:</strong> ' . $user->name . ' (' . $user->role . ')</p>';
        
        $html .= '<h2>Permissions de création:</h2>';
        $html .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">';
        
        if ($user->role === 'maire') {
            $html .= '<h3>✅ MAIRE - Peut créer :</h3>';
            $html .= '<li>✅ Administrateurs</li>';
            $html .= '<li>✅ Collecteurs</li>';
            $html .= '<li><a href="/users/create" style="background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Créer un utilisateur</a></li>';
        } elseif ($user->role === 'gestionnaire') {
            $html .= '<h3>✅ GESTIONNAIRE - Peut créer :</h3>';
            $html .= '<li>❌ Administrateurs (Maire uniquement)</li>';
            $html .= '<li>✅ Collecteurs</li>';
            $html .= '<li><a href="/users/create" style="background: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Créer un collecteur</a></li>';
        } else {
            $html .= '<h3>❌ COLLECTEUR - Ne peut pas créer d\'utilisateurs</h3>';
        }
        
        $html .= '</div>';
        $html .= '<br><a href="/users" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">📋 Voir les utilisateurs</a>';
        $html .= ' <a href="/dashboard" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔙 Dashboard</a>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });

    // Route de test pour vérifier les permissions
    Route::get('/test-permissions', function () {
        $user = auth()->user();
        $html = '<h1>🔐 TEST PERMISSIONS</h1>';
        $html .= '<p><strong>Utilisateur:</strong> ' . $user->name . ' (' . $user->role . ')</p>';
        
        $html .= '<h2>Routes disponibles pour vous:</h2>';
        $html .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">';
        
        if ($user->role === 'maire') {
            $html .= '<h3>✅ MAIRE - Tous les droits :</h3>';
            $html .= '<li>Créer utilisateurs: <a href="/users/create">✅</a></li>';
            $html .= '<li>Supprimer utilisateurs: ✅</li>';
            $html .= '<li>Supprimer unités: ✅</li>';
            $html .= '<li>Tous les rapports: ✅</li>';
        } elseif ($user->role === 'gestionnaire') {
            $html .= '<h3>✅ GESTIONNAIRE - Droits étendus :</h3>';
            $html .= '<li>Créer utilisateurs: ❌ (Maire uniquement)</li>';
            $html .= '<li>Supprimer collecteurs: ✅</li>';
            $html .= '<li>Supprimer unités: ✅</li>';
            $html .= '<li>Voir tous les utilisateurs: <a href="/users">✅</a></li>';
        } else {
            $html .= '<h3>✅ COLLECTEUR - Droits limités :</h3>';
            $html .= '<li>Créer unités: <a href="/unites/create">✅</a></li>';
            $html .= '<li>Voir ses unités: <a href="/unites">✅</a></li>';
            $html .= '<li>Supprimer: ❌</li>';
        }
        
        $html .= '</div>';
        $html .= '<br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔙 Retour dashboard</a>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });

    // Route de test pour la géolocalisation des paiements
    Route::get('/test-payment-geolocation', function () {
        $html = '<!DOCTYPE html><html><head><title>Test Géolocalisation Paiement</title></head><body>';
        $html .= '<h1>🧪 TEST GÉOLOCALISATION PAIEMENT</h1>';
        $html .= '<p><strong>Utilisateur connecté :</strong> ' . auth()->user()->name . ' (' . auth()->user()->role . ')</p>';
        
        if (auth()->user()->role === 'collecteur') {
            $html .= '<h2>✅ COLLECTEUR - Test de géolocalisation</h2>';
            $html .= '<div style="background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;">';
            $html .= '<h3>Fonctionnalités disponibles :</h3>';
            $html .= '<ul>';
            $html .= '<li>✅ Géolocalisation obligatoire sur la page de paiement</li>';
            $html .= '<li>✅ Capture GPS automatique</li>';
            $html .= '<li>✅ Position manuelle en cas d\'échec GPS</li>';
            $html .= '<li>✅ Tracking automatique de position</li>';
            $html .= '<li>✅ Validation avant soumission du formulaire</li>';
            $html .= '</ul>';
            $html .= '</div>';
            
            $html .= '<h3>🔗 Liens de test :</h3>';
            $html .= '<ul>';
            $html .= '<li><a href="/collecteur/paiements/create" style="font-size: 18px; color: blue;">💰 Créer un paiement avec géolocalisation</a></li>';
            $html .= '<li><a href="/collecteur/paiements" style="font-size: 18px; color: blue;">📋 Voir les paiements</a></li>';
            $html .= '</ul>';
        } elseif (auth()->user()->role === 'maire') {
            $html .= '<h2>✅ MAIRE - Suivi des collecteurs</h2>';
            $html .= '<div style="background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0;">';
            $html .= '<h3>Fonctionnalités disponibles :</h3>';
            $html .= '<ul>';
            $html .= '<li>✅ Suivi en temps réel des collecteurs</li>';
            $html .= '<li>✅ Visualisation des positions GPS</li>';
            $html .= '<li>✅ Historique des itinéraires</li>';
            $html .= '<li>✅ Statuts des collecteurs (en ligne, collecte, déplacement)</li>';
            $html .= '<li>✅ Positions des paiements enregistrés</li>';
            $html .= '</ul>';
            $html .= '</div>';
            
            $html .= '<h3>🔗 Liens de test :</h3>';
            $html .= '<ul>';
            $html .= '<li><a href="/tracking/live" style="font-size: 18px; color: blue;">🗺️ Suivi en temps réel des collecteurs</a></li>';
            $html .= '<li><a href="/tracking/positions" style="font-size: 18px; color: blue;">📍 Positions des collecteurs</a></li>';
            $html .= '<li><a href="/paiements" style="font-size: 18px; color: blue;">💰 Voir tous les paiements</a></li>';
            $html .= '</ul>';
        } else {
            $html .= '<h2>❌ RÔLE NON AUTORISÉ</h2>';
            $html .= '<p>Cette fonctionnalité est réservée aux collecteurs et au maire.</p>';
        }
        
        $html .= '<hr>';
        $html .= '<h3>📊 Informations techniques :</h3>';
        $html .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">';
        $html .= '<p><strong>Migration créée :</strong> add_location_fields_to_paiements_table.php</p>';
        $html .= '<p><strong>Champs ajoutés :</strong> payment_latitude, payment_longitude, payment_accuracy, payment_method</p>';
        $html .= '<p><strong>Tracking :</strong> collector_tracking table</p>';
        $html .= '<p><strong>API :</strong> /tracking/position (POST), /tracking/positions (GET)</p>';
        $html .= '</div>';
        
        $html .= '<br><a href="/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔙 Retour Dashboard</a>';
        $html .= '</body></html>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });

    // Route de test pour créer une unité test
    Route::get('/test-create-unite', function () {
        try {
            $unite = \App\Models\UniteCommerciale::create([
                'nom_proprietaire' => 'TEST Gueyki Faye - ' . now()->format('H:i:s'),
                'nature' => 'Boutique',
                'type_commerce' => 'Alimentation générale',
                'categorie' => 'activite_commerciale',
                'adresse' => 'Sandiara Centre',
                'telephone' => '77 123 45 67',
                'latitude' => 14.7515,
                'longitude' => -16.9525,
                'zone_id' => 1, // Première zone
                'taxe_id' => 1, // Première taxe
                'actif' => true,
                'date_recensement' => now(),
                'collecteur_id' => auth()->id()
            ]);
            
            return '<h1>✅ SUCCÈS</h1><p>Unité test créée avec ID: ' . $unite->id . '</p><p>Nom: ' . $unite->nom_proprietaire . '</p><a href="/unites">Voir la liste</a>';
        } catch (\Exception $e) {
            return '<h1>❌ ERREUR</h1><p>' . $e->getMessage() . '</p><p>Stack trace: ' . $e->getTraceAsString() . '</p>';
        }
    });

    // Route de test pour les unités commerciales
    Route::get('/test-unites', function () {
        $unites = \App\Models\UniteCommerciale::with(['zone', 'taxe', 'collecteur'])->get();
        
        $html = '<h1>🏪 TEST UNITÉS COMMERCIALES</h1>';
        $html .= '<p>Total unités dans la base: <strong>' . $unites->count() . '</strong></p>';
        
        if ($unites->count() > 0) {
            $html .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
            $html .= '<tr style="background: #f8f9fa;">';
            $html .= '<th style="padding: 8px;">ID</th>';
            $html .= '<th style="padding: 8px;">Propriétaire</th>';
            $html .= '<th style="padding: 8px;">Nature</th>';
            $html .= '<th style="padding: 8px;">Zone</th>';
            $html .= '<th style="padding: 8px;">Collecteur</th>';
            $html .= '<th style="padding: 8px;">Date recensement</th>';
            $html .= '<th style="padding: 8px;">Actif</th>';
            $html .= '</tr>';
            
            foreach ($unites as $unite) {
                $html .= '<tr>';
                $html .= '<td style="padding: 8px;">' . $unite->id . '</td>';
                $html .= '<td style="padding: 8px;">' . $unite->nom_proprietaire . '</td>';
                $html .= '<td style="padding: 8px;">' . $unite->nature . '</td>';
                $html .= '<td style="padding: 8px;">' . ($unite->zone ? $unite->zone->nom : 'Aucune') . '</td>';
                $html .= '<td style="padding: 8px;">' . ($unite->collecteur ? $unite->collecteur->name : 'Aucun') . '</td>';
                $html .= '<td style="padding: 8px;">' . ($unite->date_recensement ? $unite->date_recensement->format('d/m/Y H:i') : 'Non définie') . '</td>';
                $html .= '<td style="padding: 8px;">' . ($unite->actif ? '✅ Oui' : '❌ Non') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p style="color: red;">❌ Aucune unité commerciale trouvée dans la base de données !</p>';
        }
        
        $html .= '<br><a href="/unites" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔗 Aller vers la liste des unités</a>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });

    // Route de diagnostic pour admin
    Route::get('/test-admin', function () {
        if (!auth()->check()) {
            return '<h1>❌ NON CONNECTÉ</h1><p><a href="/admin/login">Se connecter en admin</a></p>';
        }
        
        $user = auth()->user();
        $html = '<h1>✅ CONNECTÉ ADMIN</h1>';
        $html .= '<p><strong>Nom:</strong> ' . $user->name . '</p>';
        $html .= '<p><strong>Rôle:</strong> ' . $user->role . '</p>';
        $html .= '<p><strong>ID:</strong> ' . $user->id . '</p>';
        
        // Test des routes disponibles
        $routes = [
            'zones' => route('zones.index'),
            'unites' => route('unites.index'), 
            'taxes' => route('taxes.index'),
            'paiements' => route('paiements.index'),
            'users' => route('users.index'),
            'logs' => route('logs.index'),
            'map' => route('map.index')
        ];
        
        $html .= '<h2>🔗 TESTEZ CES LIENS (ils devraient fonctionner) :</h2>';
        $html .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        foreach ($routes as $name => $url) {
            $html .= '<div style="margin: 10px 0; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
            $html .= '<strong>' . ucfirst($name) . '</strong><br>';
            $html .= '<a href="' . $url . '" style="color: #007bff; text-decoration: none; font-size: 14px;">' . $url . '</a>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '<h3>🚀 Si ces liens fonctionnent, alors le problème est dans votre navigation !</h3>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });
    
    // Route pour lister les comptes
    Route::get('/test-comptes', function () {
        $users = \App\Models\User::all();
        return response('<h1>Comptes disponibles :</h1>' . 
            $users->map(function($u) { 
                return '<li><strong>' . $u->identifiant . '</strong> (' . $u->role . ') - ' . $u->name . '</li>'; 
            })->implode('') . 
            '<p><a href="/admin/login">Se connecter en admin</a></p>');
    });
    
    // Route de test navigation simple
    Route::get('/test-navigation', function () {
        if (!auth()->check()) {
            return redirect('/admin/login');
        }
        
        $html = '<!DOCTYPE html><html><head><title>Test Navigation</title></head><body>';
        $html .= '<h1>🧪 TEST DE NAVIGATION SIMPLE</h1>';
        $html .= '<p>Utilisateur connecté: <strong>' . auth()->user()->name . '</strong> (' . auth()->user()->role . ')</p>';
        $html .= '<h2>Liens directs (devraient fonctionner):</h2>';
        $html .= '<ul style="line-height: 2;">';
        $html .= '<li><a href="/zones" style="font-size: 18px; color: blue;">🗺️ Zones fiscales</a></li>';
        $html .= '<li><a href="/unites" style="font-size: 18px; color: blue;">🏪 Unités commerciales</a></li>';
        $html .= '<li><a href="/taxes" style="font-size: 18px; color: blue;">💰 Taxes</a></li>';
        $html .= '<li><a href="/paiements" style="font-size: 18px; color: blue;">💳 Paiements</a></li>';
        $html .= '<li><a href="/map" style="font-size: 18px; color: blue;">🗺️ Carte</a></li>';
        $html .= '<li><a href="/logs" style="font-size: 18px; color: blue;">📊 Logs</a></li>';
        $html .= '</ul>';
        $html .= '<p><a href="/users">🔙 Retour à la gestion des utilisateurs</a></p>';
        $html .= '</body></html>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });
    
    // Dashboard général
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes spécifiques aux dashboards
    Route::get('/maire/dashboard', [DashboardController::class, 'maire'])->name('maire.dashboard')->middleware('role:maire');
    Route::get('/maire/dashboard/live-stats', [DashboardController::class, 'maireLiveStats'])
        ->name('maire.dashboard.live')
        ->middleware('role:maire');
    Route::get('/gestionnaire/dashboard', [DashboardController::class, 'gestionnaire'])->name('gestionnaire.dashboard')->middleware('role:gestionnaire');
    Route::get('/collecteur/dashboard', [DashboardController::class, 'collecteur'])->name('collecteur.dashboard')->middleware('role:collecteur');

    // Gestion multi-communes (admin SICOFIL) — tableau de bord unique, toutes les communes
    Route::middleware(['auth', 'role:admin_sicofil'])->prefix('multi-communes')->name('communes.')->group(function () {
        Route::get('/', [CommuneController::class, 'index'])->name('index');
        Route::get('/carte', [CommuneController::class, 'map'])->name('map');
        Route::get('/communes/create', [CommuneController::class, 'create'])->name('create');
        Route::post('/communes', [CommuneController::class, 'store'])->name('store');
        Route::get('/communes/{commune}', [CommuneController::class, 'show'])->name('show');
        Route::get('/communes/{commune}/edit', [CommuneController::class, 'edit'])->name('edit');
        Route::put('/communes/{commune}', [CommuneController::class, 'update'])->name('update');
        Route::get('/communes/{commune}/users/create', [CommuneController::class, 'createUserForm'])->name('users.create');
        Route::post('/communes/{commune}/users', [CommuneController::class, 'storeUser'])->name('users.store');
        Route::delete('/communes/{commune}', [CommuneController::class, 'destroy'])->name('destroy');
        Route::post('/communes/{commune}/scope', [CommuneController::class, 'setScope'])->name('scope.set');
        Route::post('/communes/scope/clear', [CommuneController::class, 'clearScope'])->name('scope.clear');
    });

    // Toggle recensement collecteur (gestionnaire de la commune)
    Route::put('/communes/{commune}/toggle-recensement', [CommuneController::class, 'toggleRecensement'])
        ->name('communes.toggle-recensement')
        ->middleware('role:gestionnaire,admin_sicofil');

    // Toggle mise à jour collecteur (gestionnaire de la commune)
    Route::put('/communes/{commune}/toggle-mise-a-jour', [CommuneController::class, 'toggleMiseAJour'])
        ->name('communes.toggle-mise-a-jour')
        ->middleware('role:gestionnaire,admin_sicofil');

    // Redirection /carte → carte SICOFIL ou carte classique (évite 404)
    Route::get('/carte', function () {
        if (!auth()->check()) {
            return redirect('/multi-communes/login');
        }
        if (strtolower(auth()->user()->role ?? '') === 'admin_sicofil') {
            return redirect()->route('communes.map');
        }
        return redirect()->route('map.index');
    })->name('carte.redirect');

    // Route du tableau de bord Géomarketing (maire uniquement)
    Route::get('/maire/geomarketing', [GeomarketingController::class, 'index'])
        ->name('maire.geomarketing')
        ->middleware('role:maire');

    // Endpoints Géomarketing via middleware web (pour AJAX avec session)
    // URIs sans préfixe /api pour éviter toute collision avec routes/api.php
    Route::middleware(['auth'])->group(function () {
        Route::get('/geomarketing/data', [GeomarketingController::class, 'data'])->name('web.geomarketing.data');
        Route::get('/geomarketing/population', [GeomarketingController::class, 'population'])->name('web.geomarketing.population');
        Route::get('/geomarketing/roads', [GeomarketingController::class, 'roads'])->name('web.geomarketing.roads');
        Route::get('/geomarketing/bati', [GeomarketingController::class, 'bati'])->name('web.geomarketing.bati');
        Route::get('/geomarketing/recommendations', [GeomarketingController::class, 'recommendations'])->name('web.geomarketing.recommendations');
        Route::get('/geomarketing/heatmap', [GeomarketingController::class, 'heatmap'])->name('web.geomarketing.heatmap');
        Route::get('/geomarketing/combined-density', [GeomarketingController::class, 'combinedDensity'])->name('web.geomarketing.combinedDensity');
    });

    // Routes pour le gestionnaire (CRUD collecteurs uniquement - les autres routes sont communes)
    Route::middleware(['role:gestionnaire'])->group(function () {
        // Gestion des collecteurs (uniquement pour gestionnaire)
        Route::get('/collecteurs/create', [UserController::class, 'create'])->name('collecteurs.create');
        Route::post('/collecteurs', [UserController::class, 'store'])->name('collecteurs.store');
        Route::get('/collecteurs/{user}/edit', [UserController::class, 'edit'])->name('collecteurs.edit');
        Route::put('/collecteurs/{user}', [UserController::class, 'update'])->name('collecteurs.update');
        Route::delete('/collecteurs/{user}', [UserController::class, 'destroy'])->name('collecteurs.destroy');

        // Gestion des paramètres de commerce (uniquement gestionnaire)
        Route::get('/commerce-parameters', [CommerceParametersController::class, 'index'])->name('commerce-parameters.index');
        Route::post('/commerce-parameters/nature', [CommerceParametersController::class, 'storeNature'])->name('commerce-parameters.nature.store');
        Route::put('/commerce-parameters/nature/{nature}', [CommerceParametersController::class, 'updateNature'])->name('commerce-parameters.nature.update');
        Route::delete('/commerce-parameters/nature/{nature}', [CommerceParametersController::class, 'destroyNature'])->name('commerce-parameters.nature.destroy');
        Route::post('/commerce-parameters/type', [CommerceParametersController::class, 'storeType'])->name('commerce-parameters.type.store');
        Route::put('/commerce-parameters/type/{type}', [CommerceParametersController::class, 'updateType'])->name('commerce-parameters.type.update');
        Route::delete('/commerce-parameters/type/{type}', [CommerceParametersController::class, 'destroyType'])->name('commerce-parameters.type.destroy');
        Route::get('/api/commerce-filters', [CommerceParametersController::class, 'getFiltersData'])->name('api.commerce-filters');
        Route::get('/api/types-by-nature/{nature}', [CommerceParametersController::class, 'getTypesByNature'])->name('api.types-by-nature');
        
        // Note: Les routes pour finaliser les paiements partiels sont définies dans le groupe commun (ligne ~797)
        // avec contrôle d'accès dans le contrôleur pour éviter les conflits de routes
    });

    // Routes communes à tous les utilisateurs connectés
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::get('/map/import-zones', [MapController::class, 'showImportZonesForm'])->name('map.import-zones');
    Route::post('/map/import-zones', [MapController::class, 'storeImportZones'])->name('map.import-zones.store');
    Route::get('/api/map/thematic-data', [MapController::class, 'getThematicMapData'])->name('map.thematic.data');
    Route::get('/api/zones', function() {
        return response()->json(
            \App\Models\ZoneFiscale::where('commune_id', auth()->user()->commune_id)
                ->select('id', 'nom')
                ->orderBy('nom')
                ->get()
        );
    });
    Route::get('/api/types-commerce', function() {
        return response()->json(\App\Models\TypeCommerce::select('id', 'nom')->get());
    });
    Route::get('/api/unites/{id}', function($id) {
        $query = \App\Models\UniteCommerciale::with(['zone', 'taxe', 'paiements'])
            ->where('commune_id', auth()->user()->commune_id);
        if (auth()->user()->role === 'collecteur') {
            $query->where('zone_id', auth()->user()->zone_id);
        }
        $unite = $query->find($id);
        if (!$unite) {
            return response()->json(['error' => 'Unité non trouvée'], 404);
        }
        return response()->json($unite);
    });
    Route::get('/api/unites/search', function(\Illuminate\Http\Request $request) {
        $term = $request->get('q', '');
        if (strlen($term) < 2) {
            return response()->json([]);
        }
        $search = '%' . $term . '%';
        $query = \App\Models\UniteCommerciale::where('commune_id', auth()->user()->commune_id)
            ->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(prenom_proprietaire) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(telephone) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(adresse) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(type_commerce) LIKE LOWER(?)', [$search])
                  ->orWhereRaw('LOWER(nature) LIKE LOWER(?)', [$search]);
            })
            ->select('id', 'nom_proprietaire', 'prenom_proprietaire', 'telephone', 'type_commerce', 'nature', 'adresse', 'superficie', 'geometrie_polygon', 'zone_id')
            ->limit(10);
        if (auth()->user()->role === 'collecteur') {
            $query->where('zone_id', auth()->user()->zone_id);
        }
        return response()->json($query->get());
    });
    // Endpoints GeoJSON pour couches shapefiles (PostGIS)
    Route::get('/api/map/boundaries', [MapController::class, 'getBoundariesGeoJSON'])->name('map.boundaries.geojson');
    Route::get('/api/map/boundaries-status', [MapController::class, 'boundariesStatus'])->name('map.boundaries.status');
    
    // Route pour la carte thématique
    Route::get('/carte-thematique', [MapController::class, 'showThematicMap'])->name('carte.thematique');
    
    // Route pour le rapport avec carte thématique
    Route::get('/rapport-avec-carte', [RapportController::class, 'rapportAvecCarte'])->name('rapport.avec.carte');
    
    // Routes qui doivent fonctionner pour admin (déplacées du groupe problématique)
    Route::get('/zones', [ZoneFiscaleController::class, 'index'])->name('zones.index');
    Route::get('/zones/create', [ZoneFiscaleController::class, 'create'])->name('zones.create');
    Route::post('/zones', [ZoneFiscaleController::class, 'store'])->name('zones.store');
    Route::get('/zones/{zone}', [ZoneFiscaleController::class, 'show'])->name('zones.show');
    Route::get('/zones/{zone}/edit', [ZoneFiscaleController::class, 'edit'])->name('zones.edit');
    Route::put('/zones/{zone}', [ZoneFiscaleController::class, 'update'])->name('zones.update');
    Route::patch('/zones/{zone}', [ZoneFiscaleController::class, 'update']);
    Route::delete('/zones/{zone}', [ZoneFiscaleController::class, 'destroy'])->name('zones.destroy');
    
    Route::get('/unites', [UniteCommercialeController::class, 'index'])->name('unites.index');
    Route::get('/unites/export-excel', [UniteCommercialeController::class, 'exportExcel'])->name('unites.export-excel');
    Route::get('/unites/export-pdf', [UniteCommercialeController::class, 'exportPdf'])->name('unites.export-pdf');
    Route::get('/unites/create', [UniteCommercialeController::class, 'create'])->name('unites.create');
    Route::post('/unites', [UniteCommercialeController::class, 'store'])->name('unites.store');
    Route::get('/unites/{unite}', [UniteCommercialeController::class, 'show'])->name('unites.show');
    Route::get('/unites/{unite}/edit', [UniteCommercialeController::class, 'edit'])->name('unites.edit');
    Route::put('/unites/{unite}', [UniteCommercialeController::class, 'update'])->name('unites.update');
    Route::patch('/unites/{unite}', [UniteCommercialeController::class, 'update']);
    Route::delete('/unites/{unite}', [UniteCommercialeController::class, 'destroy'])->name('unites.destroy');
    
    Route::get('/taxes', [TaxeController::class, 'index'])->name('taxes.index');
    Route::get('/taxes/create', [TaxeController::class, 'create'])->name('taxes.create');
    Route::post('/taxes', [TaxeController::class, 'store'])->name('taxes.store');
    Route::get('/taxes/{taxe}', [TaxeController::class, 'show'])->name('taxes.show');
    Route::get('/taxes/{taxe}/edit', [TaxeController::class, 'edit'])->name('taxes.edit');
    Route::put('/taxes/{taxe}', [TaxeController::class, 'update'])->name('taxes.update');
    Route::patch('/taxes/{taxe}', [TaxeController::class, 'update']);
    Route::delete('/taxes/{taxe}', [TaxeController::class, 'destroy'])->name('taxes.destroy');
    
    // Routes pour les types de commerce
    Route::get('/types-commerce', [TypeCommerceController::class, 'index'])->name('types-commerce.index');
    Route::get('/types-commerce/create', [TypeCommerceController::class, 'create'])->name('types-commerce.create');
    Route::post('/types-commerce', [TypeCommerceController::class, 'store'])->name('types-commerce.store');
    Route::get('/types-commerce/{typeCommerce}', [TypeCommerceController::class, 'show'])->name('types-commerce.show');
    Route::get('/types-commerce/{typeCommerce}/edit', [TypeCommerceController::class, 'edit'])->name('types-commerce.edit');
    Route::put('/types-commerce/{typeCommerce}', [TypeCommerceController::class, 'update'])->name('types-commerce.update');
    Route::patch('/types-commerce/{typeCommerce}', [TypeCommerceController::class, 'update']);
    Route::delete('/types-commerce/{typeCommerce}', [TypeCommerceController::class, 'destroy'])->name('types-commerce.destroy');
    
    // Route GET alternative pour la suppression des types de commerce
    Route::get('/types-commerce/{id}/delete', function($id) {
        if (!auth()->check() || auth()->user()->role !== 'gestionnaire') {
            return redirect()->route('types-commerce.index')->with('error', 'Accès non autorisé. Seuls les gestionnaires peuvent supprimer des types de commerce.');
        }
        
        $typeCommerce = \App\Models\TypeCommerce::find($id);
        if (!$typeCommerce) {
            return redirect()->route('types-commerce.index')->with('error', 'Type de commerce non trouvé');
        }
        
        // Vérifier s'il y a des unités commerciales associées
        $unitesCount = \App\Models\UniteCommerciale::where('type_commerce', $typeCommerce->nom)->count();
        
        if ($unitesCount > 0) {
            return redirect()->route('types-commerce.index')
                ->with('error', "Impossible de supprimer ce type de commerce. Il est utilisé par {$unitesCount} unité(s) commerciale(s).");
        }
        
        $nom = $typeCommerce->nom;
        
        try {
            \App\Models\Log::createLog('delete_type_commerce', [
                'type_commerce_id' => $id,
                'type_commerce_nom' => $nom
            ]);
            
            $typeCommerce->delete();
            
            return redirect()->route('types-commerce.index')
                ->with('success', "Type de commerce '$nom' supprimé avec succès.");
                
        } catch (\Exception $e) {
            return redirect()->route('types-commerce.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    })->name('types-commerce.delete');
    
    // Route GET alternative pour la suppression (contournement du problème DELETE)
    Route::get('/taxes/{id}/delete', function($id) {
        if (!auth()->check() || auth()->user()->role !== 'gestionnaire') {
            return redirect()->route('taxes.index')->with('error', 'Accès non autorisé');
        }
        
        $taxe = \App\Models\Taxe::find($id);
        if (!$taxe) {
            return redirect()->route('taxes.index')->with('error', 'Taxe non trouvée');
        }
        
        $nom = $taxe->nom;
        
        try {
            // Mettre à jour les unités commerciales
            \App\Models\UniteCommerciale::where('taxe_id', $id)->update(['taxe_id' => null]);
            
            // Supprimer la taxe
            $taxe->delete();
            
            // Log
            \App\Models\Log::createLog('delete_taxe', [
                'taxe_id' => $id,
                'taxe_nom' => $nom
            ]);
            
            return redirect()->route('taxes.index')
                ->with('success', "Taxe '$nom' (ID: $id) supprimée avec succès.");
                
        } catch (\Exception $e) {
            return redirect()->route('taxes.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    })->name('taxes.delete');

    // Route GET alternative pour la suppression des utilisateurs (contournement du problème DELETE)
    Route::get('/users/{id}/delete', function($id) {
        if (!auth()->check() || auth()->user()->role !== 'gestionnaire') {
            return redirect()->route('users.index')->with('error', 'Accès non autorisé');
        }
        
        $user = \App\Models\User::find($id);
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Collecteur non trouvé');
        }
        
        if ($user->role !== 'collecteur') {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez supprimer que les collecteurs');
        }
        
        $nom = $user->name;
        
        try {
            // Détacher le collecteur des unités commerciales avant suppression
            \App\Models\UniteCommerciale::where('collecteur_id', $id)->update(['collecteur_id' => null]);
            
            // Log
            \App\Models\Log::createLog('delete_collecteur', [
                'collecteur_id' => $id,
                'collecteur_name' => $nom,
                'zone_id' => $user->zone_id
            ]);
            
            // Supprimer l'utilisateur
            $user->delete();
            
            return redirect()->route('users.index')
                ->with('success', "Collecteur '$nom' (ID: $id) supprimé avec succès.");
                
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    })->name('users.delete');

    // Route GET alternative pour la suppression des unités commerciales
    Route::get('/unites/{id}/delete', function($id) {
        if (!auth()->check() || auth()->user()->role !== 'gestionnaire') {
            return redirect()->route('unites.index')->with('error', 'Accès non autorisé');
        }
        
        $unite = \App\Models\UniteCommerciale::find($id);
        if (!$unite) {
            return redirect()->route('unites.index')->with('error', 'Unité commerciale non trouvée');
        }
        
        $nom = $unite->nom_proprietaire;
        
        try {
            // Supprimer les paiements liés avant de supprimer l'unité
            $unite->paiements()->delete();
            
            // Supprimer la photo si elle existe
            if ($unite->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($unite->photo);
            }
            
            // Log
            \App\Models\Log::createLog('delete_unite', [
                'unite_id' => $id,
                'unite_nom' => $nom,
                'type_commerce' => $unite->type_commerce,
                'zone_nom' => $unite->zone ? $unite->zone->nom : 'Aucune'
            ]);
            
            // Supprimer l'unité
            $unite->delete();
            
            return redirect()->route('unites.index')
                ->with('success', "Unité commerciale '$nom' (ID: $id) supprimée avec succès.");
                
        } catch (\Exception $e) {
            return redirect()->route('unites.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    })->name('unites.delete');

    Route::get('/paiements', [PaiementController::class, 'index'])->name('paiements.index');
    Route::get('/paiements/export-excel', [PaiementController::class, 'exportExcel'])->name('paiements.export-excel');
    Route::get('/paiements/export-pdf', [PaiementController::class, 'exportPdf'])->name('paiements.export-pdf');
    Route::get('/paiements/create', [PaiementController::class, 'create'])->name('paiements.create');
    Route::post('/paiements', [PaiementController::class, 'store'])->name('paiements.store');
    Route::get('/paiements/{paiement}', [PaiementController::class, 'show'])->name('paiements.show');
    Route::get('/paiements/{paiement}/ticket', [PaiementController::class, 'ticket'])->name('paiements.ticket');
    Route::get('/paiements/{paiement}/receipt', [PaiementController::class, 'receipt'])->name('paiements.receipt');
    Route::get('/paiements/{paiement}/verify', [PaiementController::class, 'verify'])->name('paiements.verify');
    Route::get('/paiements/{paiement}/edit', [PaiementController::class, 'edit'])->name('paiements.edit');
    Route::put('/paiements/{paiement}', [PaiementController::class, 'update'])->name('paiements.update');
    Route::patch('/paiements/{paiement}', [PaiementController::class, 'update']);
    Route::delete('/paiements/{paiement}', [PaiementController::class, 'destroy'])->name('paiements.destroy');
    
    // Routes pour finaliser les paiements partiels (accessible aux gestionnaires, collecteurs et maires)
    // Le contrôleur vérifie les permissions
    Route::get('/paiements/{paiement}/add-payment', [PaiementController::class, 'addPayment'])->name('paiements.add-payment');
    Route::post('/paiements/{paiement}/add-payment', [PaiementController::class, 'storeAdditionalPayment'])->name('paiements.store-additional');
    
    // Routes pour les alertes de retard de paiement
    Route::get('/alerts/payment-overdue', [App\Http\Controllers\PaymentAlertController::class, 'index'])->name('alerts.payment-overdue');
    Route::post('/alerts/{alert}/mark-seen', [App\Http\Controllers\PaymentAlertController::class, 'markAsSeen'])->name('alerts.mark-seen');
    
    // Route de test pour déboguer les paiements
    Route::get('/debug-paiements', function() {
        $paiements = \App\Models\Paiement::with(['unite', 'collecteur'])->take(5)->get();
        return response()->json([
            'total' => $paiements->count(),
            'paiements' => $paiements->map(function($p) {
                return [
                    'id' => $p->id,
                    'reference' => $p->reference,
                    'montant' => $p->montant,
                    'statut' => $p->statut,
                    'unite_id' => $p->unite_id,
                    'collecteur_id' => $p->collecteur_id,
                    'unite_exists' => $p->unite ? true : false,
                    'collecteur_exists' => $p->collecteur ? true : false,
                ];
            })
        ]);
    })->name('debug.paiements');
    
    // Route users accessible aux admins ET au maire (mais avec restrictions côté contrôleur)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/collecteurs', [UserController::class, 'index'])->name('collecteurs.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}', [UserController::class, 'update']);
    
    // API pour la carte
    Route::get('/api/unites-commerciales', [UniteCommercialeController::class, 'apiIndex']);
    Route::get('/api/zones-fiscales', [ZoneFiscaleController::class, 'apiIndex']);
    Route::post('/unites-commerciales', [UniteCommercialeController::class, 'apiStore']);
    Route::get('/security/status', function () {
        return response()->json([
            'status' => 'active',
            'timestamp' => now(),
            'protections' => [
                'csrf' => true,
                'rate_limiting' => true,
                'sql_injection' => true,
                'xss_protection' => true,
                'ddos_protection' => true,
            ]
        ]);
    });
    
    // Route de diagnostic middleware
    Route::get('/test-middleware', function () {
        $user = auth()->user();
        $html = '<h1>🔍 TEST MIDDLEWARE</h1>';
        $html .= '<p><strong>Utilisateur:</strong> ' . $user->name . '</p>';
        $html .= '<p><strong>Rôle:</strong> ' . $user->role . '</p>';
        
        // Test du middleware role
        $allowedRoles = array_map('trim', explode(',', 'maire,admin'));
        
        $html .= '<h2>Test middleware role:maire,admin</h2>';
        $html .= '<p>Rôles autorisés: ' . implode(', ', $allowedRoles) . '</p>';
        $html .= '<p>Votre rôle (' . $user->role . ') est autorisé: ' . (in_array($user->role, $allowedRoles) ? '✅ OUI' : '❌ NON') . '</p>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    });

    // Test route pour diagnostic
    Route::get('/test-profil-gestionnaire', function () {
        $user = auth()->user();
        if (!$user) {
            return 'Utilisateur non connecté';
        }
        
        $html = '<h1>Test Profil Gestionnaire</h1>';
        $html .= '<p><strong>Utilisateur connecté :</strong> ' . $user->name . '</p>';
        $html .= '<p><strong>Rôle :</strong> ' . $user->role . '</p>';
        $html .= '<p><strong>Identifiant :</strong> ' . $user->identifiant . '</p>';
        
        $allowedRoles = ['maire', 'gestionnaire'];
        $html .= '<p><strong>Rôles autorisés pour profil :</strong> ' . implode(', ', $allowedRoles) . '</p>';
        $html .= '<p><strong>Accès autorisé :</strong> ' . (in_array($user->role, $allowedRoles) ? '✅ OUI' : '❌ NON') . '</p>';
        
        // Test des liens
        $html .= '<hr>';
        $html .= '<h2>Tests de liens :</h2>';
        $html .= '<p><a href="' . route('profil.edit') . '" target="_blank">→ Accéder au profil (profil.edit)</a></p>';
        $html .= '<p><a href="/profil" target="_blank">→ Accéder au profil (URL directe)</a></p>';
        
        // Vérifier la route
        $html .= '<hr>';
        $html .= '<h2>Information sur la route :</h2>';
        $html .= '<p><strong>Route profil.edit existe :</strong> ' . (route('profil.edit') ? '✅ OUI' : '❌ NON') . '</p>';
        
        return response($html, 200, ['Content-Type' => 'text/html']);
    })->middleware(['auth']);

// Profil gestionnaire et maire (exclu collecteurs) - Configuration simplifiée
Route::middleware(['auth'])->group(function () {
    Route::get('/profil', function() {
        $user = auth()->user();
        
        // Vérification simple des rôles autorisés
        if (!in_array($user->role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès réservé aux maires et gestionnaires');
        }
        
        return app(App\Http\Controllers\UserController::class)->editProfile();
    })->name('profil.edit');
    
    Route::put('/profil', function() {
        $user = auth()->user();
        
        // Vérification simple des rôles autorisés
        if (!in_array($user->role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès réservé aux maires et gestionnaires');
        }
        
        return app(App\Http\Controllers\UserController::class)->updateProfile(request());
    })->name('profil.update');
});

// Route de test pour le nouveau formulaire avec filtres
Route::get('/test-formulaire-commerce', function() {
    return view('unites.create-with-filters');
})->middleware(['auth']);



// Routes pour le gestionnaire (CRUD complet) - SUPPRIMÉ car déjà déclaré plus haut
    // Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    //     ->middleware('role:gestionnaire');
});

// Suivi des collecteurs et Rapports : maire ET gestionnaire (éviter 403)
Route::middleware(['auth', 'role:maire,gestionnaire'])->group(function () {
    Route::get('/suivi-collecteurs', function() {
        $collectors = \App\Models\User::where('role', 'collecteur')->with('zone')->get();
        return view('tracking.live', compact('collectors'));
    })->name('suivi.collecteurs');
    Route::get('/tracking/live', [TrackingController::class, 'liveTracking'])->name('tracking.live');
    Route::get('/tracking/positions', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/tracking/collector/{collectorId}', [TrackingController::class, 'show'])->name('tracking.show');

    // Rapports - maire et gestionnaire
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [RapportController::class, 'index'])->name('index');
        Route::get('/financier', [RapportController::class, 'financier'])->name('financier');
        Route::get('/financier-pdf', [RapportController::class, 'financierPdf'])->name('financier.pdf');
        Route::get('/financier-moderne', [RapportController::class, 'financierModerne'])->name('financier.moderne');
        Route::get('/collecteurs', [RapportController::class, 'collecteurs'])->name('collecteurs');
        Route::get('/unites', [RapportController::class, 'unites'])->name('unites');
        Route::get('/formulaire', [RapportController::class, 'formulaireRapport'])->name('formulaire');
        Route::post('/rapport-financier-pdf', [RapportController::class, 'rapportFinancier'])->name('rapport.financier.pdf');
        Route::get('/financier-camembert', [RapportController::class, 'financierCamembert'])->name('financier.camembert');
        Route::get('/synthese', [RapportController::class, 'synthese'])->name('synthese');
        Route::get('/zones', [RapportController::class, 'zones'])->name('zones');
        Route::get('/taxes', [RapportController::class, 'paiements'])->name('taxes');
        Route::get('/mensuel', [RapportController::class, 'mensuel'])->name('mensuel');
        Route::get('/comparatif', [RapportController::class, 'comparatif'])->name('comparatif');
        Route::get('/nature-commerce', [RapportController::class, 'natureCommerce'])->name('nature-commerce');
        Route::get('/type-commerce', [RapportController::class, 'typeCommerce'])->name('type-commerce');
        Route::get('/nature-type-commerce', [RapportController::class, 'natureTypeCommerce'])->name('nature-type-commerce');
        Route::get('/carte', [RapportController::class, 'carteThematique'])->name('carte');
        Route::get('/geomarketing', [RapportController::class, 'geomarketing'])->name('geomarketing');
    });
});

// Routes EXCLUSIVES au MAIRE
Route::middleware(['auth', 'role:maire'])->group(function () {
    
    // Le maire n'a plus accès aux suppressions et modifications d'utilisateurs
    // Seuls les gestionnaires peuvent modifier/supprimer
    
    // Actions supplémentaires réservées au maire (SUPPRIMÉES)
    // Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Routes alternatives pour les autres onglets problématiques
    Route::get('/rapports-maire', function() {
        if (!auth()->check() || auth()->user()->role !== 'maire') {
            abort(403, 'Accès non autorisé');
        }
        
        return app(\App\Http\Controllers\RapportController::class)->index();
    })->name('rapports.maire');
    
    Route::get('/carte-maire', function() {
        if (!auth()->check()) {
            abort(403, 'Accès non autorisé');
        }
        
        return app(\App\Http\Controllers\MapController::class)->index();
    })->name('carte.maire');
    
    Route::get('/logs-maire', function(Request $request) {
        if (!auth()->check()) {
            abort(403, 'Accès non autorisé');
        }
        
        return app(\App\Http\Controllers\LogController::class)->index($request);
    })->name('logs.maire');

    // Sécurité - Accès complet
    Route::get('/security/dashboard', [SecurityController::class, 'dashboard'])->name('security.dashboard');
    Route::post('/security/unblock-ip', [SecurityController::class, 'unblockIP'])->name('security.unblock-ip');
    Route::post('/security/block-ip', [SecurityController::class, 'blockIP'])->name('security.block-ip');
    Route::get('/security/stats', [SecurityController::class, 'getDetailedStats'])->name('security.stats');
    Route::post('/security/cleanup', [SecurityController::class, 'cleanupLogs'])->name('security.cleanup');
});

// Routes EXCLUSIVES aux ADMINS
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Routes de suppression pour admin (avec restrictions dans le contrôleur)
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::delete('/admin/unites/{unite}', [UniteCommercialeController::class, 'destroy'])->name('admin.unites.destroy');

    // Note: Les routes pour finaliser les paiements partiels sont définies dans les groupes middleware spécifiques
    // (gestionnaire, collecteur, maire) pour éviter les conflits de routes
});

// Routes pour les COLLECTEURS - Accès limité à leur zone
Route::middleware(['auth', 'role:collecteur'])->group(function () {
    // Routes spécifiques collecteurs avec préfixe pour éviter les conflits
    Route::get('/collecteur/paiements', [PaiementController::class, 'index'])->name('collecteur.paiements.index');
    Route::get('/collecteur/paiements/create', [PaiementController::class, 'create'])->name('collecteur.paiements.create');
    Route::post('/collecteur/paiements', [PaiementController::class, 'store'])->name('collecteur.paiements.store');
    Route::get('/collecteur/paiements/{paiement}', [PaiementController::class, 'show'])->name('collecteur.paiements.show');
    Route::get('/collecteur/paiements/{paiement}/ticket', [PaiementController::class, 'ticket'])->name('collecteur.paiements.ticket');
    
    // Routes pour finaliser les paiements partiels (collecteurs)
    Route::get('/collecteur/paiements/{paiement}/add-payment', [PaiementController::class, 'addPayment'])->name('collecteur.paiements.add-payment');
    Route::post('/collecteur/paiements/{paiement}/add-payment', [PaiementController::class, 'storeAdditionalPayment'])->name('collecteur.paiements.store-additional');
    
    // Routes de tracking pour les collecteurs
    Route::post('/tracking/position', [TrackingController::class, 'store'])->name('tracking.store');
    
    Route::get('/collecteur/unites', [UniteCommercialeController::class, 'index'])->name('collecteur.unites.index');
    Route::get('/collecteur/unites/update', [UniteCommercialeController::class, 'updateFormForCollecteur'])->name('collecteur.unites.update-form');
    Route::post('/collecteur/unites/update', [UniteCommercialeController::class, 'updateByCollecteur'])->name('collecteur.unites.update-submit');
    Route::get('/collecteur/unites/{unite}', [UniteCommercialeController::class, 'show'])->name('collecteur.unites.show');
});

// Routes pour le géomarketing avancé du maire
Route::middleware(['auth', 'role:maire'])->group(function () {
    Route::get('/maire/geomarketing/advanced', [App\Http\Controllers\AdvancedGeomarketingController::class, 'index'])->name('maire.geomarketing.advanced');
    Route::post('/maire/geomarketing/analyze', [App\Http\Controllers\AdvancedGeomarketingController::class, 'analyzeOpportunities'])->name('maire.geomarketing.analyze');
    Route::post('/maire/geomarketing/proposals', [App\Http\Controllers\AdvancedGeomarketingController::class, 'generateInvestmentProposals'])->name('maire.geomarketing.proposals');
    Route::get('/maire/geomarketing/zone/{id}', [App\Http\Controllers\AdvancedGeomarketingController::class, 'analyzeZoneDetail'])->name('maire.geomarketing.zone.detail');
    Route::get('/maire/geomarketing/population', [App\Http\Controllers\AdvancedGeomarketingController::class, 'populationData'])->name('maire.geomarketing.population');
    Route::get('/maire/geomarketing/roads', [App\Http\Controllers\AdvancedGeomarketingController::class, 'roadData'])->name('maire.geomarketing.roads');
    Route::get('/maire/geomarketing/bati', [App\Http\Controllers\AdvancedGeomarketingController::class, 'batiData'])->name('maire.geomarketing.bati');
});

// Routes API pour les données géographiques (accès restreint)
Route::middleware(['auth', 'throttle:60,1'])->prefix('api')->group(function () {
    Route::get('/unites-commerciales', [UniteCommercialeController::class, 'apiIndex']);
    Route::get('/zones-fiscales', [ZoneFiscaleController::class, 'apiIndex']);
    Route::post('/unites-commerciales', [UniteCommercialeController::class, 'apiStore']);
    Route::get('/security/status', function () {
        return response()->json([
            'status' => 'active',
            'timestamp' => now(),
            'protections' => [
                'csrf' => true,
                'rate_limiting' => true,
                'sql_injection' => true,
                'xss_protection' => true,
                'ddos_protection' => true,
            ]
        ]);
    });
});

// Route de test de sécurité (uniquement en développement)
if (app()->environment('local')) {
    Route::get('/security-test', function () {
        return view('security.test');
    });
}

// Route de diagnostic pour tester le rôle utilisateur
Route::get('/test-role', function () {
    if (!auth()->check()) {
        return '<h1>❌ NON CONNECTÉ</h1><p><a href="/admin/login">Se connecter</a></p>';
    }
    
    $user = auth()->user();
    $html = '<!DOCTYPE html><html><head><title>Test Rôle</title></head><body>';
    $html .= '<h1>🔍 DIAGNOSTIC RÔLE UTILISATEUR</h1>';
    $html .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">';
    $html .= '<h2>Informations utilisateur :</h2>';
    $html .= '<p><strong>Nom :</strong> ' . $user->name . '</p>';
    $html .= '<p><strong>Identifiant :</strong> ' . $user->identifiant . '</p>';
    $html .= '<p><strong>Rôle :</strong> ' . $user->role . '</p>';
    $html .= '<p><strong>ID :</strong> ' . $user->id . '</p>';
    $html .= '</div>';
    
    $html .= '<h2>Test des liens :</h2>';
    $html .= '<div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0;">';
    
    if ($user->role === 'gestionnaire') {
        $html .= '<h3>✅ GESTIONNAIRE - Liens disponibles :</h3>';
        $html .= '<ul>';
        $html .= '<li><a href="/collecteurs" style="color: blue; font-size: 18px;">📋 /collecteurs (Collecteurs)</a></li>';
        $html .= '<li><a href="/users" style="color: blue; font-size: 18px;">👥 /users (Utilisateurs)</a></li>';
        $html .= '<li><a href="/zones" style="color: blue; font-size: 18px;">🗺️ /zones (Zones)</a></li>';
        $html .= '<li><a href="/unites" style="color: blue; font-size: 18px;">🏪 /unites (Unités)</a></li>';
        $html .= '<li><a href="/taxes" style="color: blue; font-size: 18px;">💰 /taxes (Taxes)</a></li>';
        $html .= '</ul>';
    } elseif ($user->role === 'maire') {
        $html .= '<h3>✅ MAIRE - Accès en lecture seule :</h3>';
        $html .= '<ul>';
        $html .= '<li><a href="/users" style="color: blue; font-size: 18px;">👥 /users (Utilisateurs - consultation)</a></li>';
        $html .= '<li><a href="/zones" style="color: blue; font-size: 18px;">🗺️ /zones (Zones fiscales - consultation)</a></li>';
        $html .= '<li><a href="/unites" style="color: blue; font-size: 18px;">🏪 /unites (Unités commerciales - consultation)</a></li>';
        $html .= '<li><a href="/taxes" style="color: blue; font-size: 18px;">💰 /taxes (Taxes - consultation)</a></li>';
        $html .= '<li><a href="/paiements" style="color: blue; font-size: 18px;">💳 /paiements (Paiements - consultation)</a></li>';
        $html .= '<li><a href="/rapports" style="color: blue; font-size: 18px;">📊 /rapports (Rapports)</a></li>';
        $html .= '<li><a href="/map" style="color: blue; font-size: 18px;">🗺️ /map (Carte)</a></li>';
        $html .= '<li><a href="/logs" style="color: blue; font-size: 18px;">📋 /logs (Logs)</a></li>';
        $html .= '</ul>';
        $html .= '<p style="color: orange; font-weight: bold;">⚠️ Le maire peut tout voir mais ne peut rien modifier/supprimer</p>';
        $html .= '<ul style="color: #28a745;">';
        $html .= '<li>✅ Accès complet en lecture seule</li>';
        $html .= '<li>✅ Pas de boutons "Ajouter", "Modifier", "Supprimer"</li>';
        $html .= '<li>✅ Titres changés en "Consultation"</li>';
        $html .= '</ul>';
    } else {
        $html .= '<h3>❌ COLLECTEUR - Accès limité</h3>';
        $html .= '<p>Vous ne pouvez pas accéder à la gestion des utilisateurs.</p>';
    }
    
    $html .= '</div>';
    $html .= '<p><a href="/gestionnaire/dashboard" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔙 Retour Dashboard</a></p>';
    $html .= '</body></html>';
    
    return response($html, 200, ['Content-Type' => 'text/html']);
});

Route::post('/map/photo', [PhotoMapController::class, 'store'])->name('map.photo.store');
Route::get('/map/photos', [PhotoMapController::class, 'all'])->name('map.photo.all');

// Routes PostGIS pour les tests
Route::get('/test-postgis', [PostGISController::class, 'testConnection'])->name('test.postgis');
Route::get('/api/zones-fiscales', [PostGISController::class, 'getZonesFiscales'])->name('api.zones.fiscales');
Route::get('/api/find-zone', [PostGISController::class, 'findZoneByPoint'])->name('api.find.zone');

// Routes pour la carte des zones fiscales
Route::get('/carte-zones', [CarteController::class, 'index'])->name('carte.zones');
Route::get('/api/carte-zones-data', [CarteController::class, 'getZonesData'])->name('api.carte.zones.data');

/*
|--------------------------------------------------------------------------
| GEOSERVER (WMS / WFS - SIG Intercommunal)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\GeoServerController;

Route::prefix('api/geoserver')->name('api.geoserver.')->group(function () {
    Route::get('/wms-url', [GeoServerController::class, 'wmsUrl'])->name('wms.url');
    Route::get('/wfs-features', [GeoServerController::class, 'wfsFeatures'])->name('wfs.features');
    Route::get('/vector-tiles', [GeoServerController::class, 'vectorTilesUrl'])->name('vectortiles.url');
});
Route::get('/geoserver', [GeoServerController::class, 'index'])->name('geoserver.index')->middleware('auth');

/*
|--------------------------------------------------------------------------
| PORTAIL CONTRIBUABLE (Portail Public)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ContribuableController;

Route::prefix('contribuable')->name('contribuable.')->group(function () {
    // Connexion OTP
    Route::get('/login', [ContribuableController::class, 'showLogin'])->name('login');
    Route::post('/send-otp', [ContribuableController::class, 'sendOtp'])->name('send.otp');
    Route::post('/verify-otp', [ContribuableController::class, 'verifyOtp'])->name('verify.otp');
    
    // Espace contribuable (authentifié)
    Route::middleware('auth:contribuable')->group(function () {
        Route::get('/dashboard', [ContribuableController::class, 'dashboard'])->name('dashboard');
        Route::get('/paiement', [ContribuableController::class, 'paymentForm'])->name('paiement');
        Route::post('/paiement', [ContribuableController::class, 'storePayment'])->name('paiement.store');
        Route::post('/associate-unite', [ContribuableController::class, 'associateUnite'])->name('associate.unite');
        Route::post('/initiate-payment', [ContribuableController::class, 'initiatePayment'])->name('initiate.payment');
        Route::get('/paiements/{paiement}/recu', [ContribuableController::class, 'receipt'])->name('paiements.receipt');
        Route::post('/contact-mairie', [ContribuableController::class, 'contactMairie'])->name('contact.mairie');
        Route::get('/logout', [ContribuableController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| WEBHOOKS MOBILE MONEY (Wave, Orange Money, Free Money)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\WebhookController;

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/wave', [WebhookController::class, 'waveWebhook'])->name('wave');
    Route::post('/orange-money', [WebhookController::class, 'orangeMoneyWebhook'])->name('orange_money');
    Route::post('/free-money', [WebhookController::class, 'freeMoneyWebhook'])->name('free_money');
});

/*
|--------------------------------------------------------------------------
| ADMIN SICOFIL (Multi-Communes National)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\AdminSicofilController;

Route::middleware(['auth', 'role:admin_sicofil'])->prefix('admin-sicofil')->name('admin.sicofil.')->group(function () {
    Route::get('/dashboard', [AdminSicofilController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des abonnements
    Route::get('/abonnements', [AdminSicofilController::class, 'abonnements'])->name('abonnements.index');
    Route::get('/abonnements/create', [AdminSicofilController::class, 'createAbonnement'])->name('abonnements.create');
    Route::post('/abonnements', [AdminSicofilController::class, 'storeAbonnement'])->name('abonnements.store');
    Route::get('/abonnements/{id}', [AdminSicofilController::class, 'showAbonnement'])->name('abonnements.show');
    Route::post('/abonnements/{id}/suspend', [AdminSicofilController::class, 'suspendAbonnement'])->name('abonnements.suspend');
    Route::post('/abonnements/{id}/activate', [AdminSicofilController::class, 'activateAbonnement'])->name('abonnements.activate');
    Route::get('/abonnements/{id}/edit', [AdminSicofilController::class, 'editAbonnement'])->name('abonnements.edit');
    Route::put('/abonnements/{id}', [AdminSicofilController::class, 'updateAbonnement'])->name('abonnements.update');
    Route::post('/abonnements/{id}/renew', [AdminSicofilController::class, 'renewAbonnement'])->name('abonnements.renew');
    Route::delete('/abonnements/{id}', [AdminSicofilController::class, 'deleteAbonnement'])->name('abonnements.delete');
    
    // Facturation nationale
    Route::get('/factures', [AdminSicofilController::class, 'factures'])->name('factures.index');
    Route::post('/factures/generate', [AdminSicofilController::class, 'generateFactures'])->name('factures.generate');
    Route::get('/factures/export/pdf', [AdminSicofilController::class, 'exportFacturesPdf'])->name('factures.export.pdf');
    Route::get('/factures/export/excel', [AdminSicofilController::class, 'exportFacturesExcel'])->name('factures.export.excel');
    Route::get('/factures/{id}/pdf', [AdminSicofilController::class, 'facturePdf'])->name('factures.pdf');
    Route::post('/factures/{id}/pay', [AdminSicofilController::class, 'markFacturePayee'])->name('factures.pay');
    
    Route::get('/taxes', [AdminSicofilController::class, 'taxes'])->name('taxes');
    Route::get('/logs', [AdminSicofilController::class, 'logs'])->name('logs');

    // Statistiques globales
    Route::get('/stats', [AdminSicofilController::class, 'stats'])->name('stats');
    Route::get('/audit', [AdminSicofilController::class, 'audit'])->name('audit');
    Route::get('/support', [AdminSicofilController::class, 'support'])->name('support');
    Route::post('/support', [AdminSicofilController::class, 'storeSupportTicket'])->name('support.store');
    Route::put('/support/{ticket}', [AdminSicofilController::class, 'updateSupportTicket'])->name('support.update');
    Route::get('/monitoring', [AdminSicofilController::class, 'monitoring'])->name('monitoring');
    
    // Notifications & webhooks
    Route::get('/notifications', [AdminSicofilController::class, 'notifications'])->name('notifications');
    Route::get('/webhooks-logs', [AdminSicofilController::class, 'webhooksLogs'])->name('webhooks.logs');
});
