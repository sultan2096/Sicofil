@echo off
chcp 65001 >nul
echo.
echo ╔════════════════════════════════════════════════════╗
echo ║   SICOFIL - Installation Plateforme Nationale    ║
echo ╚════════════════════════════════════════════════════╝
echo.

REM Vérifier si Composer est installé
where composer >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Composer n'est pas installé !
    echo Téléchargez-le sur https://getcomposer.org
    pause
    exit /b 1
)

REM Vérifier si PHP est disponible
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    set PHP_PATH=c:\xampp\php\php.exe
    if not exist "%PHP_PATH%" (
        echo ❌ PHP non trouvé !
        pause
        exit /b 1
    )
) else (
    set PHP_PATH=php
)

echo [1/8] 📦 Installation des dépendances Composer...
composer install --no-interaction --prefer-dist --optimize-autoloader
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Erreur lors de l'installation Composer
    pause
    exit /b 1
)
echo ✅ Dépendances installées

echo.
echo [2/8] 🔑 Génération de la clé d'application...
%PHP_PATH% artisan key:generate --force
echo ✅ Clé générée

echo.
echo [3/8] 📊 Création des tables de base de données...
%PHP_PATH% artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️ Erreur migration - Vérifiez votre configuration DB
)
echo ✅ Tables créées

echo.
echo [4/8] 🌱 Seed des données initiales...
%PHP_PATH% artisan db:seed --force
echo ✅ Données insérées

echo.
echo [5/8] 📝 Publication des assets Horizon...
%PHP_PATH% artisan horizon:install
echo ✅ Horizon installé

echo.
echo [6/8] 🗂️ Création du lien symbolique storage...
%PHP_PATH% artisan storage:link
echo ✅ Lien créé

echo.
echo [7/8] 🧹 Nettoyage du cache...
%PHP_PATH% artisan cache:clear
%PHP_PATH% artisan config:clear
%PHP_PATH% artisan route:clear
%PHP_PATH% artisan view:clear
echo ✅ Cache nettoyé

echo.
echo [8/8] 🔐 Configuration des permissions...
echo ℹ️ Assurez-vous que les dossiers storage/ et bootstrap/cache/ sont accessibles en écriture

echo.
echo ╔════════════════════════════════════════════════════╗
echo ║           ✅ INSTALLATION TERMINÉE !              ║
echo ╚════════════════════════════════════════════════════╝
echo.
echo 📋 Prochaines étapes:
echo.
echo 1. Configurez votre fichier .env avec vos identifiants DB
echo 2. Configurez Redis (REDIS_HOST, REDIS_PORT)
echo 3. Configurez les APIs Mobile Money (Wave, Orange Money)
echo 4. Configurez le fournisseur SMS (Twilio, InfoBip, etc.)
echo.
echo 🚀 Pour démarrer l'application:
echo    - start_sicofil.bat
echo.
echo 📖 Documentation complète: SICOFIL_IMPLEMENTATION.md
echo.
pause
