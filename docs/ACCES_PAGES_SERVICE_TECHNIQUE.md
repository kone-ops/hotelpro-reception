# Accès aux pages du Service technique (Maintenance)

Ce document décrit **étape par étape** ce qui a été implémenté et **comment accéder** aux pages du module Service technique.

---

## 1. Résumé de l’implémentation

| Élément | Détail |
|--------|--------|
| **Rôle** | `maintenance` (permissions : `view-maintenance`, `update-technical-state`) |
| **Utilisateur de démo** | `technicien@hotelpro.test` / mot de passe : `password` |
| **Préfixe des routes** | `/maintenance` |
| **Contrôleurs** | `App\Modules\Maintenance\Controllers\DashboardController`, `RoomController`, `HistoryController` |
| **Service** | `App\Modules\Maintenance\Services\MaintenanceService` (mise à jour de l’état technique + historique) |
| **Vues** | `resources/views/maintenance/dashboard.blade.php`, `rooms/index.blade.php`, `history/index.blade.php` |

---

## 2. Étapes pour accéder aux pages

### Étape 1 – Créer le rôle et l’utilisateur (une seule fois)

En ligne de commande à la racine du projet :

```bash
cd c:\xampp\htdocs\hotelpro
php artisan db:seed --class=RolesAndAdminSeeder
```

Cela crée (ou met à jour) le rôle `maintenance` et l’utilisateur **technicien@hotelpro.test** (mot de passe : **password**), rattaché à l’hôtel par défaut.

### Étape 2 – Démarrer l’application

```bash
php artisan serve
```

### Étape 3 – Se connecter en tant que technicien

1. Ouvrir dans le navigateur : **http://127.0.0.1:8000** (ou **http://localhost:8000**).
2. Se connecter avec :
   - **Email :** `technicien@hotelpro.test`
   - **Mot de passe :** `password`
3. Après connexion, vous êtes redirigé vers le **tableau de bord du service technique** :  
   **http://127.0.0.1:8000/maintenance**

### Étape 4 – Naviguer dans le module

- **Menu latéral (sidebar)** : section « Service technique » avec :
  - Tableau de bord
  - Chambres (état technique)
  - Historique des interventions
- Vous pouvez aussi ouvrir directement les URLs ci‑dessous.

---

## 3. URLs des pages (accès direct)

| Page | URL | Description |
|------|-----|-------------|
| **Tableau de bord** | **http://127.0.0.1:8000/maintenance** | Statistiques (problème signalé, en maintenance, hors service) et listes des chambres par état. |
| **Chambres (état technique)** | **http://127.0.0.1:8000/maintenance/rooms** | Liste des chambres en *issue* / *maintenance* / *hors service* avec filtres (état, étage). Actions : Remettre en service, Problème, Maintenance, Hors service, ou formulaire avec note. |
| **Historique des interventions** | **http://127.0.0.1:8000/maintenance/history** | Historique des changements d’état technique (table `room_state_history`, type `technical`), avec pagination. |

---

## 4. Comportement des pages

### Tableau de bord (`/maintenance`)

- Affiche le nombre de chambres par état : **Problème signalé**, **En maintenance**, **Hors service**, et le **total**.
- Trois blocs listent les chambres concernées (avec lien « Voir tout » vers la liste filtrée).
- Liens rapides vers « Chambres (état technique) » et « Historique des interventions ».

### Chambres – État technique (`/maintenance/rooms`)

- **Filtres** : état technique (tous / problème signalé / en maintenance / hors service), étage.
- Seules les chambres dont l’état technique est **issue**, **maintenance** ou **out_of_service** sont listées.
- Pour chaque chambre :
  - **Remettre en service** : passe l’état technique à `normal` (chambre à nouveau disponible pour la réservation si occupation et nettoyage le permettent).
  - **Problème** : `issue`
  - **Maintenance** : `maintenance`
  - **Hors service** : `out_of_service`
  - **Ajouter une note** : ouvre un modal pour choisir le nouvel état et saisir une note optionnelle (enregistrée dans l’historique).

Pour qu’une chambre apparaisse dans cette liste, son état technique doit déjà être **issue**, **maintenance** ou **out_of_service** (par exemple après qu’un réceptionniste ou un admin a mis la chambre en « maintenance » depuis l’écran État des chambres de la réception).

### Historique (`/maintenance/history`)

- Liste des enregistrements de la table `room_state_history` avec `state_type = 'technical'`.
- Colonnes : date/heure, chambre, type de service, ancien état, nouvel état, utilisateur, note.
- Pagination (20 par page).
- Option « Service technique uniquement » pour filtrer par `service = maintenance`.

---

## 5. Récapitulatif – Accès rapide

1. **Seeder** : `php artisan db:seed --class=RolesAndAdminSeeder`
2. **Serveur** : `php artisan serve`
3. **Connexion** : **http://127.0.0.1:8000** → `technicien@hotelpro.test` / `password`
4. **Tableau de bord** : **http://127.0.0.1:8000/maintenance**
5. **Chambres** : **http://127.0.0.1:8000/maintenance/rooms**
6. **Historique** : **http://127.0.0.1:8000/maintenance/history**

Seuls les utilisateurs ayant le rôle **maintenance** et un **hotel_id** assigné peuvent accéder à ces pages (middleware `role:maintenance` et `hotel.access`).
