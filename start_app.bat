@echo off
chcp 65001 >nul
cls
echo.
echo ════════════════════════════════════════════════════════
echo            SICOFIL - Démarrage Rapide
echo ════════════════════════════════════════════════════════
echo.
echo [√] Configuration optimisée
echo [√] Base de données: PostgreSQL
echo [√] Cache: File (rapide pour développement)
echo [√] Queues: Database
echo.
echo ════════════════════════════════════════════════════════
echo.
echo Lancement du serveur...
echo.
echo Accès:
echo   http://localhost/sicosa
echo   http://localhost/sicosa/admin-sicofil/dashboard
echo   http://localhost/sicosa/contribuable/login
echo.
echo Comptes:
echo   Admin SICOFIL: adminsicofil@sicofil.sn / sicofil2026
echo   Maire:         maire2025 / sicosa2025
echo.
echo ════════════════════════════════════════════════════════
echo.
cd c:\xampp\htdocs\sicosa
c:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
pause
