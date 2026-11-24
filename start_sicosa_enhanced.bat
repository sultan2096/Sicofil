@echo off
title SICOSA - Serveur Laravel avec Vérification des Fonctionnalités
color 0A

echo.
echo ========================================
echo     SICOSA - Demarrage du serveur
echo ========================================
echo.
echo Demarrage du serveur Laravel...
echo.

REM Démarrer le serveur Laravel
start "SICOSA Server" cmd /k "cd /d %~dp0 && C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000"

REM Attendre que le serveur démarre
timeout /t 5 /nobreak >nul

echo.
echo ========================================
echo     Verification des fonctionnalites
echo ========================================
echo.

REM Tester l'API de base
echo Test de l'API de base...
curl -s http://127.0.0.1:8000/test-api >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ API de base accessible
) else (
    echo ❌ API de base non accessible
)

REM Tester l'API thématique
echo Test de l'API thematique...
curl -s http://127.0.0.1:8000/api/map/thematic-data >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ API thematique accessible
) else (
    echo ❌ API thematique non accessible (authentification requise)
)

echo.
echo ========================================
echo     Interfaces disponibles
echo ========================================
echo.
echo   - Accueil principal : http://127.0.0.1:8000
echo   - Interface Maire   : http://127.0.0.1:8000/maire/login
echo   - Interface Admin   : http://127.0.0.1:8000/admin/login
echo   - Interface Collecteur : http://127.0.0.1:8000/collecteur/login
echo.
echo Pages d'acces direct :
echo   - Maire      : http://127.0.0.1:8000/maire.html
echo   - Admin      : http://127.0.0.1:8000/admin.html
echo   - Collecteur : http://127.0.0.1:8000/collecteur.html
echo   - Test       : http://127.0.0.1:8000/test_recensement.html
echo.
echo ========================================
echo     Fonctionnalites de la carte
echo ========================================
echo.
echo   - Carte principale : http://127.0.0.1:8000/map
echo   - Carte thematique : http://127.0.0.1:8000/rapports/carte-thematique
echo   - Test des fonctionnalites : http://127.0.0.1:8000/test-map.html
echo.
echo ========================================
echo     Instructions d'utilisation
echo ========================================
echo.
echo 1. Ouvrez votre navigateur sur l'une des URLs ci-dessus
echo 2. Pour tester les fonctionnalites de la carte :
echo    - Allez sur /test-map.html
echo    - Cliquez sur les boutons de test
echo    - Verifiez que les filtres fonctionnent
echo 3. Pour la carte thematique :
echo    - Connectez-vous avec un compte valide
echo    - Utilisez les filtres pour analyser les donnees
echo    - Exportez les resultats en image ou PDF
echo.
echo ========================================
echo.
echo Serveur demarre avec succes !
echo Appuyez sur une touche pour fermer cette fenetre...
pause >nul

