@echo off
setlocal
set "PHP=C:\xampp\php\php.exe"
set "ROOT=%~dp0"
set "ARTISAN=%ROOT%artisan"

if not exist "%PHP%" (
    echo PHP non trouve: %PHP%
    echo Ajustez le chemin dans ce fichier.
    pause
    exit /b 1
)

cd /d "%ROOT%"

if "%1"=="" goto usage
if "%1"=="up" goto docker
if "%1"=="backup" goto backup
if "%1"=="import-shp" goto import_shp
if "%1"=="import-geojson" goto import_geojson
if "%1"=="install-deps" goto install_deps
goto usage

:docker
echo Demarrage Docker...
docker compose up -d 2>nul || docker-compose up -d 2>nul || echo Docker non installe.
goto end

:backup
echo Backup base de donnees...
"%PHP%" "%ARTISAN%" sicofil:backup --compress
goto end

:import_shp
if "%2"=="" (
    echo Usage: %~nx0 import-shp "Sandiara_commune\polygone loulybentegne.shp" [--commune=1] [--truncate]
    goto end
)
"%PHP%" "%ARTISAN%" sicofil:import:shapefile %2 %3 %4
goto end

:import_geojson
if "%2"=="" (
    echo Usage: %~nx0 import-geojson zones.json [--commune=1] [--truncate]
    goto end
)
"%PHP%" "%ARTISAN%" sicofil:import:geojson-zones %2 %3 %4
goto end

:install_deps
echo Installation des dependances php-shapefile...
if exist composer.phar (
    "%PHP%" composer.phar require gasparesganga/php-shapefile
) else if exist composer (
    "%PHP%" composer require gasparesganga/php-shapefile
) else (
    "%PHP%" -r "copy('https://getcomposer.org/installer','composer-setup.php');"
    "%PHP%" composer-setup.php --quiet
    "%PHP%" composer.phar require gasparesganga/php-shapefile
    del composer-setup.php 2>nul
)
goto end

:usage
echo.
echo SICOFIL - Commandes utiles
echo ========================
echo   %~nx0 up              - Docker compose up -d
echo   %~nx0 backup          - Backup base
echo   %~nx0 import-shp path - Import shapefile
echo   %~nx0 import-geojson path - Import GeoJSON
echo   %~nx0 install-deps    - Installer php-shapefile
echo.
goto end

:end
pause
