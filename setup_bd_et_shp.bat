@echo off
chcp 65001 >nul
setlocal

:: ============================================================
:: SICOSA - Création BD, migrations et import des shapefiles
:: ============================================================
:: 1. Adapter PGBIN : chemin du dossier bin de PostgreSQL
::    Ex: C:\Program Files\PostgreSQL\16\bin
:: 2. Lancer ce script depuis la racine du projet (sicosa)
:: ============================================================

set PGBIN=C:\Program Files\PostgreSQL\16\bin
set PGHOST=127.0.0.1
set PGPORT=5432
set PGUSER=postgres
set PGPASSWORD=Sow
set DBNAME=sicosa_postgis
set SHP_DIR=%~dp0Sandiara_commune

:: Vérifier que le dossier des shapefiles existe
if not exist "%SHP_DIR%" (
    echo ERREUR: Dossier introuvable: %SHP_DIR%
    pause
    exit /b 1
)

:: Vérifier que shp2pgsql existe
if not exist "%PGBIN%\shp2pgsql.exe" (
    echo ERREUR: shp2pgsql introuvable dans %PGBIN%
    echo Modifiez PGBIN en tête de ce script (ex: PostgreSQL 15 = ...\PostgreSQL\15\bin^)
    pause
    exit /b 1
)

echo.
echo ===== 1. Suppression de l'ancienne base (si elle existe) =====
"%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d postgres -c "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '%DBNAME%' AND pid <> pg_backend_pid();" 2>nul
"%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d postgres -c "DROP DATABASE IF EXISTS %DBNAME%;"

echo.
echo ===== 2. Création de la base sicosa_postgis =====
"%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d postgres -c "CREATE DATABASE %DBNAME%;"
if errorlevel 1 (
    echo ERREUR lors de la création de la base.
    pause
    exit /b 1
)

echo.
echo ===== 3. Activation de PostGIS =====
"%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %DBNAME% -c "CREATE EXTENSION IF NOT EXISTS postgis;"
if errorlevel 1 (
    echo ERREUR lors de l'activation de PostGIS.
    pause
    exit /b 1
)

echo.
echo ===== 4. Migrations Laravel =====
cd /d "%~dp0"
call php artisan migrate --force
if errorlevel 1 (
    echo ERREUR lors des migrations.
    pause
    exit /b 1
)

echo.
echo ===== 5. Seed des zones fiscales (et autres données de base) =====
call php artisan db:seed --force
if errorlevel 1 (
    echo ERREUR lors du seed.
    pause
    exit /b 1
)

echo.
echo ===== 6. Import des shapefiles dans PostGIS =====
echo Les tables doivent s'appeler: polygone_loulibentegne, polygone_sandiaracentre, polygone_ndioukhthiorokh
echo pour que "php artisan zones:sync-geometries" fonctionne.
echo.

:: Import sandiaracommune (limite commune - optionnel)
echo Import: sandiaracommune...
"%PGBIN%\shp2pgsql.exe" -I -s 4326 -W UTF-8 "%SHP_DIR%\sandiaracommune.shp" public.sandiaracommune | "%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %DBNAME% -q
if errorlevel 1 echo (Table peut déjà exister ou fichier manquant - on continue.)

:: polygone loulybentegne -> table polygone_loulibentegne (nom attendu par zones:sync-geometries)
echo Import: polygone loulybentegne -> polygone_loulibentegne...
"%PGBIN%\shp2pgsql.exe" -I -s 4326 -W UTF-8 "%SHP_DIR%\polygone loulybentegne.shp" public.polygone_loulibentegne | "%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %DBNAME% -q
if errorlevel 1 (
    echo ERREUR import polygone_loulibentegne
) else (
    echo OK polygone_loulibentegne
)

:: polygone sandiaracentre (attention: nom fichier avec espace avant .shp)
echo Import: polygone sandiaracentre -> polygone_sandiaracentre...
"%PGBIN%\shp2pgsql.exe" -I -s 4326 -W UTF-8 "%SHP_DIR%\polygone sandiaracentre .shp" public.polygone_sandiaracentre | "%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %DBNAME% -q
if errorlevel 1 (
    echo ERREUR import polygone_sandiaracentre. Verifiez le nom du fichier (espace avant .shp^).
) else (
    echo OK polygone_sandiaracentre
)

:: polygone ndioukhthiorokhe -> table polygone_ndioukhthiorokh
echo Import: polygone ndioukhthiorokhe -> polygone_ndioukhthiorokh...
"%PGBIN%\shp2pgsql.exe" -I -s 4326 -W UTF-8 "%SHP_DIR%\polygone ndioukhthiorokhe.shp" public.polygone_ndioukhthiorokh | "%PGBIN%\psql.exe" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %DBNAME% -q
if errorlevel 1 (
    echo ERREUR import polygone_ndioukhthiorokh
) else (
    echo OK polygone_ndioukhthiorokh
)

echo.
echo ===== 7. Synchronisation des géométries vers zones_fiscales =====
call php artisan zones:sync-geometries

echo.
echo ===== Terminé =====
echo Base: %DBNAME%
echo Mot de passe .env: Sow
echo.
pause
