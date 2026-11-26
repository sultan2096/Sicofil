<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PaymentAlertController;
use App\Http\Controllers\GeomarketingController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PostGISController;
use App\Http\Controllers\MobileMoneyWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API publique pour vérification QR codes
Route::get('/verify-qr/{paiement}', [PaiementController::class, 'verifyQR']);

// API pour les alertes de retard (avec middleware auth)
Route::middleware('auth')->get('/alerts/overdue-count', [PaymentAlertController::class, 'getOverdueCount'])->name('api.alerts.overdue-count');

// API pour le tableau de bord de géomarketing
Route::middleware('auth')->get('/geomarketing/data', [GeomarketingController::class, 'data'])->name('api.geomarketing.data');
// Couches additionnelles et recommandations
Route::middleware('auth')->get('/geomarketing/population', [GeomarketingController::class, 'population'])->name('api.geomarketing.population');
Route::middleware('auth')->get('/geomarketing/roads', [GeomarketingController::class, 'roads'])->name('api.geomarketing.roads');
Route::middleware('auth')->get('/geomarketing/bati', [GeomarketingController::class, 'bati'])->name('api.geomarketing.bati');
Route::middleware('auth')->get('/geomarketing/recommendations', [GeomarketingController::class, 'recommendations'])->name('api.geomarketing.recommendations');

// API pour la carte thématique
Route::middleware('auth')->get('/map/thematic-data', [MapController::class, 'getThematicMapData'])->name('api.map.thematic-data');
Route::middleware('auth')->get('/map/data', [MapController::class, 'getData'])->name('api.map.data');
Route::middleware('auth')->get('/map/search', [MapController::class, 'search'])->name('api.map.search');
Route::middleware('auth')->get('/unites/{id}', [MapController::class, 'show'])->name('api.unites.show');

// API pour les zones fiscales PostGIS
Route::get('/zones-fiscales', [PostGISController::class, 'getZonesFiscales'])->name('api.zones-fiscales');
Route::get('/zones-fiscales/point', [PostGISController::class, 'findZoneByPoint'])->name('api.zones-fiscales.point');

// Webhooks Mobile Money (public, sécurisés par signature)
Route::post('/webhooks/wave', [MobileMoneyWebhookController::class, 'wave'])->name('webhooks.wave');
Route::post('/webhooks/orange-money', [MobileMoneyWebhookController::class, 'orangeMoney'])->name('webhooks.orange-money');
Route::post('/webhooks/free-money', [MobileMoneyWebhookController::class, 'freeMoney'])->name('webhooks.free-money');

// Webhook de test (local uniquement)
if (app()->environment('local')) {
    Route::post('/webhooks/test', [MobileMoneyWebhookController::class, 'testWebhook'])->name('webhooks.test');
}
