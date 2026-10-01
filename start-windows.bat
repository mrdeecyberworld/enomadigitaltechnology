@echo off
setlocal
rem Double-click to run the Enoma website on this computer.
rem The first time, it installs everything the website needs (PHP), then starts it.
cd /d "%~dp0"
title Enoma Digital Technologies - local website

set "ENOMA_HOME=%LOCALAPPDATA%\EnomaPHP"
set "PHP="
call :findphp
if defined PHP goto check

echo.
echo   ============================================================
echo    First-time setup
echo   ============================================================
echo.
echo   This website runs on PHP, which is not on this computer yet.
echo   It will be installed for you now (free, about 30 MB, 1-3 minutes).
echo   You only need to do this once.
echo.
choice /c YN /m "  Install PHP now"
if errorlevel 2 goto manual

call :install_winget
call :findphp
if defined PHP goto check
call :install_zip
call :findphp
if defined PHP goto check
goto manual

:check
rem PHP needs the Microsoft Visual C++ runtime; most PCs already have it.
"%PHP%" -v >nul 2>&1
if errorlevel 1 call :vcredist
"%PHP%" -v >nul 2>&1
if errorlevel 1 goto broken
call :cacert
set "ENOMA_CA_FILE=%ENOMA_HOME%\cacert.pem"
"%PHP%" start.php
pause
exit /b

rem ---------------------------------------------------------------
:findphp
for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP=%%D\php.exe"
if not defined PHP if exist "%ENOMA_HOME%\php.exe" set "PHP=%ENOMA_HOME%\php.exe"
if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined PHP if exist "C:\php\php.exe" set "PHP=C:\php\php.exe"
exit /b

:install_winget
where winget >nul 2>nul
if errorlevel 1 exit /b
echo.
echo   Installing PHP with Windows Package Manager...
winget install --id PHP.PHP.8.3 -e --source winget --accept-source-agreements --accept-package-agreements
exit /b

:install_zip
echo.
echo   Downloading PHP from windows.php.net...
if not exist "%ENOMA_HOME%" mkdir "%ENOMA_HOME%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; [Net.ServicePointManager]::SecurityProtocol='Tls12'; $z=Join-Path $env:TEMP 'enoma-php.zip'; Invoke-WebRequest 'https://windows.php.net/downloads/releases/latest/php-8.3-nts-Win32-vs16-x64-latest.zip' -OutFile $z -UseBasicParsing; Expand-Archive $z $env:ENOMA_HOME -Force; Remove-Item $z"
exit /b

:vcredist
echo.
echo   Installing the Microsoft Visual C++ runtime that PHP needs.
echo   Windows may ask for permission: click Yes.
where winget >nul 2>nul
if not errorlevel 1 (
  winget install --id Microsoft.VCRedist.2015+.x64 -e --source winget --accept-source-agreements --accept-package-agreements
  "%PHP%" -v >nul 2>&1
  if not errorlevel 1 exit /b
)
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; [Net.ServicePointManager]::SecurityProtocol='Tls12'; $f=Join-Path $env:TEMP 'vc_redist.x64.exe'; Invoke-WebRequest 'https://aka.ms/vs/17/release/vc_redist.x64.exe' -OutFile $f -UseBasicParsing; Start-Process $f -ArgumentList '/install','/quiet','/norestart' -Verb RunAs -Wait"
exit /b

:cacert
rem Security certificates so email (SMTP/Resend), news and the AI assistant can make secure connections.
if exist "%ENOMA_HOME%\cacert.pem" exit /b
if not exist "%ENOMA_HOME%" mkdir "%ENOMA_HOME%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol='Tls12'; try { Invoke-WebRequest 'https://curl.se/ca/cacert.pem' -OutFile (Join-Path $env:ENOMA_HOME 'cacert.pem') -UseBasicParsing } catch { }"
exit /b

:broken
echo.
echo   PHP was installed but could not start on this computer.
echo   Restart your computer and double-click start-windows.bat again.
echo   If it still fails, see LOCAL-SETUP.md (Troubleshooting).
echo.
pause
exit /b 1

:manual
echo.
echo   PHP could not be installed automatically.
echo.
echo   Install it yourself, then double-click start-windows.bat again:
echo     - Open PowerShell and run:  winget install PHP.PHP.8.3
echo     - Or install XAMPP from https://www.apachefriends.org
echo   Full instructions: LOCAL-SETUP.md
echo.
pause
exit /b 1
