# Script pour installer Hotel Pro comme service Windows
# Le serveur démarrera automatiquement au démarrage de Windows
# OPTIONNEL - Pour une expérience encore plus transparente

param(
    [switch]$Uninstall
)

if ($Uninstall) {
    Write-Host "Désinstallation du service..." -ForegroundColor Yellow
    
    $service = Get-Service -Name "HotelProServer" -ErrorAction SilentlyContinue
    if ($service) {
        Stop-Service -Name "HotelProServer" -Force
        sc.exe delete "HotelProServer"
        Write-Host "Service désinstallé." -ForegroundColor Green
    } else {
        Write-Host "Service non trouvé." -ForegroundColor Yellow
    }
    exit 0
}

# Vérifier les droits administrateur
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ERREUR: Ce script doit être exécuté en tant qu'administrateur!" -ForegroundColor Red
    exit 1
}

Write-Host "Installation du service Hotel Pro..." -ForegroundColor Cyan

# Chemin vers le script de démarrage
$scriptPath = Join-Path $PSScriptRoot "start-server-service.bat"
$serviceName = "HotelProServer"
$displayName = "Hotel Pro Server"

# Créer le script de service
$serviceScript = @"
@echo off
REM Service Windows pour Hotel Pro
REM Démarre automatiquement le serveur web

set SERVER_PATH=C:\laragon

if exist "%SERVER_PATH%\laragon.exe" (
    "%SERVER_PATH%\laragon.exe" start
    exit /b 0
)

if exist "C:\wamp64\wampmanager.exe" (
    net start wampapache64
    net start wampmysqld64
    exit /b 0
)

if exist "C:\xampp\xampp-control.exe" (
    C:\xampp\apache_start.bat
    C:\xampp\mysql_start.bat
    exit /b 0
)

exit /b 1
"@

$serviceScript | Out-File -FilePath $scriptPath -Encoding ASCII

# Créer le service Windows
$serviceExists = Get-Service -Name $serviceName -ErrorAction SilentlyContinue

if ($serviceExists) {
    Write-Host "Le service existe déjà. Mise à jour..." -ForegroundColor Yellow
    Stop-Service -Name $serviceName -Force -ErrorAction SilentlyContinue
    sc.exe delete $serviceName
    Start-Sleep -Seconds 2
}

# Installer le service avec NSSM (Non-Sucking Service Manager)
# Télécharger NSSM si nécessaire
$nssmPath = Join-Path $PSScriptRoot "nssm.exe"

if (-not (Test-Path $nssmPath)) {
    Write-Host "Téléchargement de NSSM..." -ForegroundColor Yellow
    $nssmUrl = "https://nssm.cc/release/nssm-2.24.zip"
    $nssmZip = Join-Path $env:TEMP "nssm.zip"
    
    try {
        Invoke-WebRequest -Uri $nssmUrl -OutFile $nssmZip
        Expand-Archive -Path $nssmZip -DestinationPath $env:TEMP -Force
        $nssmExe = Get-ChildItem -Path $env:TEMP -Filter "nssm.exe" -Recurse | Select-Object -First 1
        Copy-Item $nssmExe.FullName -Destination $nssmPath
        Write-Host "NSSM téléchargé." -ForegroundColor Green
    } catch {
        Write-Host "ERREUR: Impossible de télécharger NSSM." -ForegroundColor Red
        Write-Host "Alternative: Utiliser le launcher simple (launcher-vbs.vbs)" -ForegroundColor Yellow
        exit 1
    }
}

# Installer le service
& $nssmPath install $serviceName $scriptPath
& $nssmPath set $serviceName DisplayName $displayName
& $nssmPath set $serviceName Description "Service pour Hotel Pro - Démarre automatiquement le serveur web"
& $nssmPath set $serviceName Start SERVICE_AUTO_START
& $nssmPath set $serviceName AppExit Default Restart

Write-Host "Service installé!" -ForegroundColor Green
Write-Host ""
Write-Host "Le serveur démarrera automatiquement au démarrage de Windows." -ForegroundColor Cyan
Write-Host "Pour démarrer maintenant: Start-Service -Name HotelProServer" -ForegroundColor Yellow
Write-Host "Pour désinstaller: .\install-service.ps1 -Uninstall" -ForegroundColor Yellow

