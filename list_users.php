<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$roles = ['admin', 'admin_sicofil', 'maire', 'gestionnaire', 'collecteur'];

foreach ($roles as $role) {
    echo "=== ROLE: $role ===\n";
    $users = User::withoutGlobalScopes()->where('role', $role)->get(['id', 'name', 'identifiant', 'email']);
    if ($users->isEmpty()) {
        echo "Aucun compte\n";
    } else {
        foreach ($users as $u) {
            echo "  ID: {$u->id} | Nom: {$u->name} | Identifiant: " . ($u->identifiant ?? 'NULL') . " | Email: " . ($u->email ?? 'NULL') . "\n";
        }
    }
    echo "\n";
}
