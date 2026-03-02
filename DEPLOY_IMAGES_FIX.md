# 🔧 Guide de Résolution - Images non affichées après déploiement

## 📋 Problème

Après déploiement, les images (logos des hôtels, pièces d'identité des clients, etc.) ne s'affichent plus.

## 🔍 Causes possibles

1. **Lien symbolique `storage` manquant ou incorrect**
2. **Permissions incorrectes des fichiers/dossiers**
3. **Configuration `APP_URL` incorrecte dans `.env`**
4. **Serveur web (nginx/apache) ne suit pas les liens symboliques**
5. **Cache de configuration Laravel obsolète**

## ✅ Solution complète

### 1. Exécuter la commande de correction automatique

```bash
php artisan storage:fix-permissions
```

Cette commande :
- ✅ Crée les répertoires nécessaires (`storage/app/public/hotels/logos`, `identity_documents`, etc.)
- ✅ Corrige les permissions (755 pour les dossiers, 644 pour les fichiers)
- ✅ Supprime et recrée le lien symbolique `public/storage`
- ✅ Vérifie que tout fonctionne correctement

### 2. Vérifier la configuration `.env`

Assurez-vous que `APP_URL` est correctement configuré :

```env
APP_URL=http://votre-domaine.com
# ou
APP_URL=http://192.168.1.100
# ou pour le serveur de développement local
APP_URL=http://127.0.0.1:8000
```

**⚠️ Important :** Ne pas mettre de slash `/` à la fin de `APP_URL`.

### 3. Vider les caches Laravel

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 4. Vérifier les permissions du serveur web

#### Pour Apache

Vérifier que le fichier `.htaccess` dans `public/` contient :

```apache
Options +FollowSymLinks
```

Ou dans la configuration Apache :

```apache
<Directory /chemin/vers/votre/projet/public>
    Options +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

#### Pour Nginx

Vérifier que la configuration nginx suit les liens symboliques (c'est généralement le cas par défaut).

Si nécessaire, ajouter dans la configuration :

```nginx
location /storage {
    alias /chemin/vers/votre/projet/storage/app/public;
    try_files $uri $uri/ =404;
}
```

### 5. Vérifier manuellement le lien symbolique

```bash
# Vérifier que le lien existe
ls -la public/storage

# Devrait afficher quelque chose comme :
# lrwxrwxrwx ... public/storage -> /chemin/vers/storage/app/public

# Vérifier que le lien fonctionne
test -L public/storage && echo "Le lien existe" || echo "Le lien n'existe pas"
readlink -f public/storage
```

### 6. Vérifier que les fichiers existent

```bash
# Vérifier les logos des hôtels
ls -la storage/app/public/hotels/logos/

# Vérifier les documents d'identité
ls -la storage/app/public/identity_documents/
```

### 7. Tester l'accès direct

Essayez d'accéder directement à une image via l'URL :

```
http://votre-domaine.com/storage/hotels/logos/nom_du_fichier.jpg
```

Si cela fonctionne, le problème vient peut-être du code. Si cela ne fonctionne pas, c'est un problème de configuration du serveur web.

## 🔄 Solution permanente

Le code a été amélioré pour être plus robuste :

1. **`app/Models/Hotel.php`** : Utilise `Storage::url()` avec fallback sur `asset()`
2. **`app/Models/IdentityDocument.php`** : Même amélioration pour les pièces d'identité
3. **`app/Services/DocumentService.php`** : Méthode `getFileUrl()` améliorée
4. **Commande `storage:fix-permissions`** : Créée pour automatiser la correction

## 🚀 Checklist après déploiement

- [ ] Exécuter `php artisan storage:fix-permissions`
- [ ] Vérifier `APP_URL` dans `.env`
- [ ] Vider tous les caches Laravel
- [ ] Vérifier que le lien symbolique `public/storage` existe et pointe vers `storage/app/public`
- [ ] Vérifier les permissions (755 pour dossiers, 644 pour fichiers)
- [ ] Tester l'accès direct à une image via URL
- [ ] Vérifier la configuration du serveur web (FollowSymLinks pour Apache)

## 📝 Notes importantes

- **Permissions recommandées** :
  - Dossiers : `755` (rwxr-xr-x)
  - Fichiers : `644` (rw-r--r--)
  - Propriétaire : généralement l'utilisateur du serveur web (www-data, apache, nginx)

- **En cas de problème persistant** :
  1. Vérifier les logs du serveur web (Apache : `/var/log/apache2/error.log`, Nginx : `/var/log/nginx/error.log`)
  2. Vérifier les logs Laravel : `storage/logs/laravel.log`
  3. Tester avec `php artisan serve` pour isoler le problème (serveur web vs Laravel)

