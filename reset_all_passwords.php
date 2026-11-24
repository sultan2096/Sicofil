<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$newPassword = 'password123';
$hashed = Hash::make($newPassword);

// Mettre a jour tous les comptes existants
User::withoutGlobalScopes()->whereNotNull('id')->update(['password' => $hashed]);

// Roles autorises par la contrainte CHECK
$rolesNeeded = ['admin_sicofil', 'maire', 'gestionnaire', 'collecteur'];
foreach ($rolesNeeded as $role) {
    $exists = User::withoutGlobalScopes()->where('role', $role)->exists();
    if (!$exists) {
        $maxId = User::withoutGlobalScopes()->max('id') ?? 0;
        $identifiant = $role === 'admin_sicofil' ? 'adminsicofil' : $role;
        $email = $identifiant . '@demo.com';
        // Verifier unicite email
        if (User::withoutGlobalScopes()->where('email', $email)->exists()) {
            $email = $identifiant . rand(100,999) . '@demo.com';
        }
        User::withoutGlobalScopes()->create([
            'name' => ucfirst(str_replace('_', ' ', $role)) . ' Demo',
            'identifiant' => $identifiant,
            'email' => $email,
            'password' => $hashed,
            'role' => $role,
            'actif' => true,
        ]);
        echo "CREATED|$role|$identifiant|$newPassword\n";
    }
}

// Afficher tous les identifiants
$all = User::withoutGlobalScopes()->get(['id', 'name', 'identifiant', 'email', 'role']);
foreach ($all as $u) {
    echo "ACCOUNT|" . $u->role . "|" . ($u->identifiant ?? 'NULL') . "|" . ($u->email ?? 'NULL') . "|" . $u->name . "\n";
}
echo "PASSWORD_RESET|$newPassword\n";
