@echo off
echo ========================================
echo Execution de la migration de geolocalisation
echo ========================================
echo.

echo 1. Verification de PHP...
php --version
if errorlevel 1 (
    echo ERREUR: PHP n'est pas trouve dans le PATH
    echo Essayez d'ajouter PHP au PATH ou utilisez XAMPP
    pause
    exit /b 1
)

echo.
echo 2. Execution de la migration...
php artisan migrate --path=database/migrations/2025_01_25_000000_add_location_fields_to_paiements_table.php
if errorlevel 1 goto error

echo.
echo 3. Verification de la migration...
php artisan migrate:status
if errorlevel 1 goto error

echo.
echo ========================================
echo Migration executee avec succes!
echo ========================================
echo.
echo Les nouveaux champs ont ete ajoutes a la table paiements:
echo - payment_latitude
echo - payment_longitude  
echo - payment_accuracy
echo - payment_method
echo.
echo Vous pouvez maintenant tester la geolocalisation des paiements.
echo.
pause
exit /b 0

:error
echo.
echo ========================================
echo ERREUR: La migration a echoue!
echo ========================================
echo.
echo Verifiez que:
echo 1. La base de donnees est accessible
echo 2. Les permissions sont correctes
echo 3. Aucune migration n'est en conflit
echo.
pause
exit /b 1
