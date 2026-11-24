<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;

$identifiant = 'admin_sicofil';
$password  = 'admin123';

$user = App\Models\User::where('identifiant', $identifiant)->first();

if (!$user) {
    echo "USER NOT FOUND\n";
    exit(1);
}

echo "User found: " . $user->name . " (role=" . $user->role . ")\n";

if (Hash::check($password, $user->password)) {
    echo "PASSWORD MATCHES: YES\n";

    // Reset password to be extra sure
    $newHash = Hash::make('sicofil2025');
    $user->password = $newHash;
    $user->save();
    echo "Password reset to: sicofil2025\n";

    // Clear rate limiter
    Illuminate\Support\Facades\RateLimiter::clear('login.' . request()->ip());
    echo "Rate limiter cleared\n";

    echo "\n=== NOW TRY LOGIN WITH ===\n";
    echo "Identifiant: admin_sicofil\n";
    echo "Password: sicofil2025\n";
} else {
    echo "PASSWORD MATCHES: NO\n";
}
