# 📦 Base de Données Vierge - Guide d'Utilisation

## 🎯 Objectif

Créer une base de données vierge avec uniquement :
- ✅ La structure complète (toutes les tables)
- ✅ Les rôles et permissions de base
- ✅ Un compte super-admin pour la première connexion
- ✅ Les paramètres système essentiels
- ❌ Aucune donnée de test (pas d'hôtels, réservations, etc.)

## 🚀 Création de la Base Vierge

### Commande

```bash
php artisan db:create-empty
```

Cette commande va :
1. Créer un nouveau fichier `database/database_vide.sqlite`
2. Exécuter toutes les migrations pour créer la structure
3. Créer les rôles et permissions essentiels
4. Créer un compte super-admin par défaut
5. Créer les paramètres système de base

### Options

```bash
# Utiliser un nom personnalisé
php artisan db:create-empty --name=ma_base_vierge.sqlite
```

## 📋 Données Incluses dans la Base Vierge

### ✅ Rôles Créés

1. **super-admin** - Administrateur système
   - Toutes les permissions
   - Peut gérer tout le système

2. **hotel-admin** - Administrateur d'hôtel
   - Gestion des utilisateurs de l'hôtel
   - Gestion des formulaires
   - Visualisation et validation des réservations
   - Impression des fiches de police

3. **receptionist** - Réceptionniste
   - Visualisation des réservations
   - Validation des réservations
   - Impression des fiches de police

### ✅ Permissions Créées

- `manage-hotels` - Gérer les hôtels
- `manage-users` - Gérer les utilisateurs
- `manage-forms` - Gérer les formulaires
- `view-reservations` - Voir les réservations
- `validate-reservations` - Valider les réservations
- `print-police-form` - Imprimer les fiches de police

### ✅ Compte Super-Admin

**Informations de connexion par défaut :**
- 📧 Email: `admin@hotelpro.test`
- 🔑 Mot de passe: `password`

⚠️ **IMPORTANT : Changez ce mot de passe immédiatement après la première connexion !**

### ✅ Paramètres Système

Tous les paramètres système essentiels sont créés (notifications, interface, impressions, etc.)

### ❌ Données NON Incluses

- ❌ Aucun hôtel
- ❌ Aucune réservation
- ❌ Aucun utilisateur (sauf le super-admin)
- ❌ Aucune chambre
- ❌ Aucun type de chambre
- ❌ Aucune donnée de test

## 📥 Utilisation dans une Entreprise

### Étape 1 : Créer la Base Vierge

```bash
php artisan db:create-empty
```

### Étape 2 : Copier le Fichier

Copiez `database/database_vide.sqlite` vers votre serveur de production dans le dossier `database/`

### Étape 3 : Configurer .env

Modifiez le fichier `.env` sur le serveur de production :

```env
DB_CONNECTION=sqlite
DB_DATABASE=database_vide.sqlite
```

Ou si vous préférez utiliser un autre nom :

```env
DB_CONNECTION=sqlite
DB_DATABASE=/chemin/complet/vers/database_vide.sqlite
```

### Étape 4 : Vérifier les Permissions

Sur Linux/Mac, assurez-vous que le fichier est accessible en écriture :

```bash
chmod 664 database/database_vide.sqlite
chown www-data:www-data database/database_vide.sqlite  # Adaptez selon votre serveur
```

Sur Windows/WampServer, assurez-vous que le serveur web a les permissions d'écriture.

### Étape 5 : Se Connecter

1. Accédez à l'application
2. Connectez-vous avec :
   - Email: `admin@hotelpro.test`
   - Mot de passe: `password`
3. **Changez immédiatement le mot de passe** dans les paramètres de profil

### Étape 6 : Créer votre Premier Hôtel

1. Connecté en tant que super-admin
2. Allez dans "Hôtels" > "Nouvel hôtel"
3. Créez votre premier hôtel
4. Ajoutez les utilisateurs nécessaires

## 🔧 Maintenance

### Recréer une Base Vierge

Si vous avez besoin de recréer une base vierge :

```bash
# Supprimer l'ancienne (optionnel)
rm database/database_vide.sqlite

# Créer une nouvelle
php artisan db:create-empty
```

### Vérifier le Contenu

Pour vérifier ce qui est dans la base vierge :

```bash
sqlite3 database/database_vide.sqlite

# Dans sqlite3 :
.tables                    # Liste les tables
SELECT COUNT(*) FROM users; # Nombre d'utilisateurs
SELECT COUNT(*) FROM hotels; # Nombre d'hôtels (devrait être 0)
SELECT * FROM roles;       # Liste des rôles
```

## 📊 Structure de la Base

La base vierge contient toutes les tables nécessaires :

- ✅ `users` - 1 utilisateur (super-admin)
- ✅ `roles` - 3 rôles (super-admin, hotel-admin, receptionist)
- ✅ `permissions` - 6 permissions de base
- ✅ `settings` - Paramètres système
- ✅ Toutes les autres tables (vides) : `hotels`, `reservations`, `rooms`, `room_types`, `form_fields`, etc.

## 🔒 Sécurité

1. ⚠️ **Changez le mot de passe du super-admin immédiatement**
2. ⚠️ **Ne partagez jamais le fichier de base avec le mot de passe par défaut**
3. ⚠️ **Surveillez les permissions du fichier SQLite**
4. ⚠️ **Faites des sauvegardes régulières**

## 💡 Astuces

### Exporter la Base Vierge pour Distribution

```bash
# Créer la base
php artisan db:create-empty

# Compresser pour distribution
tar -czf database_vide.tar.gz database/database_vide.sqlite
# ou
zip database_vide.zip database/database_vide.sqlite
```

### Utiliser avec MySQL/PostgreSQL

Si vous préférez utiliser MySQL ou PostgreSQL en production :

1. Créez d'abord la base vierge en SQLite
2. Exportez la structure :
   ```bash
   php artisan schema:dump
   ```
3. Importez dans votre base MySQL/PostgreSQL
4. Exécutez les seeders pour les données essentielles :
   ```bash
   php artisan db:seed --class=RolesAndAdminSeeder
   php artisan db:seed --class=SettingsSeeder
   ```

## 📝 Notes

- La base vierge est **très légère** (quelques centaines de KB)
- Elle est **100% portable** - copiez le fichier et c'est tout
- Parfait pour démarrer une nouvelle installation propre
- Idéal pour la formation de nouveaux utilisateurs
- Utilisable comme template pour de nouveaux projets clients

