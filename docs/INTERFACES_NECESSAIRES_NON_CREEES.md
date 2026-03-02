# Interfaces nécessaires non encore créées (ou à adapter)

Ce document liste les **interfaces (vues) nécessaires** qui **ne sont pas encore créées** dans le projet, ou qui sont **à adapter** après des changements (ex. suppression du module imprimantes).

---

## 1. Interfaces non encore créées

### 1.1 Module Service technique (Maintenance)

Le module **Maintenance** est décrit dans `docs/INTEGRATION_SERVICE_TECHNIQUE.md` mais **aucune vue n’existe** pour l’instant. Les interfaces à créer sont :

| Vue à créer | Rôle | Contrôleur / route prévus |
|-------------|------|----------------------------|
| `resources/views/maintenance/dashboard.blade.php` | Tableau de bord : chambres en issue / maintenance / hors service, statistiques | `Maintenance\Controllers\DashboardController@index` → route `maintenance.dashboard` |
| `resources/views/maintenance/rooms/index.blade.php` | Liste des chambres par état technique, actions (signaler problème, mettre en maintenance, remettre en service) | `Maintenance\Controllers\RoomController@index` → route `maintenance.rooms.index` |
| `resources/views/maintenance/history/index.blade.php` | *(Optionnel)* Historique des interventions techniques | `Maintenance\Controllers\HistoryController@index` → route `maintenance.history.index` |

**Résumé :**  
- **Obligatoires pour le module :** `maintenance/dashboard.blade.php`, `maintenance/rooms/index.blade.php`.  
- **Optionnel :** `maintenance/history/index.blade.php` (si vous ajoutez un historique des interventions).

---

## 2. Interfaces existantes mais à adapter (optionnel)

### 2.1 Super Admin – Détails données hôtel (`super/hotel-data/show.blade.php`)

- **Constat :** La vue affiche une section **« Imprimantes configurées »** (`$printers`) et des **statistiques imprimantes** (`$stats['printers']`). Le modèle et les contrôleurs liés aux imprimantes ont été supprimés du projet ; le contrôleur utilise `DB::table('printers')` dans un `try/catch` et passe une collection vide si la table n’existe pas.
- **Impact :** La page ne plante pas, mais la section imprimantes reste affichée (vide ou avec des stats à 0).
- **Recommandation :** Si le module imprimantes est définitivement abandonné, **adapter la vue** : masquer ou supprimer le bloc « Imprimantes configurées » et, si besoin, retirer l’affichage de la stat « imprimantes » pour éviter toute confusion.

---

### 2.2 Paramètres admin (`admin/settings/`)

- **Fichiers existants :**  
  `resources/views/admin/settings/index.blade.php`,  
  `resources/views/admin/settings/impression.blade.php`
- **Constat :** Ces vues appellent des routes du type `route('super.settings.update')`, `route('super.settings.impression')`, etc. Ces routes **ne sont pas définies** dans `routes/web.php` (seules les routes `super.ui-settings.*` existent).
- **Impact :** Les liens ou formulaires pointant vers ces routes généreront des erreurs « route not defined ».
- **Recommandation :**  
  - Soit **ajouter les routes** et les méthodes de contrôleur correspondantes (`SettingController`, etc.) pour rendre ces interfaces accessibles.  
  - Soit **ne plus utiliser** ces vues (ne pas y lier de lien dans le menu) ou les supprimer si elles ne font plus partie du périmètre.

---

## 3. Récapitulatif

| Type | Détail |
|------|--------|
| **À créer (nécessaires pour le service technique)** | `maintenance/dashboard.blade.php`, `maintenance/rooms/index.blade.php` ; optionnel : `maintenance/history/index.blade.php` |
| **À adapter (nettoyage / cohérence)** | `super/hotel-data/show.blade.php` (section imprimantes) ; `admin/settings/*` (routes ou usage à clarifier) |

Toutes les autres vues référencées par les contrôleurs existent déjà dans `resources/views/` (auth, dashboard, hotel, reception, housekeeping, laundry, super, profile, notifications, public, etc.). Les seules **interfaces nécessaires et non encore créées** concernent le **module Service technique (Maintenance)** décrit dans le guide d’intégration.
