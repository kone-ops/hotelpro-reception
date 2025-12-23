# 🖥️ Guide d'Hébergement sur Windows Server 2016

## Vue d'ensemble

Ce guide vous explique comment héberger votre application Laravel sur Windows Server 2016 et la rendre accessible en réseau comme une application desktop.

---

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Option 1 : IIS avec PHP Manager](#option-1--iis-avec-php-manager)
3. [Option 2 : Laragon (Recommandé pour développement/test)](#option-2--laragon-recommandé-pour-développementtest)
4. [Option 3 : XAMPP/WAMP](#option-3--xamppwamp)
5. [Configuration réseau](#configuration-réseau)
6. [Application Desktop (Electron/PWA)](#application-desktop-electronpwa)
7. [Sécurité et optimisations](#sécurité-et-optimisations)

---

## 🔧 Prérequis

### Logiciels nécessaires :
- **Windows Server 2016** (Standard ou Datacenter)
- **PHP 8.2+** (compatible avec Laravel 12)
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js** (pour les assets frontend)
- **Git** (optionnel, pour le déploiement)

### Services Windows :
- **IIS (Internet Information Services)** ou serveur web alternatif
- **SQL Server** ou **MySQL/MariaDB** pour la base de données

---

## 🚀 Option 1 : IIS avec PHP Manager (Production)

### Étape 1 : Installation d'IIS

```powershell
# Ouvrir PowerShell en tant qu'administrateur
Install-WindowsFeature -name Web-Server -IncludeManagementTools
Install-WindowsFeature -name Web-Mgmt-Console
```

### Étape 2 : Installation de PHP

1. Télécharger PHP 8.2+ depuis [windows.php.net](https://windows.php.net/download/)
2. Extraire dans `C:\PHP`
3. Copier `php.ini-production` vers `php.ini`
4. Configurer `php.ini` :

```ini
extension_dir = "ext"
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
extension=zip

memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

### Étape 3 : Installation de PHP Manager pour IIS

1. Télécharger depuis : [phpmanager.codeplex.com](https://phpmanager.codeplex.com/)
2. Installer et configurer PHP dans IIS

### Étape 4 : Installation de Composer

```powershell
# Télécharger et installer Composer
Invoke-WebRequest -Uri https://getcomposer.org/Composer-Setup.exe -OutFile Composer-Setup.exe
.\Composer-Setup.exe
```

### Étape 5 : Configuration du site IIS

1. **Créer le site dans IIS** :
   - Ouvrir IIS Manager
   - Clic droit sur "Sites" → "Ajouter un site web"
   - Nom : `HotelPro`
   - Chemin physique : `C:\inetpub\wwwroot\hotelpro-reception-main`
   - Port : `80` (ou `8080` pour éviter les conflits)

2. **Configurer les permissions** :
   ```powershell
   # Donner les permissions à IIS_IUSRS
   icacls "C:\inetpub\wwwroot\hotelpro-reception-main" /grant "IIS_IUSRS:(OI)(CI)F" /T
   ```

3. **Configurer web.config** (créer dans le dossier `public`) :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <defaultDocument>
            <files>
                <clear />
                <add value="index.php" />
            </files>
        </defaultDocument>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)/$" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Redirect" redirectType="Permanent" url="/{R:1}" />
                </rule>
                <rule name="Imported Rule 2" stopProcessing="true">
                    <match url="^" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
        <httpErrors errorMode="Detailed" />
        <directoryBrowse enabled="false" />
    </system.webServer>
</configuration>
```

### Étape 6 : Configuration de Laravel

1. **Copier les fichiers** de votre projet vers `C:\inetpub\wwwroot\hotelpro-reception-main`

2. **Installer les dépendances** :
   ```powershell
   cd C:\inetpub\wwwroot\hotelpro-reception-main
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

3. **Configurer .env** :
   ```env
   APP_NAME="Hotel Pro"
   APP_ENV=production
   APP_KEY=base64:VOTRE_CLE_GENEREE
   APP_DEBUG=false
   APP_URL=http://VOTRE_IP_SERVEUR:80

   DB_CONNECTION=sqlite
   DB_DATABASE=C:\inetpub\wwwroot\hotelpro-reception-main\database\database.sqlite

   SESSION_DRIVER=database
   SESSION_LIFETIME=4320
   ```

4. **Générer la clé d'application** :
   ```powershell
   php artisan key:generate
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 🎯 Option 2 : Laragon (Recommandé pour développement/test)

Laragon est plus simple à configurer et idéal pour un environnement de test.

### Installation :

1. Télécharger Laragon depuis [laragon.org](https://laragon.org/download/)
2. Installer avec PHP 8.2+, MySQL, Composer inclus
3. Copier votre projet dans `C:\laragon\www\hotelpro`
4. Configurer comme ci-dessus

**Avantages** :
- ✅ Configuration automatique
- ✅ SSL local inclus
- ✅ Gestion simple des bases de données
- ✅ Redémarrage rapide

---

## 🌐 Configuration réseau

### 1. Configurer le pare-feu Windows

```powershell
# Autoriser le port 80 (HTTP)
New-NetFirewallRule -DisplayName "Hotel Pro HTTP" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow

# Ou port 8080 si vous utilisez ce port
New-NetFirewallRule -DisplayName "Hotel Pro HTTP" -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow
```

### 2. Obtenir l'adresse IP du serveur

```powershell
# Afficher l'adresse IP
ipconfig
```

### 3. Accès depuis le réseau

Les clients peuvent accéder via :
- `http://IP_SERVEUR:80` (ex: `http://192.168.1.100:80`)
- `http://NOM_SERVEUR:80` (si DNS configuré)

### 4. Configuration DNS (optionnel)

Pour utiliser un nom de domaine local :
1. Configurer DNS sur le serveur ou le contrôleur de domaine
2. Créer une entrée A pointant vers l'IP du serveur
3. Accès via : `http://hotelpro.local` ou `http://hotelpro.votredomaine.local`

---

## 💻 Application Desktop (Electron/PWA)

### Option A : Application Electron (Vraie application desktop)

Créer une application Electron qui encapsule votre site web.

#### 1. Créer le projet Electron

```bash
mkdir hotelpro-desktop
cd hotelpro-desktop
npm init -y
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
    icon: path.join(__dirname, 'icon.png'), // Optionnel
    titleBarStyle: 'default',
    autoHideMenuBar: true // Masquer la barre de menu
  });

  // Charger votre application Laravel
  mainWindow.loadURL('http://VOTRE_IP_SERVEUR:80');
  
  // Ouvrir les DevTools en développement (optionnel)
  // mainWindow.webContents.openDevTools();

  mainWindow.on('closed', () => {
    mainWindow = null;
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

#### 3. Modifier `package.json` :

```json
{
  "name": "hotelpro-desktop",
  "version": "1.0.0",
  "main": "main.js",
  "scripts": {
    "start": "electron .",
    "build": "electron-builder"
  },
  "build": {
    "appId": "com.hotelpro.app",
    "win": {
      "target": "nsis",
      "icon": "icon.ico"
    },
    "nsis": {
      "oneClick": false,
      "allowToChangeInstallationDirectory": true
    }
  }
}
```

#### 4. Installer electron-builder :

```bash
npm install electron-builder --save-dev
```

#### 5. Créer l'installateur :

```bash
npm run build
```

Cela créera un fichier `.exe` installable que vous pouvez distribuer aux clients.

### Option B : PWA (Progressive Web App)

Transformer votre application en PWA pour qu'elle puisse être installée comme une application.

#### 1. Créer `public/manifest.json` :

```json
{
  "name": "Hotel Pro - Gestion Hôtelière",
  "short_name": "Hotel Pro",
  "description": "Système de gestion hôtelière professionnel",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#1a4b8c",
  "theme_color": "#1a4b8c",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/Template/logo.jpg",
      "sizes": "192x192",
      "type": "image/jpeg"
    },
    {
      "src": "/Template/logo.jpg",
      "sizes": "512x512",
      "type": "image/jpeg"
    }
  ]
}
```

#### 2. Ajouter dans `resources/views/layouts/app.blade.php` (dans `<head>`) :

```html
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#1a4b8c">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
```

#### 3. Créer un Service Worker (`public/sw.js`) :

```javascript
const CACHE_NAME = 'hotelpro-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        return response || fetch(event.request);
      })
  );
});
```

#### 4. Enregistrer le Service Worker dans votre layout :

```html
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW registered: ', registration);
      })
      .catch((registrationError) => {
        console.log('SW registration failed: ', registrationError);
      });
  });
}
</script>
```

**Utilisation** :
- Les utilisateurs peuvent installer l'application depuis le navigateur
- Elle apparaîtra comme une application dans le menu Démarrer
- Fonctionne hors ligne (basique)

---

## 🔒 Sécurité et optimisations

### 1. Configuration HTTPS (Recommandé)

Pour la production, utilisez HTTPS :

```powershell
# Installer le certificat SSL (Let's Encrypt avec win-acme)
# Ou utiliser un certificat d'entreprise
```

### 2. Restreindre l'accès par IP (optionnel)

Dans IIS, configurer les restrictions d'adresse IP pour limiter l'accès au réseau local.

### 3. Optimisations Laravel

```powershell
# Mettre en cache les configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimiser l'autoloader
composer install --optimize-autoloader --no-dev
```

### 4. Configuration PHP pour la production

Dans `php.ini` :
```ini
display_errors = Off
log_errors = On
error_log = C:\inetpub\wwwroot\hotelpro-reception-main\storage\logs\php-errors.log
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 5. Planificateur de tâches Windows

Pour les tâches Laravel (cron), créer une tâche planifiée :

```powershell
# Créer une tâche qui exécute toutes les minutes
schtasks /create /tn "Laravel Scheduler" /tr "php C:\inetpub\wwwroot\hotelpro-reception-main\artisan schedule:run" /sc minute /mo 1
```

Ou modifier `app/Console/Kernel.php` pour utiliser une commande Windows :

```php
protected function schedule(Schedule $schedule)
{
    // Vos tâches planifiées
}
```

---

## 📦 Déploiement automatisé

### Script PowerShell de déploiement

Créer `deploy.ps1` :

```powershell
# Script de déploiement
$projectPath = "C:\inetpub\wwwroot\hotelpro-reception-main"

Write-Host "Déploiement en cours..."

# Aller dans le dossier du projet
Set-Location $projectPath

# Mettre à jour depuis Git (si utilisé)
# git pull origin main

# Installer les dépendances
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

Write-Host "Déploiement terminé !"
```

---

## 🎯 Résumé des étapes rapides

1. **Installer IIS et PHP** sur Windows Server 2016
2. **Configurer le site** dans IIS
3. **Déployer l'application** Laravel
4. **Configurer le pare-feu** pour autoriser l'accès réseau
5. **Créer l'application desktop** (Electron ou PWA)
6. **Distribuer** l'application aux clients

---

## 📞 Support

En cas de problème :
- Vérifier les logs : `storage/logs/laravel.log`
- Vérifier les logs IIS : `C:\inetpub\logs\LogFiles`
- Vérifier les logs PHP : Configuration dans `php.ini`

---

**Note** : Pour un environnement de production critique, considérez l'utilisation d'un serveur Linux avec Nginx/Apache, qui est généralement plus performant et plus sécurisé pour les applications Laravel.

