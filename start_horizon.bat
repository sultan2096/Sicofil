@echo off
chcp 65001 >nul
title Laravel Horizon - SICOFIL

cd /d "c:\xampp\htdocs\sicosa"
set PHP_EXE=c:\xampp\php\php.exe
if not exist "%PHP_EXE%" set PHP_EXE=php

echo.
echo ═══════════════════════════════════════════════════════
echo   Horizon ne tourne PAS sous Windows (PHP pcntl absent)
echo ═══════════════════════════════════════════════════════
echo.
echo Sur Windows, utilisez le worker de file a la place:
echo   Double-cliquez sur: start_queue_work.bat
echo   Ou en terminal: %PHP_EXE% artisan queue:work
echo.
echo Le tableau de bord Horizon (page web) reste utilisable;
echo seuls les workers doivent etre lances avec queue:work.
echo ═══════════════════════════════════════════════════════
echo.
choice /C YN /M "Lancer queue:work maintenant (Y=Oui, N=Quitter)"
if errorlevel 2 exit /b
echo.
"%PHP_EXE%" artisan queue:work
pause
