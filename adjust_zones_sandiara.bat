@echo off
echo ========================================
echo   Ajustement des zones fiscales
echo   selon la limite de Sandiara
echo ========================================
echo.

echo Connexion a la base de donnees PostgreSQL...
echo.

REM Configuration de la base de données
set DB_HOST=localhost
set DB_PORT=5432
set DB_NAME=sicosa_sandiara
set DB_USER=postgres
set DB_PASSWORD=postgres

echo Execution du script SQL...
psql -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d %DB_NAME% -f "database\adjust_zones_within_sandiara_boundary.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   SUCCES: Zones ajustees avec succes!
    echo ========================================
    echo.
    echo Les zones fiscales ont ete ajustees pour:
    echo - Respecter exactement la limite de Sandiara
    echo - Couvrir totalement la commune
    echo - Ne pas se chevaucher
    echo.
    echo Vous pouvez maintenant rafraichir la carte:
    echo http://127.0.0.1:8000/map
    echo.
) else (
    echo.
    echo ========================================
    echo   ERREUR: Echec de l'ajustement des zones
    echo ========================================
    echo.
    echo Verifiez:
    echo - La connexion a PostgreSQL
    echo - Les permissions de la base de donnees
    echo - Le fichier SQL existe
    echo.
)

pause
