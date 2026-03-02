# Procédures – Base de données : SQLite et phpMyAdmin

Ce document décrit les **procédures uniquement**, sans modification du code du projet. Deux cas : garder **SQLite** ou utiliser **MySQL** avec **phpMyAdmin**.

---

## 1. Rappel : SQLite vs phpMyAdmin

| Option | Base de données | Outil d’administration |
|--------|-----------------|-------------------------|
| **SQLite** | Fichier unique (ex. `database/database.sqlite`) | Pas phpMyAdmin (phpMyAdmin = MySQL/MariaDB). Utiliser un outil type DB Browser for SQLite, ou la ligne de commande. |
| **phpMyAdmin** | MySQL ou MariaDB (XAMPP) | phpMyAdmin dans le navigateur (ex. `http://localhost/phpmyadmin`) |

On ne peut pas « connecter SQLite à phpMyAdmin » : phpMyAdmin gère uniquement MySQL/MariaDB.

---

## 2. Procédure A : Utiliser le projet avec SQLite (sans phpMyAdmin)

### 2.1 Vérifier la configuration

- Fichier : **`.env`** à la racine du projet.
- Vérifier que les lignes suivantes sont présentes (ou les noter pour les remettre si vous changez plus tard) :

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

- Les variables `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` sont ignorées quand `DB_CONNECTION=sqlite`.

### 2.2 Créer la base SQLite si elle n’existe pas

1. Aller dans le dossier **`database/`** du projet.
2. S’il n’y a pas de fichier **`database.sqlite`** :
   - Créer un fichier vide nommé `database.sqlite` dans `database/`,  
   **ou**
   - Exécuter en ligne de commande à la racine du projet :  
     `php artisan db:show` (Laravel peut créer le fichier selon la config),  
     ou lancer les migrations :  
     `php artisan migrate`  
     (Laravel crée le fichier SQLite s’il est configuré ainsi).

### 2.3 Créer / mettre à jour les tables (migrations)

À la racine du projet :

```bash
php artisan migrate
```

Cela crée ou met à jour toutes les tables dans `database/database.sqlite`.

### 2.4 Consulter ou modifier la base SQLite (sans phpMyAdmin)

- **DB Browser for SQLite** : installer l’outil, ouvrir le fichier `database/database.sqlite`.
- **Ligne de commande** :  
  `sqlite3 database/database.sqlite`  
  puis requêtes SQL.
- **Extension VS Code / Cursor** : extension « SQLite » pour ouvrir le fichier `.sqlite`.

Aucune modification de code n’est nécessaire dans le projet pour cette procédure.

---

## 3. Procédure B : Utiliser MySQL avec phpMyAdmin (sans modifier le code applicatif)

Le **code** du projet ne change pas ; seuls le **fichier `.env`** et l’**environnement** (création de la base MySQL) changent.

### 3.1 Démarrer MySQL dans XAMPP

1. Ouvrir le **panneau de contrôle XAMPP**.
2. Démarrer **Apache** et **MySQL**.
3. S’assurer que MySQL est bien « Running ».

### 3.2 Créer la base MySQL avec phpMyAdmin

1. Ouvrir un navigateur et aller sur : **`http://localhost/phpmyadmin`** (ou l’URL de phpMyAdmin fournie par XAMPP).
2. Se connecter (souvent utilisateur **`root`**, mot de passe **vide** sous XAMPP par défaut).
3. Onglet **« Bases de données »** (ou « Databases »).
4. Créer une nouvelle base :
   - Nom proposé : **`hotelpro`** (ou autre nom de votre choix).
   - Interclassement : **`utf8mb4_unicode_ci`** (recommandé pour Laravel).
5. Cliquer sur **« Créer »** (ou « Create »).

La base est vide ; les tables seront créées par Laravel (migrations).

### 3.3 Configurer le projet pour MySQL (uniquement le fichier `.env`)

1. Ouvrir le fichier **`.env`** à la racine du projet.
2. Remplacer ou ajouter les lignes suivantes (adapter le nom de la base si différent de `hotelpro`) :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelpro
DB_USERNAME=root
DB_PASSWORD=
```

- Si MySQL a un mot de passe pour `root`, mettre ce mot de passe dans `DB_PASSWORD=`.
- Si vous utilisez un autre utilisateur MySQL, adapter `DB_USERNAME` et `DB_PASSWORD`.

3. Enregistrer le fichier `.env`.

Aucun autre fichier du projet n’a besoin d’être modifié (la config Laravel lit déjà ces variables dans `config/database.php`).

### 3.4 Créer les tables dans MySQL (migrations)

À la racine du projet, en ligne de commande :

```bash
php artisan migrate
```

Les tables du projet sont créées dans la base **`hotelpro`** (ou celle configurée).

### 3.5 Utiliser phpMyAdmin pour cette base

1. Aller sur **`http://localhost/phpmyadmin`**.
2. Dans la liste de gauche, cliquer sur la base **`hotelpro`** (ou le nom choisi).
3. Vous voyez toutes les tables créées par les migrations.
4. Vous pouvez :
   - parcourir les données (onglet « Parcourir »),
   - exécuter du SQL (onglet « SQL »),
   - exporter / importer la base.

Le projet Laravel et phpMyAdmin utilisent ainsi **la même base MySQL** ; aucune implémentation supplémentaire dans le code n’est nécessaire.

---

## 4. Résumé

| Objectif | Procédure |
|----------|-----------|
| Rester en **SQLite** | Garder `DB_CONNECTION=sqlite` et `DB_DATABASE=database/database.sqlite` dans `.env`, lancer `php artisan migrate`. Consulter la base avec DB Browser for SQLite ou la CLI, **pas** avec phpMyAdmin. |
| Utiliser **phpMyAdmin** | Utiliser **MySQL** : créer une base dans phpMyAdmin, mettre `DB_CONNECTION=mysql` et les paramètres MySQL dans `.env`, lancer `php artisan migrate`. Ensuite, tout se fait dans phpMyAdmin sur cette base. |

Aucune implémentation supplémentaire n’est requise dans le projet : uniquement configuration `.env` et exécution des commandes indiquées.
