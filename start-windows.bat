@echo off
rem Double-click to run the Enoma website on this computer.
cd /d "%~dp0"
title Enoma Digital Technologies - local website

set "PHP=php"
where php >nul 2>nul
if errorlevel 1 (
  if exist "C:\xampp\php\php.exe" (
    set "PHP=C:\xampp\php\php.exe"
  ) else if exist "C:\php\php.exe" (
    set "PHP=C:\php\php.exe"
  ) else (
    echo.
    echo   PHP is not installed yet.
    echo.
    echo   Easiest way: open PowerShell and run:
    echo       winget install PHP.PHP.8.3
    echo   then close this window and double-click start-windows.bat again.
    echo.
    echo   Or install XAMPP from https://www.apachefriends.org
    echo   Full instructions: LOCAL-SETUP.md
    echo.
    pause
    exit /b 1
  )
)

"%PHP%" start.php
pause
