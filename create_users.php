<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Création des utilisateurs SICOSA...\n";

// Supprimer les utilisateurs existants
User::truncate();

// Créer l'Administrateur
$admin = User::create([
    'name' => 'Administrateur',
    'email' => 'admin@sicosa.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin'
]);
echo "✅ Administrateur créé : admin@sicosa.com / admin123\n";

// Créer le Maire
$maire = User::create([
    'name' => 'Maire',
    'email' => 'maire@sicosa.com',
    'password' => Hash::make('maire123'),
    'role' => 'maire'
]);
echo "✅ Maire créé : maire@sicosa.com / maire123\n";

// Créer le Collecteur
$collecteur = User::create([
    'name' => 'Collecteur',
    'email' => 'collecteur@sicosa.com',
    'password' => Hash::make('collecteur123'),
    'role' => 'collecteur'
]);
echo "✅ Collecteur créé : collecteur@sicosa.com / collecteur123\n";

echo "\n🎉 Tous les utilisateurs ont été créés avec succès !\n";
echo "\n📋 Identifiants de connexion :\n";
echo "👨‍💼 Administrateur : admin@sicosa.com / admin123\n";
echo "🏛️ Maire : maire@sicosa.com / maire123\n";
echo "👷 Collecteur : collecteur@sicosa.com / collecteur123\n"; 