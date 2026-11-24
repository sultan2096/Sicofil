@echo off
chcp 65001 >nul
REM ============================================================
REM Import SHP Sandiara - cible Docker PostgreSQL sur port 5433
REM (5433 evite conflit avec PostgreSQL Windows qui utilise 5432)
REM ============================================================

set PGBIN=C:\Program Files\PostgreSQL\17\bin
set PGHOST=127.0.0.1
set PGPORT=5433
set PGDATABASE=sicosa_postgis
set PGUSER=postgres
set PGPASSWORD=Sow
set SHPDIR=%~dp0Sandiara_commune

echo.
echo ===== Import SHP Sandiara =====
echo Cible: %PGHOST%:%PGPORT% / %PGDATABASE%
echo Dossier: %SHPDIR%
echo.

REM Verifier que les outils PostgreSQL existent
if not exist "%PGBIN%\shp2pgsql.exe" (
    echo [ERREUR] shp2pgsql non trouve. Verifiez PGBIN ou installez PostgreSQL+PostGIS.
    echo PGBIN actuel: %PGBIN%
    pause
    exit /b 1
)

REM Verifier que le conteneur postgres est up (optionnel)
docker ps --filter "name=sicofil-postgres" --format "{{.Names}}" | findstr sicofil-postgres >nul
if errorlevel 1 (
    echo [ATTENTION] Conteneur sicofil-postgres non detecte. Verifiez: docker-compose up -d
    echo.
)

if not exist "%SHPDIR%\sandiaracommune.shp" (
    echo [ERREUR] Fichier absent: sandiaracommune.shp
    pause
    exit /b 1
)

echo [1/4] Extension PostGIS + suppression anciennes tables...
"%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -c "CREATE EXTENSION IF NOT EXISTS postgis; DROP TABLE IF EXISTS sandiaracommune CASCADE; DROP TABLE IF EXISTS \"polygone loulybentegne\" CASCADE; DROP TABLE IF EXISTS \"polygone ndioukhthiorokhe\" CASCADE; DROP TABLE IF EXISTS \"polygone sandiaracentre\" CASCADE;"
if errorlevel 1 (
    echo [ERREUR] Connexion impossible. Lancez: docker-compose up -d
    echo Mot de passe: %PGPASSWORD%
    pause
    exit /b 1
)

echo.
echo [2/4] Import sandiaracommune...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\sandiaracommune.shp" public.sandiaracommune | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
if errorlevel 1 goto :error

echo [3/4] Import polygone loulybentegne...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\polygone loulybentegne.shp" "public.polygone loulybentegne" | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
if errorlevel 1 goto :error

echo [4/4] Import polygone ndioukhthiorokhe...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\polygone ndioukhthiorokhe.shp" "public.polygone ndioukhthiorokhe" | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
if errorlevel 1 goto :error

echo Import polygone sandiaracentre...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\polygone sandiaracentre .shp" "public.polygone sandiaracentre" | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
if errorlevel 1 goto :error

echo.
echo Verification...
"%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -c "SELECT tablename FROM pg_tables WHERE schemaname='public' AND tablename IN ('sandiaracommune','polygone loulybentegne','polygone ndioukhthiorokhe','polygone sandiaracentre');"

echo.
echo ===== Import termine avec succes =====
echo Actualisez la carte: http://localhost:8080/map
pause
exit /b 0

:error
echo.
echo [ERREUR] Import echoue.
pause
exit /b 1
