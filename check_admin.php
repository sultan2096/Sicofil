<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::where('role', 'admin_sicofil')->get(['id','identifiant','email','role','name','password']);
foreach($users as $u) {
    echo 'Identifiant: ' . $u->identifiant . PHP_EOL;
    echo 'Email: ' . $u->email . PHP_EOL;
    echo 'Role: ' . $u->role . PHP_EOL;
    echo 'Name: ' . $u->name . PHP_EOL;
    echo 'Hash matches sicofil2025: ' . (Hash::check('sicofil2025', $u->password) ? 'YES' : 'NO') . PHP_EOL;
    echo 'Hash matches admin123: ' . (Hash::check('admin123', $u->password) ? 'YES' : 'NO') . PHP_EOL;
}
