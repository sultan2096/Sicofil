<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal tracking collecteurs (Maire/Gestionnaire)
Broadcast::channel('tracking.commune.{communeId}', function ($user, $communeId) {
    $role = strtolower($user->role ?? '');
    if ($role === 'admin_sicofil') return true;
    return (int) $user->commune_id === (int) $communeId && in_array($role, ['maire', 'gestionnaire']);
});

// Canal position collecteur
Broadcast::channel('collector.{collectorId}', function ($user, $collectorId) {
    $collector = \App\Models\User::find($collectorId);
    if (!$collector || $collector->role !== 'collecteur') return false;
    if ($user->role === 'admin_sicofil') return true;
    return (int) $user->commune_id === (int) $collector->commune_id;
});
