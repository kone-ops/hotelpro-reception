# ✅ Checklist de Déploiement - Hotel Pro v13

## 📋 Vérifications Pré-Déploiement

### ✅ Configuration de base

- [x] **Pages d'erreur personnalisées** (403, 404, 500) créées
- [x] **Handler.php amélioré** avec gestion Spatie et logging
- [x] **Gestion avancée des sessions** avec géolocalisation
- [x] **PWA configurée** (manifest.json, service worker)
- [x] **web.config** pour IIS créé
- [x] **Scripts de déploiement** créés (deploy-windows11.ps1)

### ⚠️ À vérifier avant déploiement

#### 1. Fichier .env
```bash
# Vérifier que ces valeurs sont correctes :
APP_ENV=production          # ⚠️ Changer de 'local' à 'production'
APP_DEBUG=false             # ⚠️ S'assurer que c'est false
APP_URL=http://VOTRE_IP     # ⚠️ Mettre votre IP ou domaine
APP_KEY=base64:...          # ⚠️ Doit être généré

# Base de données
DB_CONNECTION=mysql         # ou sqlite
DB_HOST=127.0.0.1
DB_DATABASE=hotelpro
DB_USERNAME=root
DB_PASSWORD=

# Sessions
SESSION_DRIVER=database
SESSION_LIFETIME=4320
```

#### 2. Optimisations Laravel
```bash
# À exécuter avant le déploiement :
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer install --no-dev --optimize-autoloader
```

#### 3. Permissions des dossiers
```bash
# Vérifier que ces dossiers sont accessibles en écriture :
storage/
storage/logs/
storage/framework/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
bootstrap/cache/
```

#### 4. Base de données
- [ ] Migrations exécutées : `php artisan migrate`
- [ ] Seeders exécutés si nécessaire : `php artisan db:seed`
- [ ] Base de données créée et accessible

#### 5. Assets compilés
```bash
npm install
npm run build
# Vérifier que public/build/ contient les assets compilés
```

#### 6. Sécurité
- [ ] `.env` n'est PAS dans le dépôt Git (vérifier .gitignore)
- [ ] `APP_KEY` est généré et unique
- [ ] `APP_DEBUG=false` en production
- [ ] Mots de passe par défaut changés
- [ ] Pare-feu configuré correctement

#### 7. Serveur Web
- [ ] Serveur web configuré (Laragon/IIS/XAMPP)
- [ ] Virtual host ou site configuré
- [ ] Document root pointe vers `public/`
- [ ] URL Rewrite activé (mod_rewrite pour Apache, URL Rewrite pour IIS)

#### 8. Réseau
- [ ] Pare-feu Windows autorise le port 80 (ou autre)
- [ ] IP du serveur connue
- [ ] Test d'accès depuis un autre ordinateur du réseau

---

## 🚀 Commandes de Déploiement

### Déploiement complet (Windows 11 avec Laragon)

```powershell
# 1. Aller dans le dossier du projet
cd C:\laragon\www\hotelpro

# 2. Exécuter le script de déploiement
.\deploy-windows11.ps1

# 3. Ou manuellement :
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### Vérification post-déploiement

```bash
# Tester l'accès
curl http://localhost
# ou ouvrir dans le navigateur

# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
php artisan about
```

---

## 📝 Fichiers à créer/modifier

### Si .env.example n'existe pas, créer un .env avec :

```env
APP_NAME="Hotel Pro"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://VOTRE_IP

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelpro
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=4320

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

---

## ✅ État Actuel de la v13

### ✅ Déjà prêt :
1. **Pages d'erreur** - 403, 404, 500 avec redirections
2. **Gestion des sessions** - Avancée avec géolocalisation
3. **Handler amélioré** - Logging détaillé des erreurs
4. **PWA** - Manifest et service worker configurés
5. **Scripts de déploiement** - PowerShell prêts
6. **Configuration IIS** - web.config créé
7. **Documentation** - Guides complets créés

### ⚠️ À faire avant déploiement :
1. **Créer .env** depuis .env.example (si n'existe pas)
2. **Configurer APP_URL** avec votre IP/domaine
3. **Générer APP_KEY** : `php artisan key:generate`
4. **Mettre APP_DEBUG=false** en production
5. **Compiler les assets** : `npm run build`
6. **Optimiser Laravel** : caches de config/routes/views
7. **Exécuter les migrations** : `php artisan migrate`
8. **Configurer le serveur web** (Laragon/IIS/XAMPP)
9. **Tester l'accès** depuis le réseau

---

## 🎯 Résumé

**La v13 est PRÊTE pour le déploiement**, mais nécessite :

1. ✅ Configuration du `.env` (APP_URL, APP_DEBUG, etc.)
2. ✅ Compilation des assets (`npm run build`)
3. ✅ Optimisations Laravel (caches)
4. ✅ Configuration du serveur web
5. ✅ Test d'accès réseau

**Temps estimé** : 15-30 minutes pour un déploiement complet

---

## 📞 En cas de problème

1. Vérifier les logs : `storage/logs/laravel.log`
2. Vérifier les permissions des dossiers
3. Vérifier la configuration .env
4. Vérifier que le serveur web est démarré
5. Vérifier le pare-feu Windows

