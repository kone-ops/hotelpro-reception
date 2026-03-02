# Documentation : Configuration QR Code avec Domaine Local

## Vue d'ensemble

Le système de QR code a été amélioré pour permettre à chaque hôtel de configurer son propre QR code avec soit :
- **Un nom de domaine local** (ex: `reservation.local`, `hotel.local`)
- **L'IP du serveur détectée automatiquement** (Wi-Fi ou Ethernet)

## Fonctionnalités

### 1. Configuration par hôtel

Chaque hôtel peut maintenant :
- Activer/désactiver l'utilisation d'un domaine local
- Configurer son propre nom de domaine
- Utiliser automatiquement l'IP du serveur si aucun domaine n'est configuré

### 2. Détection automatique de l'IP

Le système détecte automatiquement l'IP du serveur en utilisant plusieurs méthodes :
1. `gethostbyname(gethostname())` - Méthode PHP standard
2. Commande `ip -4 addr show` (Linux moderne)
3. Commande `ifconfig` (Linux/Unix)
4. Variable `$_SERVER['SERVER_ADDR']`
5. Fallback vers `192.168.1.100` si aucune IP n'est détectée

### 3. Logs détaillés

Tous les événements sont loggés dans `storage/logs/laravel.log` :
- Détection d'IP
- Génération d'URL avec domaine
- Génération d'URL avec IP automatique
- Erreurs de détection

## Utilisation

### Configuration d'un hôtel

1. **Accéder à la gestion des hôtels** (SuperAdmin)
2. **Créer ou modifier un hôtel**
3. **Dans la section "Configuration QR Code"** :
   - Cocher "Utiliser un nom de domaine local" si vous avez un domaine configuré
   - Entrer le nom de domaine (ex: `reservation.local`)
   - Laisser décoché pour utiliser l'IP automatique

### Exemples de configuration

#### Option 1 : Utiliser un domaine local
```
✅ Utiliser un nom de domaine local
Nom de domaine: reservation.local
```
→ QR Code généré : `http://reservation.local/f/1`

#### Option 2 : Utiliser l'IP automatique
```
❌ Utiliser un nom de domaine local (décoché)
```
→ QR Code généré : `http://192.168.1.100/f/1` (IP détectée automatiquement)

## Configuration du domaine local

### Sur Windows (WampServer)

1. **Modifier le fichier hosts** :
   - Chemin : `C:\Windows\System32\drivers\etc\hosts`
   - Ajouter : `192.168.1.100  reservation.local`
   - (Remplacer `192.168.1.100` par l'IP de votre serveur)

2. **Configurer Apache VirtualHost** :
   ```apache
   <VirtualHost *:80>
       ServerName reservation.local
       DocumentRoot "C:/wamp64/www/hotelpro-reception-main1/public"
       <Directory "C:/wamp64/www/hotelpro-reception-main1/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Redémarrer Apache**

### Sur Linux

1. **Modifier le fichier hosts** :
   ```bash
   sudo nano /etc/hosts
   # Ajouter : 192.168.1.100  reservation.local
   ```

2. **Configurer Apache/Nginx** :
   - Créer un VirtualHost pointant vers `public/`
   - Activer le site
   - Redémarrer le serveur web

## Vérification

### Tester la détection d'IP

Consultez les logs :
```bash
tail -f storage/logs/laravel.log | grep "IP serveur"
```

### Tester le QR code

1. Générer le QR code pour un hôtel
2. Scanner avec un smartphone
3. Vérifier que l'URL fonctionne
4. Vérifier que le formulaire s'affiche correctement

## Fichiers modifiés

### Base de données
- **Migration** : `database/migrations/2025_12_24_153838_add_domain_fields_to_hotels_table.php`
- **Champs ajoutés** :
  - `use_domain` (boolean, default: false)
  - `domain_name` (string, nullable)

### Modèles
- **app/Models/Hotel.php** : Ajout de `use_domain` et `domain_name` dans `$fillable` et `$casts`

### Contrôleurs
- **app/Http/Controllers/HotelAdmin/QrController.php** :
  - Méthode `generateFormUrl()` : Génère l'URL selon la configuration
  - Méthode `getServerIp()` : Détecte l'IP du serveur

- **app/Http/Controllers/SuperAdmin/HotelController.php** :
  - Méthode `generateFormUrl()` : Génère l'URL selon la configuration
  - Méthode `getServerIp()` : Détecte l'IP du serveur
  - Validation mise à jour pour `use_domain` et `domain_name`

- **app/Http/Controllers/PrintSelectionController.php** :
  - Méthode `generateFormUrl()` : Génère l'URL selon la configuration
  - Méthode `getServerIp()` : Détecte l'IP du serveur

### Vues
- **resources/views/super/hotels/index.blade.php** :
  - Section "Configuration QR Code" ajoutée dans les modals de création et modification
  - JavaScript pour toggle du champ domaine

## Compatibilité

✅ **Compatible avec** :
- Wi-Fi et Ethernet simultanés
- Changement d'IP automatique
- Réseau local (LAN)
- Tous les appareils du réseau

✅ **Aucune régression** :
- Les formulaires existants continuent de fonctionner
- L'authentification reste inchangée
- Le front-end reste compatible

## Dépannage

### Le QR code ne fonctionne pas

1. **Vérifier les logs** : `storage/logs/laravel.log`
2. **Vérifier la configuration** : L'hôtel a-t-il `use_domain` activé ?
3. **Tester l'URL manuellement** : Ouvrir l'URL dans un navigateur
4. **Vérifier la détection d'IP** : Consulter les logs pour voir quelle IP est détectée

### L'IP n'est pas détectée correctement

1. **Vérifier les permissions** : La commande `exec()` doit être autorisée
2. **Vérifier les interfaces réseau** : `ip addr show` ou `ifconfig`
3. **Utiliser un domaine local** : Plus fiable que l'IP automatique

### Le domaine local ne fonctionne pas

1. **Vérifier le fichier hosts** : Le domaine pointe-t-il vers la bonne IP ?
2. **Vérifier Apache/Nginx** : Le VirtualHost est-il configuré ?
3. **Tester avec ping** : `ping reservation.local` doit répondre
4. **Vérifier les permissions** : Le serveur web peut-il accéder au dossier `public/` ?

## Notes importantes

- ⚠️ **Le domaine doit être configuré sur TOUS les appareils** qui doivent accéder au formulaire
- ⚠️ **L'IP automatique peut changer** si le serveur change de réseau
- ✅ **Le domaine local est plus stable** et recommandé pour un déploiement en production
- ✅ **Les logs sont essentiels** pour déboguer les problèmes de détection d'IP

