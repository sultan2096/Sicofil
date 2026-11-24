<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('identifiant', 'admin_sicofil')->first();
if ($user) {
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Role (raw): '" . $user->role . "'\n";
    echo "Role (lower): '" . strtolower((string)$user->role) . "'\n";
    echo "Commune ID: " . ($user->commune_id ?? 'NULL') . "\n";
    echo "Zone ID: " . ($user->zone_id ?? 'NULL') . "\n";
} else {
    echo "User admin_sicofil NOT FOUND\n";
}
