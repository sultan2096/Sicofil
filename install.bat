@echo off
echo ========================================
echo Installation de SICOSA
echo ========================================
echo.

echo 1. Installation des dependances PHP...
call composer install
if errorlevel 1 goto error

echo.
echo 2. Creation du fichier .env...
if not exist .env (
    copy .env.example .env
    echo Fichier .env cree. Veuillez configurer la base de donnees.
) else (
    echo Fichier .env existe deja.
)

echo.
echo 3. Generation de la cle d'application...
php artisan key:generate
if errorlevel 1 goto error

echo.
echo 4. Configuration de la base de donnees...
echo Veuillez vous assurer que PostgreSQL est installe et en cours d'execution.
echo Creez une base de donnees nommee 'sicosa_db' si ce n'est pas deja fait.
pause

echo.
echo 5. Execution des migrations...
php artisan migrate --seed
if errorlevel 1 goto error

echo.
echo 6. Installation des dependances frontend...
call npm install
if errorlevel 1 goto error

echo.
echo 7. Construction des assets...
call npm run build
if errorlevel 1 goto error

echo.
echo ========================================
echo Installation terminee avec succes!
echo ========================================
echo.
echo Identifiants par defaut:
echo - Maire: maire2025 / sicosa2025
echo - Admin: admin01 / admin2025
echo - Collecteurs: collecteur01-05 / collecteur2025
echo.
echo Pour lancer l'application: php artisan serve
echo.
pause
exit /b 0

:error
echo.
echo ========================================
echo ERREUR: L'installation a echoue!
echo ========================================
pause
exit /b 1 