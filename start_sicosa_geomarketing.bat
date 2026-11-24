@echo off
title SICOSA Géomarketing - Démarrage Avancé
color 0A

echo.
echo ========================================
echo    🚀 SICOSA GÉOMARKETING AVANCÉ
echo ========================================
echo.
echo Démarrage du système de géomarketing...
echo.

:: Vérifier que XAMPP est installé
if not exist "C:\xampp\php\php.exe" (
    echo ❌ ERREUR: XAMPP n'est pas installé ou PHP n'est pas trouvé
    echo    Veuillez installer XAMPP depuis https://www.apachefriends.org/
    pause
    exit /b 1
)

:: Aller dans le répertoire du projet
cd /d "%~dp0"
echo 📁 Répertoire: %CD%

:: Vérifier que Laravel est installé
if not exist "vendor\autoload.php" (
    echo ⚠️  Laravel n'est pas installé. Installation en cours...
    echo.
    echo Installation des dépendances Composer...
    C:\xampp\php\php.exe composer install --no-dev --optimize-autoloader
    if errorlevel 1 (
        echo ❌ Erreur lors de l'installation des dépendances
        pause
        exit /b 1
    )
    echo ✅ Dépendances installées avec succès
)

:: Vérifier la base de données
echo.
echo 🔍 Vérification de la base de données...
C:\xampp\php\php.exe artisan migrate:status
if errorlevel 1 (
    echo ⚠️  Base de données non configurée. Configuration en cours...
    C:\xampp\php\php.exe artisan migrate
    if errorlevel 1 (
        echo ❌ Erreur lors de la migration de la base de données
        pause
        exit /b 1
    )
    echo ✅ Base de données configurée
)

:: Vérifier les tables de géomarketing
echo.
echo 🔍 Vérification des tables de géomarketing...
C:\xampp\php\php.exe artisan tinker --execute="
    if (!Schema::hasTable('population_zones')) {
        echo 'Tables de géomarketing manquantes. Création...';
        Artisan::call('migrate');
        echo 'Tables créées.';
    }
    if (!DB::table('population_zones')->count()) {
        echo 'Données de démonstration manquantes. Création...';
        Artisan::call('db:seed', ['--class' => 'GeomarketingDemoSeeder']);
        echo 'Données créées.';
    }
    echo 'Géomarketing prêt.';
"

:: Démarrer le serveur Laravel
echo.
echo 🚀 Démarrage du serveur Laravel...
echo.
echo 📍 URL: http://127.0.0.1:8000
echo 🗺️  Géomarketing: http://127.0.0.1:8000/maire/geomarketing/advanced
echo 🧪 Test: http://127.0.0.1:8000/test-geomarketing.html
echo.

start "SICOSA Géomarketing Server" cmd /k "cd /d %~dp0 && C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000"

:: Attendre que le serveur démarre
timeout /t 5 /nobreak >nul

:: Tester l'API
echo.
echo 🔍 Test de l'API de géomarketing...
echo.

echo Test de l'API de base...
curl -s http://127.0.0.1:8000/test-api
if errorlevel 1 (
    echo ❌ API de base non accessible
) else (
    echo ✅ API de base accessible
)

echo.
echo Test de l'API de géomarketing...
curl -s http://127.0.0.1:8000/maire/geomarketing/population
if errorlevel 1 (
    echo ❌ API géomarketing non accessible
) else (
    echo ✅ API géomarketing accessible
)

echo.
echo ========================================
echo    🎯 SYSTÈME GÉOMARKETING PRÊT !
echo ========================================
echo.
echo 🌐 Interfaces disponibles:
echo    • Accueil: http://127.0.0.1:8000
echo    • Géomarketing: http://127.0.0.1:8000/maire/geomarketing/advanced
echo    • Test: http://127.0.0.1:8000/test-geomarketing.html
echo.
echo 📊 Fonctionnalités disponibles:
echo    ✅ Analyse des zones d'opportunité commerciale
echo    ✅ Couches géographiques (Population, Routes, Bâti)
echo    ✅ Heatmap de densité de population
echo    ✅ Carte multi-critères
echo    ✅ Génération de propositions d'investissement
echo    ✅ Filtres avancés par nature d'activité
echo    ✅ Scores d'opportunité (0-100)
echo    ✅ Recommandations intelligentes
echo.
echo 🎮 Utilisation:
echo    1. Ouvrez http://127.0.0.1:8000/maire/geomarketing/advanced
echo    2. Configurez les filtres selon vos besoins
echo    3. Cliquez sur "Analyser" pour lancer l'analyse
echo    4. Utilisez les couches géographiques pour affiner l'analyse
echo    5. Générez des propositions d'investissement
echo.
echo 🔧 En cas de problème:
echo    • Vérifiez que XAMPP est démarré (Apache + MySQL)
echo    • Consultez les logs: storage/logs/laravel.log
echo    • Testez avec: http://127.0.0.1:8000/test-geomarketing.html
echo.
echo ========================================
echo    🚀 Serveur démarré avec succès !
echo ========================================
echo.
echo Appuyez sur une touche pour fermer cette fenêtre...
pause >nul



