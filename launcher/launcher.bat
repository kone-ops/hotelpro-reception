@echo off
REM Launcher automatique pour Hotel Pro
REM Lance le serveur en arrière-plan et ouvre l'application

REM Masquer la fenêtre de commande
if not "%1"=="min" start /min cmd /c "%~0" min & exit

REM Configuration
set SERVER_PATH=C:\laragon
set APP_URL=http://hotelpro.test
set APP_PORT=80

REM Vérifier si Laragon est installé
if exist "%SERVER_PATH%\laragon.exe" (
    set SERVER_TYPE=laragon
    set SERVER_EXE=%SERVER_PATH%\laragon.exe
    goto :start_server
)

REM Vérifier si WAMP est installé
if exist "C:\wamp64\wampmanager.exe" (
    set SERVER_TYPE=wamp
    set SERVER_PATH=C:\wamp64
    set SERVER_EXE=%SERVER_PATH%\wampmanager.exe
    goto :start_server
)

REM Vérifier si XAMPP est installé
if exist "C:\xampp\xampp-control.exe" (
    set SERVER_TYPE=xampp
    set SERVER_PATH=C:\xampp
    set SERVER_EXE=%SERVER_PATH%\xampp-control.exe
    goto :start_server
)

REM Si aucun serveur trouvé, afficher un message
echo Aucun serveur web trouve (Laragon/WAMP/XAMPP)
echo Installation requise.
pause
exit /b 1

:start_server
echo Demarrage du serveur...

REM Démarrer le serveur selon le type
if "%SERVER_TYPE%"=="laragon" (
    REM Laragon - Démarrer Apache et MySQL
    start "" "%SERVER_EXE%" start
    timeout /t 3 /nobreak >nul
)

if "%SERVER_TYPE%"=="wamp" (
    REM WAMP - Démarrer les services
    net start wampapache64 >nul 2>&1
    net start wampmysqld64 >nul 2>&1
    timeout /t 3 /nobreak >nul
)

if "%SERVER_TYPE%"=="xampp" (
    REM XAMPP - Démarrer Apache et MySQL
    "%SERVER_PATH%\apache_start.bat" >nul 2>&1
    "%SERVER_PATH%\mysql_start.bat" >nul 2>&1
    timeout /t 3 /nobreak >nul
)

REM Attendre que le serveur soit prêt
:wait_server
timeout /t 2 /nobreak >nul
curl -s -o nul %APP_URL% 2>nul
if errorlevel 1 (
    echo Attente du serveur...
    goto :wait_server
)

REM Ouvrir l'application dans le navigateur par défaut
start "" "%APP_URL%"

REM Optionnel : Ouvrir avec Electron si disponible
if exist "%~dp0..\electron-example\dist\Hotel Pro.exe" (
    start "" "%~dp0..\electron-example\dist\Hotel Pro.exe"
    exit /b 0
)

exit /b 0

