# 🖥️ Guide d'Hébergement sur Windows 11

> **Note** : Ce guide est optimisé pour Windows 11. Pour Windows Server 2016, consultez `GUIDE_HEBERGEMENT_WINDOWS_SERVER.md`

## Vue d'ensemble

Ce guide vous explique comment héberger votre application Laravel sur Windows 11 et la rendre accessible en réseau comme une application desktop.

---

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Option 1 : Laragon (Recommandé - Le plus simple)](#option-1--laragon-recommandé---le-plus-simple)
3. [Option 2 : XAMPP](#option-2--xampp)
4. [Option 3 : WAMP](#option-3--wamp)
5. [Configuration réseau](#configuration-réseau)
6. [Application Desktop (Electron/PWA)](#application-desktop-electronpwa)
7. [Sécurité et optimisations](#sécurité-et-optimisations)

---

## 🔧 Prérequis

### Logiciels nécessaires :
- **Windows 11** (toutes versions)
- **PHP 8.2+** (compatible avec Laravel 12)
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js** (pour les assets frontend)
- **Git** (optionnel, pour le déploiement)

### Base de données :
- **MySQL/MariaDB** (inclus dans Laragon/XAMPP/WAMP)
- Ou **SQLite** (plus simple, fichier local)

---

## 🚀 Option 1 : Laragon (Recommandé - Le plus simple)

Laragon est la solution la plus simple et la plus moderne pour Windows 11.

### Étape 1 : Installation de Laragon

1. **Télécharger Laragon** :
   - Aller sur [laragon.org/download](https://laragon.org/download/)
   - Télécharger la version **Full** (recommandée) ou **Lite**
   - Installer dans `C:\laragon` (par défaut)

2. **Lancer Laragon** :
   - Ouvrir Laragon
   - Cliquer sur "Start All" pour démarrer Apache et MySQL

### Étape 2 : Installation de votre application

1. **Copier votre projet** :
   ```powershell
   # Créer le dossier dans Laragon
   New-Item -ItemType Directory -Path "C:\laragon\www\hotelpro" -Force
   
   # Copier tous les fichiers de votre projet vers C:\laragon\www\hotelpro
   ```

2. **Ouvrir un terminal dans Laragon** :
   - Clic droit sur Laragon → Terminal
   - Ou ouvrir PowerShell dans `C:\laragon\www\hotelpro`

3. **Installer les dépendances** :
   ```powershell
   cd C:\laragon\www\hotelpro
   composer install
   npm install
   npm run build
   ```

4. **Configurer .env** :
   ```env
   APP_NAME="Hotel Pro"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://hotelpro.test

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hotelpro
   DB_USERNAME=root
   DB_PASSWORD=

   SESSION_DRIVER=database
   SESSION_LIFETIME=4320
   ```

5. **Créer la base de données** :
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
   - Créer une base de données nommée `hotelpro`

6. **Générer la clé et migrer** :
   ```powershell
   php artisan key:generate
   php artisan migrate
   ```

7. **Accéder à l'application** :
   - Ouvrir : `http://hotelpro.test`
   - Laragon configure automatiquement les virtual hosts

### Avantages de Laragon :
- ✅ Installation en 1 clic
- ✅ SSL local automatique (https://hotelpro.test)
- ✅ phpMyAdmin inclus
- ✅ Gestion simple des bases de données
- ✅ Redémarrage rapide
- ✅ Terminal intégré

---

## 🎯 Option 2 : XAMPP

XAMPP est une alternative populaire et stable.

### Étape 1 : Installation

1. **Télécharger XAMPP** :
   - Aller sur [apachefriends.org](https://www.apachefriends.org/)
   - Télécharger XAMPP pour Windows (PHP 8.2+)
   - Installer dans `C:\xampp`

2. **Démarrer les services** :
   - Ouvrir le Panneau de contrôle XAMPP
   - Démarrer **Apache** et **MySQL**

### Étape 2 : Configuration

1. **Copier votre projet** :
   ```powershell
   # Copier vers le dossier htdocs
   Copy-Item -Path "VOTRE_PROJET\*" -Destination "C:\xampp\htdocs\hotelpro" -Recurse
   ```

2. **Configurer le virtual host** :
   - Ouvrir `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
   - Ajouter :
   ```apache
   <VirtualHost *:80>
       ServerName hotelpro.local
       DocumentRoot "C:/xampp/htdocs/hotelpro/public"
       <Directory "C:/xampp/htdocs/hotelpro/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Modifier le fichier hosts** :
   - Ouvrir `C:\Windows\System32\drivers\etc\hosts` en tant qu'administrateur
   - Ajouter :
   ```
   127.0.0.1    hotelpro.local
   ```

4. **Configurer .env** :
   ```env
   APP_URL=http://hotelpro.local
   DB_HOST=127.0.0.1
   DB_DATABASE=hotelpro
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Redémarrer Apache** et accéder à `http://hotelpro.local`

---

## 🌐 Configuration réseau

### 1. Trouver votre adresse IP locale

```powershell
# Ouvrir PowerShell
ipconfig

# Chercher "Adresse IPv4" (ex: 192.168.1.50)
```

### 2. Configurer le pare-feu Windows

```powershell
# Ouvrir PowerShell en tant qu'administrateur
# Autoriser le port 80 (HTTP)
New-NetFirewallRule -DisplayName "Hotel Pro HTTP" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow

# Ou pour Laragon (port 80 par défaut)
New-NetFirewallRule -DisplayName "Laragon HTTP" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow
```

### 3. Configurer Laragon pour l'accès réseau

1. **Ouvrir Laragon**
2. **Menu → Preferences → General**
3. **Cocher "Allow Virtual Hosts"**
4. **Dans le fichier hosts** (`C:\Windows\System32\drivers\etc\hosts`), ajouter :
   ```
   VOTRE_IP_LOCALE    hotelpro.local
   ```
   Exemple : `192.168.1.50    hotelpro.local`

5. **Redémarrer Laragon**

### 4. Accès depuis le réseau

Les autres ordinateurs du réseau peuvent accéder via :
- `http://VOTRE_IP_LOCALE` (ex: `http://192.168.1.50`)
- `http://hotelpro.local` (si le fichier hosts est configuré sur chaque machine)

### 5. Configuration DNS local (optionnel)

Pour utiliser un nom de domaine sur tout le réseau :
1. Configurer un serveur DNS local (si vous avez un routeur avec DNS)
2. Ou modifier le fichier hosts sur chaque machine cliente

---

## 💻 Application Desktop

### Option A : PWA (Progressive Web App) - Déjà configurée !

Votre application est déjà configurée en PWA. Les utilisateurs peuvent :

1. **Ouvrir l'application** dans Chrome/Edge
2. **Cliquer sur l'icône d'installation** dans la barre d'adresse
3. **Installer l'application**
4. Elle apparaîtra dans le menu Démarrer comme une vraie application

**Avantages** :
- ✅ Pas besoin d'installer quoi que ce soit
- ✅ Fonctionne sur tous les navigateurs modernes
- ✅ Mise à jour automatique
- ✅ Peut fonctionner hors ligne (basique)

### Option B : Application Electron (Application native .exe)

Créer une vraie application Windows .exe qui encapsule votre site.

#### 1. Créer le projet Electron

```powershell
# Créer un nouveau dossier
mkdir C:\hotelpro-desktop
cd C:\hotelpro-desktop

# Initialiser npm
npm init -y

# Installer Electron
npm install electron --save-dev
```

#### 2. Créer `main.js` :

```javascript
const { app, BrowserWindow } = require('electron');
const path = require('path');

let mainWindow;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      webSecurity: true
    },
    icon: path.join(__dirname, 'icon.ico'),
    titleBarStyle: 'default',
    autoHideMenuBar: true,
    show: false // Ne pas afficher avant le chargement
  });

  // Remplacer par votre IP ou URL
  const appUrl = 'http://192.168.1.50'; // Votre IP locale
  // Ou pour développement local : 'http://hotelpro.test'
  
  mainWindow.loadURL(appUrl);
  
  // Afficher la fenêtre une fois chargée
  mainWindow.once('ready-to-show', () => {
    mainWindow.show();
  });

  // Ouvrir les DevTools en développement (optionnel)
  // mainWindow.webContents.openDevTools();

  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  // Gérer les erreurs de connexion
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription) => {
    if (errorCode === -106) {
      mainWindow.loadURL(`file://${path.join(__dirname, 'offline.html')}`);
    }
  });
}

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});
```

#### 3. Créer `offline.html` (pour les erreurs de connexion) :

```html
<!DOCTYPE html>
<html>
<head>
    <title>Hotel Pro - Hors ligne</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #1a4b8c; }
        p { color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Hors ligne</h1>
        <p>Impossible de se connecter au serveur.</p>
        <p>Vérifiez que le serveur est démarré et que vous êtes connecté au réseau.</p>
    </div>
</body>
</html>
```

#### 4. Modifier `package.json` :

```json
{
  "name": "hotelpro-desktop",
  "version": "1.0.0",
  "description": "Hotel Pro - Application Desktop",
  "main": "main.js",
  "scripts": {
    "start": "electron .",
    "build": "electron-builder",
    "build-win": "electron-builder --win"
  },
  "build": {
    "appId": "com.hotelpro.app",
    "productName": "Hotel Pro",
    "win": {
      "target": [
        {
          "target": "nsis",
          "arch": ["x64"]
        }
      ],
      "icon": "icon.ico"
    },
    "nsis": {
      "oneClick": false,
      "allowToChangeInstallationDirectory": true,
      "createDesktopShortcut": true,
      "createStartMenuShortcut": true
    },
    "files": [
      "main.js",
      "offline.html",
      "icon.ico"
    ]
  },
  "devDependencies": {
    "electron": "^latest",
    "electron-builder": "^latest"
  }
}
```

#### 5. Installer electron-builder :

```powershell
npm install electron-builder --save-dev
```

#### 6. Créer l'installateur :

```powershell
npm run build-win
```

Cela créera un fichier `.exe` dans le dossier `dist/` que vous pouvez distribuer.

#### 7. Créer un raccourci automatique

Créer `create-shortcut.ps1` :

```powershell
# Créer un raccourci sur le Bureau
$WshShell = New-Object -ComObject WScript.Shell
$Shortcut = $WshShell.CreateShortcut("$env:USERPROFILE\Desktop\Hotel Pro.lnk")
$Shortcut.TargetPath = "C:\hotelpro-desktop\node_modules\.bin\electron.cmd"
$Shortcut.Arguments = "C:\hotelpro-desktop"
$Shortcut.WorkingDirectory = "C:\hotelpro-desktop"
$Shortcut.IconLocation = "C:\hotelpro-desktop\icon.ico"
$Shortcut.Description = "Hotel Pro - Gestion Hôtelière"
$Shortcut.Save()
```

---

## 🔒 Sécurité et optimisations

### 1. Configuration pour la production locale

Dans `.env` :
```env
APP_ENV=production
APP_DEBUG=false
```

Puis optimiser :
```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 2. Restreindre l'accès par IP (optionnel)

Si vous voulez limiter l'accès au réseau local uniquement :

**Pour Laragon** :
- Modifier `C:\laragon\bin\apache\apache2.4.xx\conf\extra\httpd-vhosts.conf`
- Ajouter des restrictions d'IP dans la configuration du virtual host

**Pour XAMPP** :
- Modifier la configuration Apache de la même manière

### 3. Configuration PHP

Dans `php.ini` (Laragon : `C:\laragon\bin\php\php8.2.x\php.ini`) :

```ini
display_errors = Off
log_errors = On
error_log = C:\laragon\www\hotelpro\storage\logs\php-errors.log
opcache.enable=1
opcache.memory_consumption=128
```

### 4. Planificateur de tâches Windows

Pour exécuter les tâches Laravel automatiquement :

1. Ouvrir le **Planificateur de tâches Windows**
2. Créer une tâche de base
3. Déclencheur : Toutes les minutes
4. Action : Exécuter un programme
   - Programme : `C:\laragon\bin\php\php8.2.x\php.exe`
   - Arguments : `C:\laragon\www\hotelpro\artisan schedule:run`
   - Démarrer dans : `C:\laragon\www\hotelpro`

Ou via PowerShell (en tant qu'administrateur) :

```powershell
$action = New-ScheduledTaskAction -Execute "C:\laragon\bin\php\php8.2.x\php.exe" -Argument "C:\laragon\www\hotelpro\artisan schedule:run" -WorkingDirectory "C:\laragon\www\hotelpro"
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration (New-TimeSpan -Days 365)
Register-ScheduledTask -TaskName "Laravel Scheduler" -Action $action -Trigger $trigger -Description "Exécute les tâches planifiées Laravel"
```

---

## 📦 Déploiement rapide

### Script PowerShell simplifié pour Windows 11

Créer `deploy-windows11.ps1` :

```powershell
# Script de déploiement pour Windows 11 avec Laragon
param(
    [string]$ProjectPath = "C:\laragon\www\hotelpro"
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Déploiement Hotel Pro - Windows 11" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path $ProjectPath)) {
    Write-Host "Création du dossier: $ProjectPath" -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $ProjectPath -Force | Out-Null
}

Set-Location $ProjectPath

Write-Host "[1/4] Installation des dépendances..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader
npm install
npm run build

Write-Host "[2/4] Configuration..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "  Fichier .env créé. VEUILLEZ LE CONFIGURER!" -ForegroundColor Yellow
}

php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "[3/4] Migrations..." -ForegroundColor Yellow
php artisan migrate --force

Write-Host "[4/4] Permissions..." -ForegroundColor Yellow
$acl = Get-Acl $ProjectPath
$permission = "Users", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
$accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule $permission
$acl.SetAccessRule($accessRule)
Set-Acl $ProjectPath $acl

Write-Host ""
Write-Host "✅ Déploiement terminé!" -ForegroundColor Green
Write-Host ""
Write-Host "URL d'accès local: http://hotelpro.test" -ForegroundColor Cyan
$ipAddress = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" }).IPAddress | Select-Object -First 1
if ($ipAddress) {
    Write-Host "URL d'accès réseau: http://$ipAddress" -ForegroundColor Cyan
}
Write-Host ""
```

---

## 🎯 Résumé des étapes rapides

### Avec Laragon (Recommandé) :

1. ✅ Installer Laragon
2. ✅ Copier le projet dans `C:\laragon\www\hotelpro`
3. ✅ Exécuter `composer install` et `npm install`
4. ✅ Configurer `.env`
5. ✅ Créer la base de données dans phpMyAdmin
6. ✅ Exécuter `php artisan migrate`
7. ✅ Accéder à `http://hotelpro.test`
8. ✅ Configurer le pare-feu pour l'accès réseau
9. ✅ Distribuer l'application PWA ou Electron aux clients

---

## 📞 Support

### Vérifier les logs :
- Laravel : `storage/logs/laravel.log`
- Apache (Laragon) : `C:\laragon\bin\apache\apache2.4.xx\logs\error.log`
- PHP : Configuration dans `php.ini`

### Problèmes courants :

**Port 80 déjà utilisé** :
- Arrêter Skype ou autres applications utilisant le port 80
- Ou changer le port dans Laragon/XAMPP

**Accès réseau ne fonctionne pas** :
- Vérifier le pare-feu Windows
- Vérifier que l'IP est correcte
- Vérifier que les machines sont sur le même réseau

**Erreur 500** :
- Vérifier les permissions du dossier `storage`
- Vérifier les logs Laravel
- Vérifier la configuration `.env`

---

**Note** : Pour un environnement de production avec plusieurs utilisateurs simultanés, considérez l'utilisation d'un serveur dédié (Windows Server ou Linux).

