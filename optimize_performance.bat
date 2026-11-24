@echo off
echo ========================================
echo   OPTIMISATION SICOSA - PERFORMANCES
echo ========================================

cd /d %~dp0

echo [1/6] Nettoyage du cache...
php artisan cache:clear

echo [2/6] Nettoyage des vues compilees...
php artisan view:clear

echo [3/6] Optimisation des routes...
php artisan route:cache

echo [4/6] Optimisation de la configuration...
php artisan config:cache

echo [5/6] Nettoyage des sessions...
php artisan session:flush

echo [6/6] Optimisation du chargeur automatique...
composer dump-autoload --optimize

echo.
echo ========================================
echo   OPTIMISATION TERMINEE !
echo   Les pages devraient se charger plus rapidement
echo ========================================
pause 