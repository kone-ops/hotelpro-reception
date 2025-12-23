# Script de déploiement pour Windows 11 avec Laragon
# Exécuter depuis le dossier du projet

param(
    [string]$ProjectPath = "C:\laragon\www\hotelpro",
    [switch]$SkipComposer = $false,
    [switch]$SkipNPM = $false,
    [switch]$SkipMigrations = $false
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Déploiement Hotel Pro - Windows 11" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si Laragon est installé
$laragonPath = "C:\laragon"
if (-not (Test-Path $laragonPath)) {
    Write-Host "AVERTISSEMENT: Laragon n'est pas installé dans C:\laragon" -ForegroundColor Yellow
    Write-Host "Vous pouvez continuer, mais assurez-vous que PHP et Composer sont dans le PATH" -ForegroundColor Yellow
    Write-Host ""
}

# Vérifier si le chemin existe
if (-not (Test-Path $ProjectPath)) {
    Write-Host "Création du dossier: $ProjectPath" -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $ProjectPath -Force | Out-Null
    Write-Host "  ✓ Dossier créé" -ForegroundColor Green
} else {
    Write-Host "Dossier du projet: $ProjectPath" -ForegroundColor Green
}

Set-Location $ProjectPath

Write-Host ""
Write-Host "[1/5] Vérification de l'environnement..." -ForegroundColor Yellow

# Vérifier PHP
try {
    $phpVersion = php -v 2>&1 | Select-String -Pattern "PHP (\d+\.\d+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    Write-Host "  ✓ PHP trouvé: Version $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "  ✗ ERREUR: PHP n'est pas installé ou n'est pas dans le PATH" -ForegroundColor Red
    Write-Host "    Installez Laragon ou ajoutez PHP au PATH" -ForegroundColor Yellow
    exit 1
}

# Vérifier Composer
if (-not $SkipComposer) {
    try {
        $composerVersion = composer --version 2>&1 | Select-String -Pattern "Composer version (\S+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
        Write-Host "  ✓ Composer trouvé: Version $composerVersion" -ForegroundColor Green
    } catch {
        Write-Host "  ✗ ERREUR: Composer n'est pas installé" -ForegroundColor Red
        Write-Host "    Téléchargez depuis: https://getcomposer.org/" -ForegroundColor Yellow
        exit 1
    }
}

# Vérifier Node.js
if (-not $SkipNPM) {
    try {
        $nodeVersion = node -v 2>&1
        Write-Host "  ✓ Node.js trouvé: $nodeVersion" -ForegroundColor Green
    } catch {
        Write-Host "  ⚠ AVERTISSEMENT: Node.js n'est pas installé. Les assets ne seront pas compilés." -ForegroundColor Yellow
        Write-Host "    Téléchargez depuis: https://nodejs.org/" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "[2/5] Installation des dépendances PHP..." -ForegroundColor Yellow
if (-not $SkipComposer) {
    if (Test-Path "composer.json") {
        composer install --no-dev --optimize-autoloader --no-interaction
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✓ Dépendances PHP installées" -ForegroundColor Green
        } else {
            Write-Host "  ✗ ERREUR lors de l'installation de Composer" -ForegroundColor Red
            exit 1
        }
    } else {
        Write-Host "  ⚠ Aucun composer.json trouvé, ignoré" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ⊘ Ignoré (--SkipComposer)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "[3/5] Compilation des assets..." -ForegroundColor Yellow
if (-not $SkipNPM) {
    if (Test-Path "package.json") {
        npm install --production
        if ($LASTEXITCODE -eq 0) {
            npm run build
            if ($LASTEXITCODE -eq 0) {
                Write-Host "  ✓ Assets compilés" -ForegroundColor Green
            } else {
                Write-Host "  ⚠ Erreur lors de la compilation des assets" -ForegroundColor Yellow
            }
        } else {
            Write-Host "  ⚠ Erreur lors de l'installation npm" -ForegroundColor Yellow
        }
    } else {
        Write-Host "  ⚠ Aucun package.json trouvé, ignoré" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ⊘ Ignoré (--SkipNPM)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "[4/5] Configuration de Laravel..." -ForegroundColor Yellow

# Vérifier si .env existe
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Write-Host "  Création du fichier .env depuis .env.example..." -ForegroundColor Yellow
        Copy-Item ".env.example" ".env"
        Write-Host "  ✓ Fichier .env créé" -ForegroundColor Green
        Write-Host "  ⚠ IMPORTANT: Configurez le fichier .env avec vos paramètres!" -ForegroundColor Yellow
    } else {
        Write-Host "  ⚠ Aucun .env.example trouvé" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ✓ Fichier .env existe déjà" -ForegroundColor Green
}

# Générer la clé d'application
Write-Host "  Génération de la clé d'application..." -ForegroundColor Yellow
php artisan key:generate --force 2>&1 | Out-Null

# Mettre en cache les configurations
Write-Host "  Mise en cache des configurations..." -ForegroundColor Yellow
php artisan config:cache 2>&1 | Out-Null
php artisan route:cache 2>&1 | Out-Null
php artisan view:cache 2>&1 | Out-Null
php artisan event:cache 2>&1 | Out-Null
Write-Host "  ✓ Configurations mises en cache" -ForegroundColor Green

Write-Host ""
Write-Host "[5/5] Exécution des migrations..." -ForegroundColor Yellow
if (-not $SkipMigrations) {
    php artisan migrate --force
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Migrations exécutées" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ AVERTISSEMENT: Erreur lors des migrations" -ForegroundColor Yellow
        Write-Host "    Vérifiez votre configuration de base de données dans .env" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ⊘ Ignoré (--SkipMigrations)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  ✅ Déploiement terminé!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Afficher les URLs d'accès
Write-Host "URLs d'accès:" -ForegroundColor Yellow
Write-Host "  Local:  http://hotelpro.test" -ForegroundColor Cyan
Write-Host "  Local:  http://localhost" -ForegroundColor Cyan

# Obtenir l'adresse IP locale
$ipAddresses = Get-NetIPAddress -AddressFamily IPv4 | Where-Object { 
    $_.IPAddress -notlike "127.*" -and 
    $_.IPAddress -notlike "169.254.*" -and
    $_.IPAddress -notlike "192.168.137.*"
} | Select-Object -ExpandProperty IPAddress

if ($ipAddresses) {
    $ipAddress = $ipAddresses | Select-Object -First 1
    Write-Host "  Réseau: http://$ipAddress" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Pour accéder depuis le réseau:" -ForegroundColor Yellow
    Write-Host "  1. Configurez le pare-feu Windows:" -ForegroundColor White
    Write-Host "     New-NetFirewallRule -DisplayName 'Hotel Pro HTTP' -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  2. Accédez depuis un autre ordinateur:" -ForegroundColor White
    Write-Host "     http://$ipAddress" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Prochaines étapes:" -ForegroundColor Yellow
Write-Host "  1. Configurez le fichier .env avec vos paramètres" -ForegroundColor White
Write-Host "  2. Créez la base de données dans phpMyAdmin (si MySQL)" -ForegroundColor White
Write-Host "  3. Testez l'accès local: http://hotelpro.test" -ForegroundColor White
Write-Host "  4. Configurez le pare-feu pour l'accès réseau" -ForegroundColor White
Write-Host "  5. Distribuez l'application PWA ou Electron aux clients" -ForegroundColor White
Write-Host ""

