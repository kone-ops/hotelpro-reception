# Intégration du service technique (maintenance) dans HotelPro

Ce document explique **comment intégrer le service technique / maintenance** dans le projet, en suivant le même modèle que les modules **Service des étages** (Housekeeping) et **Buanderie** (Laundry).

---

## 1. Ce qui existe déjà dans le projet

Le projet est **déjà préparé** pour un état technique des chambres :

| Élément | Détail |
|--------|--------|
| **Modèle `Room`** | Colonne `technical_state` : `normal`, `issue`, `maintenance`, `out_of_service` |
| **`Room::getGlobalStatus()`** | Priorité : état technique > occupation > nettoyage. Si `technical_state` ≠ normal, la chambre n’est pas disponible. |
| **Réception / Admin hôtel** | Peuvent mettre une chambre en « maintenance » (statut + `technical_state`) |
| **`RoomStateValidator`** | Valide les transitions d’état technique et les combinaisons (ex. maintenance ⇒ occupation = free) |

Il **manque** : un **rôle dédié**, des **routes**, un **module** (contrôleurs, vues, service) et un **menu** pour que le « service technique » ait son espace comme Housekeeping ou Laundry.

---

## 2. Plan d’intégration (étapes à suivre)

### Étape 1 – Rôle et permissions

1. **Créer le rôle** `maintenance` (ou `technician`) dans le seeder des rôles.
2. **Définir les permissions** (ex. `view-maintenance`, `update-technical-state`, `set-out-of-service`).
3. **Attribuer ces permissions au rôle** `maintenance`.
4. **Créer un utilisateur de démo** (ex. `technicien@hotelpro.test` / `password`) avec le rôle `maintenance` et un `hotel_id`.

**Fichier à modifier :** `database/seeders/RolesAndAdminSeeder.php`  
S’inspirer des blocs `$housekeepingRole` et `$housekeeping` (User).

---

### Étape 2 – Redirection après login

Après connexion, un utilisateur avec le rôle `maintenance` doit être redirigé vers le tableau de bord du service technique.

**Fichier à modifier :** `routes/web.php`  
Dans la route `/dashboard`, ajouter avant `return view('dashboard');` :

```php
if ($user->hasRole('maintenance')) return redirect()->route('maintenance.dashboard');
```

---

### Étape 3 – Module `app/Modules/Maintenance`

Créer la structure suivante (sur le modèle de `Housekeeping`) :

```
app/Modules/Maintenance/
├── Controllers/
│   ├── DashboardController.php   # Tableau de bord : chambres en issue/maintenance/hors service
│   └── RoomController.php        # Liste des chambres, actions sur technical_state
├── Services/
│   └── MaintenanceService.php    # Logique : passer en maintenance, réparer, hors service
└── (optionnel) Models/
    └── MaintenanceTask.php       # Historique des interventions (table dédiée si besoin)
```

- **DashboardController** : récupérer les chambres où `technical_state` ∈ `['issue', 'maintenance', 'out_of_service']`, afficher des statistiques (nombre en maintenance, hors service, etc.).
- **RoomController** : liste filtrée par état technique ; actions du type « Signaler un problème » (→ `issue`), « Mettre en maintenance » (→ `maintenance`), « Remettre en service » (→ `normal`), « Mettre hors service » (→ `out_of_service`). Utiliser **RoomStateValidator** pour valider les transitions.
- **MaintenanceService** : méthodes comme `setInMaintenance(Room $room, User $user)`, `setBackToNormal(Room $room)`, `setOutOfService(Room $room)`, et mise à jour de `technical_state` + appel à `$room->syncStatusFromStates()` pour garder `status` cohérent.

---

### Étape 4 – Routes

**Fichier à modifier :** `routes/web.php`  
Ajouter un groupe de routes (après le groupe Laundry, par exemple) :

```php
// Routes Service technique (Maintenance)
Route::middleware(['auth', 'role:maintenance', 'hotel.access'])->prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/', [\App\Modules\Maintenance\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms', [\App\Modules\Maintenance\Controllers\RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms/{room}/technical-state', [\App\Modules\Maintenance\Controllers\RoomController::class, 'updateTechnicalState'])->name('rooms.update-technical-state');
    // Optionnel : historique des interventions
    Route::get('/history', [\App\Modules\Maintenance\Controllers\HistoryController::class, 'index'])->name('history.index');
});
```

Adapter les noms de contrôleurs et méthodes selon votre implémentation.

---

### Étape 5 – Vues Blade

Créer les vues dans `resources/views/maintenance/` :

- `dashboard.blade.php` : résumé des chambres en problème / maintenance / hors service, liens vers la liste et les actions.
- `rooms/index.blade.php` : liste des chambres avec filtre par `technical_state`, boutons pour changer l’état (issue, maintenance, normal, out_of_service).
- (Optionnel) `history/index.blade.php` : si vous enregistrez un historique des interventions.

Réutiliser le layout (ex. `layouts.app`) et le style des vues Housekeeping/Laundry pour rester cohérent.

---

### Étape 6 – Menu (sidebar)

**Fichier à modifier :** `resources/views/layouts/sidebar.blade.php`  
Après le bloc `@elseif(auth()->user()->hasRole('laundry'))`, ajouter un bloc pour le rôle `maintenance` :

```blade
@elseif(auth()->user()->hasRole('maintenance'))
    <!-- Section Service technique -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaintenance">
                <i class="bi bi-tools"></i><span class="nav-text">Service technique</span>
            </button>
        </h2>
        <div id="collapseMaintenance" class="accordion-collapse collapse show" data-bs-parent="#navAccordion">
            <div class="accordion-body">
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="{{ route('maintenance.dashboard') }}" class="nav-link">Tableau de bord</a></li>
                    <li class="nav-item"><a href="{{ route('maintenance.rooms.index') }}" class="nav-link">Chambres (état technique)</a></li>
                    {{-- Optionnel : <li><a href="{{ route('maintenance.history.index') }}">Historique</a></li> --}}
                </ul>
            </div>
        </div>
    </div>
```

---

### Étape 7 – Règles métier (déjà en place)

- **Qui peut changer l’état technique ?**  
  Aujourd’hui : réception et admin hôtel (via le statut « maintenance »). Après intégration : en plus, le rôle `maintenance` pourra gérer `issue`, `maintenance`, `out_of_service`, `normal` depuis son module.

- **Cohérence** : utiliser **`App\Core\RulesEngine\RoomStateValidator`** dans le `MaintenanceService` ou le contrôleur avant de modifier `technical_state`. Si une chambre est occupée et que vous la mettez en maintenance, le validateur peut l’interdire (selon vos règles) ; le modèle actuel impose que en `maintenance` ou `out_of_service` l’occupation soit `free`.

- Après modification de `technical_state`, appeler **`$room->syncStatusFromStates()`** pour que la colonne `status` (affichage réception, calendrier, etc.) reste alignée.

---

### Étape 8 – Activation par hôtel (optionnel)

Si vous utilisez `hotel->settings['modules']` pour activer/désactiver des modules (ex. housekeeping, laundry), ajouter la clé `maintenance` et, dans le middleware ou dans les contrôleurs du module, vérifier que le module est activé pour l’hôtel de l’utilisateur connecté.

---

## 3. Résumé des fichiers à créer ou modifier

| Action | Fichier |
|--------|--------|
| Modifier | `database/seeders/RolesAndAdminSeeder.php` (rôle + permissions + user technicien) |
| Modifier | `routes/web.php` (redirection dashboard + groupe de routes `maintenance`) |
| Créer | `app/Modules/Maintenance/Controllers/DashboardController.php` |
| Créer | `app/Modules/Maintenance/Controllers/RoomController.php` |
| Créer | `app/Modules/Maintenance/Services/MaintenanceService.php` |
| Créer | `resources/views/maintenance/dashboard.blade.php` |
| Créer | `resources/views/maintenance/rooms/index.blade.php` |
| Modifier | `resources/views/layouts/sidebar.blade.php` (menu Service technique) |
| Optionnel | Historique : modèle `MaintenanceTask`, migration, `HistoryController`, vue |

---

## 4. Connexion en tant que technicien (après intégration)

Une fois le rôle et l’utilisateur créés (seeder) :

1. Exécuter : `php artisan db:seed --class=RolesAndAdminSeeder` (ou votre seeder qui crée le rôle et l’utilisateur).
2. Se connecter avec l’email/mot de passe du technicien (ex. `technicien@hotelpro.test` / `password`).
3. Vous serez redirigé vers **/maintenance** (tableau de bord du service technique).

---

## 5. États techniques (rappel)

| Valeur | Signification |
|--------|----------------|
| `normal` | Aucun problème technique ; la chambre peut être utilisée (selon occupation et nettoyage). |
| `issue` | Problème signalé (à traiter). |
| `maintenance` | En réparation / maintenance. |
| `out_of_service` | Hors service (indisponible longtemps). |

Ces valeurs sont déjà gérées par le modèle `Room` et par `RoomStateValidator` ; le module Maintenance permettra au service technique de les mettre à jour depuis son interface.

---

## 6. Espaces (5 catégories) – Procédures et BDD

Le module Service technique inclut la gestion d’**espaces** en cinq catégories (hors chambres) : **Espaces publics**, **Espaces techniques**, **Espaces extérieurs**, **Loisirs**, **Administration**. Chaque espace a un état technique (normal, problème signalé, en maintenance, hors service).

- **Implémentation dans le projet :** table `maintenance_areas`, modèle `App\Modules\Maintenance\Models\MaintenanceArea`, contrôleur `MaintenanceAreaController`, vues `resources/views/maintenance/areas/`, routes sous `maintenance/areas` et `maintenance/area/{area}`.
- **Procédures d’utilisation et d’intervention en BDD :** document **`docs/PROCEDURES_ESPACES_SERVICE_TECHNIQUE_BDD.md`** (structure de la table, procédures via l’application, requêtes SQL pour consulter/ajouter/modifier/supprimer, règles de cohérence).
- **Dans l’interface :** menu Service technique → **Espaces** ; un bloc **Procédures** (bouton « Procédures ») rappelle les étapes et renvoie au document ci‑dessus.
