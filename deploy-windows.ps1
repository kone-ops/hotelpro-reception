# Script de déploiement pour Windows Server 2016
# Exécuter en tant qu'administrateur

param(
    [string]$ProjectPath = "C:\inetpub\wwwroot\hotelpro-reception-main",
    [string]$ServerIP = "",
    [switch]$SkipComposer = $false,
    [switch]$SkipNPM = $false
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Déploiement Hotel Pro sur Windows" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier les permissions administrateur
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ERREUR: Ce script doit être exécuté en tant qu'administrateur!" -ForegroundColor Red
    exit 1
}

# Vérifier si le chemin existe
if (-not (Test-Path $ProjectPath)) {
    Write-Host "ERREUR: Le chemin du projet n'existe pas: $ProjectPath" -ForegroundColor Red
    Write-Host "Voulez-vous créer le dossier? (O/N)" -ForegroundColor Yellow
    $response = Read-Host
    if ($response -eq "O" -or $response -eq "o") {
        New-Item -ItemType Directory -Path $ProjectPath -Force | Out-Null
        Write-Host "Dossier créé: $ProjectPath" -ForegroundColor Green
    } else {
        exit 1
    }
}

Set-Location $ProjectPath

Write-Host "[1/6] Vérification de l'environnement..." -ForegroundColor Yellow

# Vérifier PHP
try {
    $phpVersion = php -v 2>&1 | Select-String -Pattern "PHP (\d+\.\d+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    Write-Host "  PHP trouvé: Version $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "  ERREUR: PHP n'est pas installé ou n'est pas dans le PATH" -ForegroundColor Red
    exit 1
}

# Vérifier Composer
if (-not $SkipComposer) {
    try {
        $composerVersion = composer --version 2>&1 | Select-String -Pattern "Composer version (\S+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
        Write-Host "  Composer trouvé: Version $composerVersion" -ForegroundColor Green
    } catch {
        Write-Host "  ERREUR: Composer n'est pas installé" -ForegroundColor Red
        exit 1
    }
}

# Vérifier Node.js
if (-not $SkipNPM) {
    try {
        $nodeVersion = node -v 2>&1
        Write-Host "  Node.js trouvé: $nodeVersion" -ForegroundColor Green
    } catch {
        Write-Host "  AVERTISSEMENT: Node.js n'est pas installé. Les assets ne seront pas compilés." -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "[2/6] Installation des dépendances PHP..." -ForegroundColor Yellow
if (-not $SkipComposer) {
    composer install --no-dev --optimize-autoloader --no-interaction
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  ERREUR lors de l'installation de Composer" -ForegroundColor Red
        exit 1
    }
    Write-Host "  Dépendances PHP installées" -ForegroundColor Green
} else {
    Write-Host "  Ignoré (--SkipComposer)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "[3/6] Compilation des assets..." -ForegroundColor Yellow
if (-not $SkipNPM) {
    if (Test-Path "package.json") {
        npm install --production
        npm run build
        Write-Host "  Assets compilés" -ForegroundColor Green
    } else {
        Write-Host "  Aucun package.json trouvé, ignoré" -ForegroundColor Gray
    }
} else {
    Write-Host "  Ignoré (--SkipNPM)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "[4/6] Configuration de Laravel..." -ForegroundColor Yellow

# Vérifier si .env existe
if (-not (Test-Path ".env")) {
    Write-Host "  Création du fichier .env..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "  Fichier .env créé. VEUILLEZ LE CONFIGURER!" -ForegroundColor Yellow
} else {
    Write-Host "  Fichier .env existe déjà" -ForegroundColor Green
}

# Générer la clé d'application si nécessaire
php artisan key:generate --force 2>&1 | Out-Null

# Mettre en cache les configurations
Write-Host "  Mise en cache des configurations..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

Write-Host "  Configurations mises en cache" -ForegroundColor Green

Write-Host ""
Write-Host "[5/6] Exécution des migrations..." -ForegroundColor Yellow
php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "  AVERTISSEMENT: Erreur lors des migrations" -ForegroundColor Yellow
} else {
    Write-Host "  Migrations exécutées" -ForegroundColor Green
}

Write-Host ""
Write-Host "[6/6] Configuration des permissions..." -ForegroundColor Yellow

# Donner les permissions à IIS_IUSRS
$acl = Get-Acl $ProjectPath
$permission = "IIS_IUSRS", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
$accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule $permission
$acl.SetAccessRule($accessRule)
Set-Acl $ProjectPath $acl

# Permissions pour storage et bootstrap/cache
$storagePath = Join-Path $ProjectPath "storage"
$cachePath = Join-Path $ProjectPath "bootstrap\cache"

if (Test-Path $storagePath) {
    $acl = Get-Acl $storagePath
    $acl.SetAccessRule($accessRule)
    Set-Acl $storagePath $acl
    Write-Host "  Permissions configurées pour storage/" -ForegroundColor Green
}

if (Test-Path $cachePath) {
    $acl = Get-Acl $cachePath
    $acl.SetAccessRule($accessRule)
    Set-Acl $cachePath $acl
    Write-Host "  Permissions configurées pour bootstrap/cache/" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Déploiement terminé avec succès!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Afficher l'URL d'accès
if ($ServerIP) {
    Write-Host "URL d'accès: http://$ServerIP" -ForegroundColor Cyan
} else {
    $ipAddress = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" }).IPAddress | Select-Object -First 1
    if ($ipAddress) {
        Write-Host "URL d'accès: http://$ipAddress" -ForegroundColor Cyan
    } else {
        Write-Host "URL d'accès: http://localhost" -ForegroundColor Cyan
    }
}

Write-Host ""
Write-Host "Prochaines étapes:" -ForegroundColor Yellow
Write-Host "  1. Configurer le fichier .env avec vos paramètres" -ForegroundColor White
Write-Host "  2. Configurer IIS pour pointer vers: $ProjectPath\public" -ForegroundColor White
Write-Host "  3. Configurer le pare-feu Windows pour autoriser le port 80" -ForegroundColor White
Write-Host "  4. Tester l'accès depuis un autre ordinateur du réseau" -ForegroundColor White
Write-Host ""

