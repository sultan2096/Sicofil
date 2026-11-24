@echo off
chcp 65001 >nul
REM ============================================================
REM Import des Shapefiles Sandiara dans PostgreSQL/PostGIS
REM BD: sicosa_postgis | User: postgres | Pass: Sow
REM shp2pgsql: dossier bin de PostgreSQL (ex: C:\Program Files\PostgreSQL\17\bin)
REM ============================================================

REM Modifier PGBIN si votre PostgreSQL est ailleurs (ex: 15, 16, ou C:\xampp\postgresql\bin)
set PGBIN=C:\Program Files\PostgreSQL\17\bin
set PGHOST=127.0.0.1
set PGPORT=5432
set PGDATABASE=sicosa_postgis
set PGUSER=postgres
set PGPASSWORD=Sow

REM Dossier des shapefiles
set SHPDIR=%~dp0Sandiara_commune

echo.
echo ===== Import SHP Sandiara dans PostgreSQL/PostGIS =====
echo Dossier SHP: %SHPDIR%
echo Base: %PGDATABASE%
echo.

REM Verifier que PostGIS est actif
"%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -c "CREATE EXTENSION IF NOT EXISTS postgis;" 2>nul
if errorlevel 1 (
    echo [ERREUR] Verifiez: PostgreSQL demarre, BD existe, PostGIS installe.
    pause
    exit /b 1
)

REM Supprimer les tables existantes avant re-import
echo Suppression des tables existantes...
"%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -c "DROP TABLE IF EXISTS sandiaracommune CASCADE; DROP TABLE IF EXISTS \"polygone loulybentegne\" CASCADE; DROP TABLE IF EXISTS \"polygone ndioukhthiorokhe\" CASCADE; DROP TABLE IF EXISTS \"polygone sandiaracentre\" CASCADE;" -q

REM 1. Commune Sandiara (limite globale)
echo [1/4] Import sandiaracommune.shp ...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\sandiaracommune.shp" public.sandiaracommune | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
echo OK sandiaracommune

REM 2. Polygone Loulibentegne
echo [2/4] Import polygone loulybentegne.shp ...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\polygone loulybentegne.shp" "public.polygone loulybentegne" | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
echo OK polygone loulybentegne

REM 3. Polygone Ndioukhthiorokh
echo [3/4] Import polygone ndioukhthiorokhe.shp ...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\polygone ndioukhthiorokhe.shp" "public.polygone ndioukhthiorokhe" | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
echo OK polygone ndioukhthiorokhe

REM 4. Polygone Sandiara Centre
echo [4/4] Import polygone sandiaracentre .shp ...
"%PGBIN%\shp2pgsql" -s 4326 -W UTF-8 -I -c -D "%SHPDIR%\polygone sandiaracentre .shp" "public.polygone sandiaracentre" | "%PGBIN%\psql" -h %PGHOST% -p %PGPORT% -U %PGUSER% -d %PGDATABASE% -q
echo OK polygone sandiaracentre

echo.
echo ===== Import termine =====
echo Les vrais SHP sont maintenant dans la base.
echo Actualisez la carte (http://localhost:8080/map) et activez "Limite commune (SHP)".
echo.
pause
