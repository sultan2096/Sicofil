@echo off
title SICOSA - Serveur Laravel
color 0A

echo.
echo ========================================
echo     SICOSA - Demarrage du serveur
echo ========================================
echo.
echo Demarrage du serveur Laravel...
echo.
echo Interfaces disponibles apres demarrage :
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
echo.

php artisan serve

echo.
echo Serveur arrete.
pause 