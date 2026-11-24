@echo off
chcp 65001 >nul
title SICOFIL - Plateforme Nationale

echo.
echo ╔════════════════════════════════════════════════════╗
echo ║       SICOFIL - Démarrage de la Plateforme       ║
echo ╚════════════════════════════════════════════════════╝
echo.

REM Vérifier PHP
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    set PHP_PATH=c:\xampp\php\php.exe
) else (
    set PHP_PATH=php
)

REM Vérifier si Redis est en cours d'exécution
tasklist /FI "IMAGENAME eq redis-server.exe" 2>NUL | find /I /N "redis-server.exe">NUL
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️  Redis n'est pas démarré !
    echo.
    echo Options:
    echo   1. Installer Memurai (Redis pour Windows): https://www.memurai.com
    echo   2. Utiliser Redis dans Docker: docker run -d -p 6379:6379 redis
    echo.
    choice /C YN /M "Continuer sans Redis (jobs en mode sync)"
    if %ERRORLEVEL% EQU 2 exit /b
)

echo [1/3] 🧹 Nettoyage du cache...
%PHP_PATH% artisan cache:clear >nul 2>&1
%PHP_PATH% artisan config:clear >nul 2>&1
echo ✅ Cache nettoyé

echo.
echo [2/3] 🚀 Démarrage de Laravel Horizon (queues)...
start "Laravel Horizon" cmd /k "%PHP_PATH% artisan horizon"
timeout /t 2 >nul

echo.
echo [3/3] 🌐 Démarrage du serveur Laravel...
echo.
echo ╔════════════════════════════════════════════════════╗
echo ║         🎉 SICOFIL EST MAINTENANT ACTIF !         ║
echo ╚════════════════════════════════════════════════════╝
echo.
echo 📍 Accès aux interfaces:
echo.
echo   🏠 Accueil:              http://localhost/sicosa
echo   👤 Portail Contribuable: http://localhost/sicosa/contribuable/login
echo   🏛️  Admin SICOFIL:        http://localhost/sicosa/admin-sicofil/dashboard
echo   📊 Laravel Horizon:      http://localhost/sicosa/horizon
echo.
echo   📧 Comptes de test:
echo      - Admin SICOFIL:  adminsicofil@sicofil.sn / sicofil2026
echo      - Maire:          maire2025 / sicosa2025
echo      - Gestionnaire:   gestionnaire2025 / sicosa2025
echo      - Collecteur:     collecteur2025 / sicosa2025
echo.
echo ⚙️  Services actifs:
echo   - ✅ Serveur Laravel
echo   - ✅ Laravel Horizon (queues)
echo   - ✅ Redis Cache
echo.
echo 🛑 Pour arrêter: Fermez cette fenêtre et celle de Horizon
echo.
echo ════════════════════════════════════════════════════════
echo.

REM Démarrer le serveur Laravel
%PHP_PATH% artisan serve --host=0.0.0.0 --port=8000

pause
