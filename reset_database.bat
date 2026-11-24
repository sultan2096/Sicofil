@echo off
echo ========================================
echo   REINITIALISATION BASE DE DONNEES
echo ========================================
echo.

cd /d "C:\xampp\htdocs\sicosa\sicosa"

if not exist "artisan" (
    echo ERREUR: Fichier artisan non trouve!
    pause
    exit /b 1
)

echo ATTENTION: Cette operation va supprimer toutes les donnees!
echo.
set /p confirm="Voulez-vous continuer? (o/N): "

if /i "%confirm%" neq "o" (
    echo Operation annulee.
    pause
    exit /b 0
)

echo.
echo Reinitialisation de la base de donnees...
php artisan migrate:fresh --seed

echo.
echo Base de donnees reinitialisee avec succes!
echo.
echo Comptes disponibles:
echo - Admin: admin01 / admin2025  
echo - Maire: maire2025 / sicosa2025
echo - Collecteur: collecteur01 / collecteur2025
echo.
pause 