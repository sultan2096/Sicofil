@echo off
chcp 65001 >nul
title Queue Worker - SICOFIL

cd /d "c:\xampp\htdocs\sicosa"
set PHP_EXE=c:\xampp\php\php.exe
if not exist "%PHP_EXE%" set PHP_EXE=php

echo.
echo ═══════════════════════════════════════════════════════
echo   Traitement des jobs (queue:work) - SICOFIL
echo ═══════════════════════════════════════════════════════
echo.
echo Dossier: %CD%
echo Avec QUEUE_CONNECTION=database dans .env
echo Arret: Ctrl+C
echo ═══════════════════════════════════════════════════════
echo.

"%PHP_EXE%" artisan queue:work

pause
