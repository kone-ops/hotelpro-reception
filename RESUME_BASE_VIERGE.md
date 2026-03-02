# 📦 Résumé - Base de Données Vierge

## ✅ Commande Créée

**Fichier :** `app/Console/Commands/CreateEmptyDatabase.php`

**Commande :**
```bash
php artisan db:create-empty
```

## 🎯 Objectif

Créer une base de données vierge (`database_vide.sqlite`) avec :
- ✅ Structure complète (toutes les tables)
- ✅ Rôles et permissions (super-admin, hotel-admin, receptionist)
- ✅ Un compte super-admin (`admin@hotelpro.test` / `password`)
- ✅ Paramètres système essentiels
- ❌ **Aucune donnée de test** (pas d'hôtels, réservations, etc.)

## ⚠️ Note Importante

Il y a un conflit dans les migrations concernant la table `reservations`. Si la commande échoue avec une erreur de migration, vous pouvez :

### Option 1 : Utiliser la Base Actuelle

Si vous avez déjà une base de données fonctionnelle, vous pouvez simplement :

1. Supprimer toutes les données (garder la structure) :
   ```bash
   php artisan tinker
   ```
   Puis dans tinker :
   ```php
   DB::table('hotels')->delete();
   DB::table('reservations')->delete();
   DB::table('users')->where('email', '!=', 'admin@hotelpro.test')->delete();
   // etc.
   ```

2. Ou exporter uniquement la structure :
   ```bash
   sqlite3 database/database.sqlite .schema > schema.sql
   ```

### Option 2 : Corriger la Migration

Le problème vient de `database/migrations/2025_11_08_000001_rename_prereservations_to_reservations.php` qui essaie de renommer une table qui peut déjà exister.

Vous pouvez commenter cette migration temporairement ou la corriger pour vérifier l'existence des tables avant de les renommer.

### Option 3 : Utiliser migrate:fresh + Seeders

```bash
# Créer une base vierge manuellement
touch database/database_vide.sqlite

# Configurer temporairement .env pour utiliser database_vide.sqlite
# Puis :
php artisan migrate:fresh --database=sqlite
php artisan db:seed --class=RolesAndAdminSeeder
php artisan db:seed --class=SettingsSeeder
```

## 📋 Données Incluses dans la Base Vierge

- **1 utilisateur** : Super Admin (`admin@hotelpro.test` / `password`)
- **3 rôles** : super-admin, hotel-admin, receptionist
- **6 permissions** : manage-hotels, manage-users, manage-forms, view-reservations, validate-reservations, print-police-form
- **Paramètres système** : Tous les paramètres essentiels
- **0 hôtels** : Aucun hôtel de test
- **0 réservations** : Aucune réservation de test

## 🚀 Utilisation

Une fois la base créée (manuellement ou via la commande) :

1. **Copier le fichier** `database/database_vide.sqlite` vers votre serveur
2. **Configurer `.env`** :
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database_vide.sqlite
   ```
3. **Se connecter** avec `admin@hotelpro.test` / `password`
4. **Changer le mot de passe immédiatement**

## 📝 Documentation Complète

Voir `README_DATABASE_VIERGE.md` pour la documentation complète.

