# 🔧 Guide de Déploiement WampServer - Correction des Chemins d'Images

## ✅ Vérifications Effectuées

### 1. Structure des dossiers ✅

**Dossiers statiques dans `public/` :**
- ✅ `public/assets/` - Assets vendor (Bootstrap, DataTables, etc.)
- ✅ `public/css/` - Fichiers CSS personnalisés
- ✅ `public/js/` - Fichiers JavaScript personnalisés
- ✅ `public/Template/logo.jpg` et `logo.png` - Logos statiques
- ✅ `public/storage` - Lien symbolique vers `storage/app/public` (CRÉÉ)

**Dossiers de stockage dans `storage/app/public/` :**
- ✅ `storage/app/public/hotels/logos/` - Logos des hôtels (22 fichiers trouvés)
- ✅ `storage/app/public/identity_documents/` - Pièces d'identité (28 fichiers trouvés)

### 2. Utilisation de `asset()` ✅

Tous les fichiers Blade utilisent correctement `asset()` pour les ressources statiques :

**✅ Fichiers vérifiés et corrects :**
- `resources/views/layouts/app.blade.php` - Utilise `asset()` pour tous les CSS/JS
- `resources/views/layouts/sidebar.blade.php` - Utilise `asset('Template/logo.jpg')`
- `resources/views/auth/login.blade.php` - Utilise `asset('Template/logo.jpg')`
- `resources/views/public/form.blade.php` - Utilise `$hotel->logo_url` (accessor)
- `resources/views/super/hotels/show.blade.php` - Utilise `$hotel->logo_url`
- `resources/views/reception/reservations/show.blade.php` - Utilise `asset('storage/...')`
- `resources/views/super/reservations/show.blade.php` - Utilise `asset('storage/...')`

### 3. Modèles avec Accessors ✅

**`app/Models/Hotel.php` :**
- ✅ Méthode `getLogoUrlAttribute()` utilise `Storage::url()` avec fallback sur `asset('storage/' . $this->logo)`
- ✅ Méthode `hasLogo()` vérifie l'existence du fichier

**`app/Models/IdentityDocument.php` :**
- ✅ `getFrontUrlAttribute()` et `getBackUrlAttribute()` utilisent `asset('storage/...')` avec vérification

**`app/Services/DocumentService.php` :**
- ✅ `getFileUrl()` utilise `Storage::url()` avec fallback sur `asset()`

### 4. Fichiers PDF (Cas particulier) ✅

**`resources/views/reception/police-sheet/pdf.blade.php` :**
- ✅ Utilise `storage_path('app/public/' . $logo)` pour convertir en base64
- **C'est correct** car les PDFs sont générés côté serveur et nécessitent des chemins système, pas des URLs

### 5. Lien Symbolique Storage ✅

Le lien symbolique `public/storage` est créé et pointe correctement vers `storage/app/public` :
```bash
public/storage -> /media/bachir/King_s Files/hotelpro-reception-main1/storage/app/public
```

## 🔧 Commandes à Exécuter après Déploiement

### Étape 1 : Corriger les permissions et le lien storage
```bash
php artisan storage:fix-permissions
```

Cette commande :
- Crée les répertoires nécessaires avec les bonnes permissions (755)
- Corrige les permissions des fichiers (644)
- Recrée le lien symbolique `public/storage`
- Vérifie que tout fonctionne

### Étape 2 : Nettoyer les caches Laravel
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Étape 3 : Vérifier la configuration `.env`
Assurez-vous que `APP_URL` est correctement configuré :
```env
APP_URL=http://votre-domaine.local
# ou
APP_URL=http://192.168.1.100
```

**⚠️ Important :** Ne pas mettre de slash `/` à la fin de `APP_URL`.

## 🔍 Configuration Apache pour WampServer

### Vérification du VirtualHost

Votre VirtualHost doit pointer vers le dossier `public/` :

```apache
<VirtualHost *:80>
    ServerName votre-domaine.local
    DocumentRoot "C:/wamp64/www/hotelpro-reception-main1/public"
    
    <Directory "C:/wamp64/www/hotelpro-reception-main1/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/hotelpro-error.log"
    CustomLog "logs/hotelpro-access.log" common
</VirtualHost>
```

**Points importants :**
1. ✅ `DocumentRoot` pointe vers le dossier `public/`
2. ✅ `Options FollowSymLinks` permet de suivre les liens symboliques
3. ✅ `AllowOverride All` permet l'utilisation du `.htaccess`

### Vérification du fichier `.htaccess`

Le fichier `public/.htaccess` doit contenir :
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 📋 Résumé des Chemins Utilisés

### Images Statiques (dans `public/`)
- Logo par défaut : `asset('Template/logo.jpg')`
- Assets vendor : `asset('assets/vendor/...')`
- CSS personnalisés : `asset('css/...')`
- JS personnalisés : `asset('js/...')`

### Images Dynamiques (dans `storage/app/public/`)
- Logos des hôtels : `$hotel->logo_url` (utilise `asset('storage/hotels/logos/...')`)
- Pièces d'identité : `$identityDocument->front_url` ou `back_url` (utilise `asset('storage/identity_documents/...')`)

### Cas Particuliers
- **PDFs** : Utilisent `storage_path()` pour convertir en base64 (correct)
- **Signatures** : Stockées en base64 dans la base de données (pas de fichier)

## ✅ Checklist Post-Déploiement

- [ ] Exécuter `php artisan storage:fix-permissions`
- [ ] Vider tous les caches Laravel
- [ ] Vérifier `APP_URL` dans `.env` (sans slash final)
- [ ] Vérifier que le VirtualHost pointe vers `public/`
- [ ] Vérifier que `Options FollowSymLinks` est activé dans Apache
- [ ] Tester l'accès direct à une image : `http://votre-domaine.local/storage/hotels/logos/fichier.jpg`
- [ ] Vérifier les logs Apache en cas d'erreur 404

## 🐛 Dépannage

### Si les images ne s'affichent toujours pas :

1. **Vérifier le lien symbolique :**
   ```bash
   dir public\storage
   # Doit afficher : public\storage [<SYMLINK>]
   ```

2. **Vérifier les permissions Windows :**
   - Le dossier `storage/app/public` doit être accessible en lecture
   - Le lien symbolique `public/storage` doit fonctionner

3. **Vérifier les logs Apache :**
   - Regarder `C:\wamp64\logs\apache_error.log`
   - Chercher les erreurs 404 pour les images

4. **Tester l'accès direct :**
   ```
   http://votre-domaine.local/storage/hotels/logos/nom_du_fichier.jpg
   ```

5. **Vérifier le module mod_rewrite :**
   Dans WampServer, assurez-vous que `mod_rewrite` est activé.

## 📝 Fichiers Modifiés

Aucun fichier n'a nécessité de modification car tous les chemins utilisent déjà `asset()` correctement.

Les seules améliorations apportées sont :
- ✅ Commande `storage:fix-permissions` pour automatiser la correction
- ✅ Amélioration des accessors dans les modèles pour plus de robustesse
- ✅ Guide de déploiement complet

## 🎯 Résultat Attendu

Après ces vérifications et corrections :
- ✅ Toutes les images statiques s'affichent correctement
- ✅ Les logos des hôtels s'affichent via `storage/app/public/hotels/logos/`
- ✅ Les pièces d'identité s'affichent via `storage/app/public/identity_documents/`
- ✅ Aucune erreur 404 pour les ressources
- ✅ L'application fonctionne correctement sur WampServer/Apache

