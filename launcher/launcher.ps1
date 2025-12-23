# Launcher PowerShell pour Hotel Pro
# Lance le serveur automatiquement et ouvre l'application

# Masquer la fenêtre PowerShell
Add-Type -Name Window -Namespace Console -MemberDefinition '
[DllImport("Kernel32.dll")]
public static extern IntPtr GetConsoleWindow();
[DllImport("user32.dll")]
public static extern bool ShowWindow(IntPtr hWnd, Int32 nCmdShow);
'
$consolePtr = [Console.Window]::GetConsoleWindow()
[Console.Window]::ShowWindow($consolePtr, 0) # 0 = masquer

# Configuration
$ServerPath = "C:\laragon"
$AppURL = "http://hotelpro.test"
$AppPort = 80
$ServerType = $null

# Fonction pour vérifier si le serveur répond
function Test-Server {
    param([string]$URL)
    try {
        $response = Invoke-WebRequest -Uri $URL -TimeoutSec 2 -UseBasicParsing -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

# Détecter le type de serveur installé
if (Test-Path "$ServerPath\laragon.exe") {
    $ServerType = "laragon"
    $ServerExe = "$ServerPath\laragon.exe"
} elseif (Test-Path "C:\wamp64\wampmanager.exe") {
    $ServerType = "wamp"
    $ServerPath = "C:\wamp64"
    $ServerExe = "$ServerPath\wampmanager.exe"
} elseif (Test-Path "C:\xampp\xampp-control.exe") {
    $ServerType = "xampp"
    $ServerPath = "C:\xampp"
    $ServerExe = "$ServerPath\xampp-control.exe"
} else {
    # Aucun serveur trouvé
    [System.Windows.Forms.MessageBox]::Show(
        "Aucun serveur web trouvé (Laragon/WAMP/XAMPP).`nInstallation requise.",
        "Hotel Pro - Erreur",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    )
    exit 1
}

# Démarrer le serveur
Write-Host "Démarrage du serveur..." -ForegroundColor Green

switch ($ServerType) {
    "laragon" {
        # Laragon - Démarrer Apache et MySQL
        Start-Process -FilePath $ServerExe -ArgumentList "start" -WindowStyle Hidden
        Start-Sleep -Seconds 3
    }
    "wamp" {
        # WAMP - Démarrer les services Windows
        $apache = Get-Service -Name "wampapache64" -ErrorAction SilentlyContinue
        $mysql = Get-Service -Name "wampmysqld64" -ErrorAction SilentlyContinue
        
        if ($apache -and $apache.Status -ne "Running") {
            Start-Service -Name "wampapache64"
        }
        if ($mysql -and $mysql.Status -ne "Running") {
            Start-Service -Name "wampmysqld64"
        }
        Start-Sleep -Seconds 3
    }
    "xampp" {
        # XAMPP - Démarrer Apache et MySQL
        Start-Process -FilePath "$ServerPath\apache_start.bat" -WindowStyle Hidden
        Start-Process -FilePath "$ServerPath\mysql_start.bat" -WindowStyle Hidden
        Start-Sleep -Seconds 3
    }
}

# Attendre que le serveur soit prêt
$maxAttempts = 30
$attempt = 0
$serverReady = $false

while (-not $serverReady -and $attempt -lt $maxAttempts) {
    $serverReady = Test-Server -URL $AppURL
    if (-not $serverReady) {
        Start-Sleep -Seconds 2
        $attempt++
    }
}

if (-not $serverReady) {
    [System.Windows.Forms.MessageBox]::Show(
        "Le serveur n'a pas pu démarrer.`nVérifiez votre configuration.",
        "Hotel Pro - Erreur",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Warning
    )
    exit 1
}

# Ouvrir l'application
Write-Host "Ouverture de l'application..." -ForegroundColor Green

# Option 1 : Essayer d'ouvrir avec Electron si disponible
$electronPath = Join-Path $PSScriptRoot "..\electron-example\dist\Hotel Pro.exe"
if (Test-Path $electronPath) {
    Start-Process -FilePath $electronPath -WindowStyle Normal
} else {
    # Option 2 : Ouvrir dans le navigateur par défaut
    Start-Process $AppURL
}

exit 0

