# Analyse et implémentation – Workflow décentralisé des chambres (HotelPro)

Ce document sert de **référence unique** pour l’analyse, la méthode choisie et le plan d’implémentation étape par étape. Il permet à un développeur ou à une IA de reprendre le travail de manière cohérente.

---

## 1. État actuel (constaté dans le code)

| Élément | État actuel |
|--------|-------------|
| **Room** | Un seul champ `status` : `available`, `occupied`, `maintenance`, `reserved` (enum en BDD) |
| **Gestion** | Centralisée : seul la réception modifie le statut via `Reception\RoomController::updateStatus()` |
| **Check-out** | `Reception\ReservationController::checkOut()` met la chambre directement en `available` (l.639-641) |
| **Check-in** | Chambre mise en `occupied` |
| **Rôles** | `super-admin`, `hotel-admin`, `receptionist` uniquement (RolesAndAdminSeeder) |
| **Hôtel** | Modèle avec `settings` (array) – utilisable pour activer des modules |
| **Événements** | Aucun dossier `app/Events` ; pas d’événements métier sur les chambres |
| **Historique** | Aucune traçabilité des changements de statut des chambres |

Fichiers clés déjà analysés :
- `app/Models/Room.php` – `updateStatus()`, `isAvailable()`, scopes `available`/`occupied`
- `app/Http/Controllers/Reception/RoomController.php` – changement de statut avec vérification des rôles
- `app/Http/Controllers/Reception/ReservationController.php` – `checkIn()`, `checkOut()` qui mettent à jour `room->status`
- `database/migrations/2025_10_21_100001_create_rooms_table.php` – enum `status` sur la table `rooms`

---

## 2. Méthode recommandée : approche hybride

### 2.1 Choix : états indépendants + validation + historique unifié + événements

- **États indépendants par domaine** (comme dans votre proposition « suite ») :
  - **occupation_state** (réception) : `free`, `occupied`, `released`
  - **cleaning_state** (service des étages) : `none`, `pending`, `in_progress`, `done`
  - **technical_state** (technique) : `normal`, `issue`, `maintenance`, `out_of_service`
- **Un seul historique** : table `room_state_history` avec `state_type` (occupation | cleaning | technical) pour éviter 3 tables et garder une traçabilité unifiée.
- **Règles de cohérence** : classe `RoomStateValidator` pour interdire les combinaisons incohérentes (ex. occupée + nettoyage en cours).
- **État global** : méthode `Room::getGlobalStatus()` (et optionnellement `isAvailableForReservation()`) pour l’affichage et la réservation, basée sur une priorité : technique > occupation > nettoyage.
- **Découplage** : événements Laravel (`RoomReleased`, `CleaningCompleted`, etc.) pour que les modules (housekeeping, laundry, technical) réagissent sans que la réception ne les connaisse.
- **Activation par hôtel** : utilisation de `hotel->settings['modules']` (ex. `housekeeping`, `maintenance`, `laundry`) pour activer/désactiver les modules.

### 2.2 Pourquoi cette méthode est adaptée à une bonne maîtrise par l’IA

1. **Découpage clair** : une phase = un ensemble de fichiers et de migrations identifiables.
2. **Compatibilité** : on conserve le champ `status` (dérivé de `getGlobalStatus()`) ou on le remplace progressivement, sans casser la réception tout de suite.
3. **Conventions fixes** : noms de champs, noms d’événements, structure des dossiers (`app/Core/`, `app/Modules/`) – une IA peut suivre le plan à la lettre.
4. **Tests** : chaque phase peut être testée (RoomStateValidator, transitions, listeners).
5. **Reprise** : ce document + les numéros d’étapes permettent de dire « implémenter l’étape 2.3 » sans ambiguïté.

---

## 3. Plan d’implémentation par étapes

Les étapes sont conçues pour être exécutables **dans l’ordre**. Chaque sous-étape indique les fichiers à créer ou modifier.

---

### Phase 1 : Fondations (états + cohérence + état global)

#### Étape 1.1 – Structure des dossiers

- Créer :
  - `app/Core/RulesEngine/`
  - `app/Core/StateManager/` (optionnel au début, peut être ajouté en Phase 2)
  - `app/Events/` (événements globaux liés aux chambres/réservations)
  - `app/Listeners/`
  - `app/Modules/Housekeeping/` (vide pour l’instant, ou avec un fichier placeholder)
  - `app/Modules/Maintenance/`
  - `app/Modules/Laundry/`

#### Étape 1.2 – Migration : ajouter les 3 états à `rooms`

- Créer une migration (ex. `add_room_state_columns_to_rooms_table`) qui :
  - Ajoute `occupation_state` (string, default `free`) : valeurs `free`, `occupied`, `released`.
  - Ajoute `cleaning_state` (string, default `none`) : `none`, `pending`, `in_progress`, `done`.
  - Ajoute `technical_state` (string, default `normal`) : `normal`, `issue`, `maintenance`, `out_of_service`.
- Migrer les données existantes à partir de l’ancien `status` :
  - `status = 'available'` → `occupation_state = 'free'`, `technical_state = 'normal'`
  - `status = 'occupied'` ou `'reserved'` → `occupation_state = 'occupied'`
  - `status = 'maintenance'` → `technical_state = 'maintenance'`
- **Ne pas supprimer** la colonne `status` tout de suite : la garder pour compatibilité ou la remplir via un accesseur/mutateur dérivé de l’état global (voir 1.4).

#### Étape 1.3 – Table d’historique unifié

- Créer la migration `create_room_state_history_table` :
  - `id`, `room_id` (FK), `state_type` (enum ou string : `occupation`, `cleaning`, `technical`)
  - `previous_value`, `new_value` (string)
  - `changed_by` (user_id, nullable), `service` (string, nullable : reception, housekeeping, technical)
  - `notes` (text, nullable), `changed_at` (timestamp)
  - Index sur `room_id`, `changed_at`.

#### Étape 1.4 – Modèle Room : fillable, casts, état global

- Dans `app/Models/Room.php` :
  - Ajouter aux `$fillable` : `occupation_state`, `cleaning_state`, `technical_state`.
  - Ajouter la méthode `getGlobalStatus(): string` avec la logique :
    - Si `technical_state !== 'normal'` → retourner `technical_state` (issue, maintenance, out_of_service).
    - Sinon si `occupation_state === 'occupied'` → `'occupied'`.
    - Sinon si `cleaning_state` ∈ { pending, in_progress } → `'cleaning'`.
    - Sinon → `'available'`.
  - Ajouter `isAvailableForReservation(): bool` → `return $this->getGlobalStatus() === 'available';`
  - Adapter les scopes existants (`scopeAvailable`, `scopeOccupied`) pour utiliser soit les nouveaux champs soit `getGlobalStatus()` selon la stratégie choisie (voir 1.5).
  - Optionnel : garder `status` en BDD et le synchroniser avec `getGlobalStatus()` dans un observer, ou déprécier et utiliser uniquement les 3 états + `getGlobalStatus()`.

#### Étape 1.5 – Compatibilité avec l’existant (réservations, réception)

- **Option A** : Les requêtes qui utilisent `where('status', 'available')` sont progressivement remplacées par un scope basé sur `getGlobalStatus()` (ex. `scopeAvailable()` qui vérifie `getGlobalStatus() === 'available'`). Comme SQL ne peut pas appeler une méthode PHP, on peut :
  - soit dupliquer la logique en raw SQL dans le scope,
  - soit garder une colonne `status` mise à jour par un Observer sur Room (save) qui appelle `getGlobalStatus()` et met à jour `status`.
- **Option B** (recommandée pour transition douce) : Dans la migration 1.2, après avoir rempli les 3 états, mettre à jour aussi la colonne `status` pour refléter l’état global (available, occupied, maintenance, etc.). Ensuite, à chaque modification des 3 états, mettre à jour `status` dans le même flux (service ou observer) pour que l’existant continue de fonctionner.

#### Étape 1.6 – RoomStateValidator (cohérence)

- Créer `app/Core/RulesEngine/RoomStateValidator.php` :
  - Méthode `validateStateCombination(Room $room): bool` :
    - Si `occupation_state === 'occupied'` et `cleaning_state` ∈ { pending, in_progress } → false.
    - Si `technical_state === 'maintenance'` (ou `out_of_service`) et `occupation_state !== 'free'` → false (règle métier à confirmer).
  - Méthode `validateTransition(Room $room, string $stateType, string $newValue, User $user): array` (optionnel en Phase 1) : retourne `['valid' => bool, 'message' => string]` pour des transitions plus fines plus tard.

#### Étape 1.7 – Modèle RoomStateHistory

- Créer `app/Models/RoomStateHistory.php` avec relations `room()`, `user()` (changed_by), fillable et casts appropriés.

---

### Phase 2 : Réception – Check-out et événement RoomReleased

#### Étape 2.1 – Événement RoomReleased

- Créer `app/Events/RoomReleased.php` :
  - Propriétés : `public Room $room`, `public User $releasedBy`.
  - Constructor qui les reçoit.

#### Étape 2.2 – Check-out : passer en `released` et émettre l’événement

- Dans `Reception\ReservationController::checkOut()` :
  - Au lieu de `$reservation->room->update(['status' => 'available'])`, faire :
    - `$reservation->room->update(['occupation_state' => 'released'])`
    - Mettre à jour la colonne `status` si vous suivez l’option B (ex. `status = 'available'` seulement après nettoyage, ou garder un statut intermédiaire « libérée » selon votre règle métier). Pour rester compatible avec l’existant « disponible = prête à vendre », on peut garder temporairement `status = 'available'` pour l’affichage réception, ou introduire un libellé « Libérée » côté front quand `occupation_state === 'released'` et `cleaning_state !== 'done'`.
  - Enregistrer une entrée dans `room_state_history` (occupation, previous_value = occupied, new_value = released).
  - Émettre : `event(new RoomReleased($reservation->room, Auth::user()));`

Règle métier à trancher : après check-out, la chambre est « libérée » (released). Elle ne redevient « available » (pour réservation) qu’après nettoyage terminé (voir Phase 3). Donc `getGlobalStatus()` doit retourner une valeur qui empêche la réservation tant que cleaning_state n’est pas `done` (déjà prévu dans 1.4).

#### Étape 2.3 – Listener CreateCleaningTask (module Housekeeping)

- Créer `app/Modules/Housekeeping/Listeners/CreateCleaningTask.php` (ou `app/Listeners/CreateCleaningTask.php`) :
  - Écoute `RoomReleased`.
  - Vérifier si le module housekeeping est activé pour l’hôtel (ex. `SettingsResolver::isModuleEnabled($event->room->hotel, 'housekeeping')` – à créer si besoin, en lisant `$hotel->settings['modules']['housekeeping'] ?? false`).
  - Si activé : mettre à jour la chambre `cleaning_state = 'pending'`, créer éventuellement une tâche (table `housekeeping_tasks` à créer en Phase 2 ou 3), notifier le service des étages (NotificationService ou méthode dédiée).

Enregistrer le listener dans `EventServiceProvider` ou avec `Event::listen()`.

---

### Phase 3 : Module Housekeeping (service des étages)

#### Étape 3.1 – Rôles et permissions

- Dans un seeder ou une migration de rôles : créer le rôle `housekeeping`.
- Permissions suggérées : `view-rooms`, `update-cleaning-state`, `complete-cleaning` (noms à aligner avec vos conventions).

#### Étape 3.2 – Modèle HousekeepingTask (optionnel mais recommandé)

- Migration `create_housekeeping_tasks_table` : hotel_id, room_id, type (cleaning, etc.), status (pending, in_progress, done), assigned_to (user_id), started_at, completed_at, notes.
- Modèle `App\Modules\Housekeeping\Models\HousekeepingTask` (ou `App\Models\HousekeepingTask`).

#### Étape 3.3 – Service HousekeepingService

- Créer `app/Modules/Housekeeping/Services/HousekeepingService.php` (ou `app/Services/HousekeepingService.php`) :
  - `startCleaning(Room $room, User $user)` : passer `cleaning_state` à `in_progress`, écrire dans `room_state_history`, éventuellement créer/mettre à jour HousekeepingTask.
  - `completeCleaning(Room $room, User $user, ?string $notes)` : passer `cleaning_state` à `done`, mettre à jour `occupation_state` à `free` si ce n’est pas déjà fait, mettre à jour la colonne `status` à `available`, écrire dans room_state_history, émettre un événement `CleaningCompleted` si besoin (pour buanderie, etc.).

#### Étape 3.4 – HousekeepingController

- Créer `app/Http/Controllers/Housekeeping/DashboardController.php` et `RoomController.php` (ou un seul contrôleur) :
  - Liste des chambres avec `cleaning_state = 'pending'` ou `in_progress`.
  - Actions : démarrer nettoyage, terminer nettoyage (appel au HousekeepingService).
  - Middleware / politique : rôle `housekeeping` (et hotel_id cohérent).

#### Étape 3.5 – Événement CleaningCompleted et notifications

- Créer `app/Events/CleaningCompleted.php` (Room, User).
- Si besoin : listener pour notifier la buanderie (Phase 4) ou enregistrer des lignes « linge à collecter ».

---

### Phase 4 : Module Maintenance (technique)

#### Étape 4.1 – Rôle et permissions

- Rôle `technical`, permissions : `view-rooms`, `update-technical-state`, `manage-issues`.

#### Étape 4.2 – Modèles TechnicalIssue, Space (si besoin)

- Tables : `technical_issues` (room_id ou space_id, description, status, reported_by, resolved_at, etc.), éventuellement `spaces` si vous gérez des espaces au-delà des chambres.

#### Étape 4.3 – TechnicalService

- Méthodes pour passer `technical_state` à `issue`, `maintenance`, `out_of_service`, et revenir à `normal` ; enregistrement dans `room_state_history`.

#### Étape 4.4 – TechnicalController et vues

- Dashboard technique, liste des chambres avec problème, formulaire de signalement et de résolution.

---

### Phase 5 : Module Laundry (buanderie)

#### Étape 5.1 – Rôle laundry, modèles LaundryItem / LaundryMovement

- Selon votre spec : LaundryItem (linges), mouvements (entrée/sortie).

#### Étape 5.2 – LaundryService et intégration

- Listener sur `CleaningCompleted` pour enregistrer les linges collectés si le module est activé.

---

### Phase 6 : Intégration et paramétrage par hôtel

#### Étape 6.1 – SettingsResolver (ou équivalent)

- Classe qui lit `$hotel->settings['modules']` et expose `isModuleEnabled(Hotel $hotel, string $module): bool`.

#### Étape 6.2 – Configuration des modules dans l’interface

- Écran (super-admin) pour activer/désactiver housekeeping, maintenance, laundry par hôtel (écriture dans `hotel->settings`).

#### Étape 6.3 – Notifications

- Réutiliser `NotificationService` pour notifier housekeeping à la libération, technique à la panne, buanderie aux linges, selon les listeners déjà prévus.

---

## 4. Ordre d’exécution recommandé pour l’IA

Pour une session de développement par IA, exécuter dans cet ordre :

1. **Phase 1** : 1.1 → 1.2 → 1.3 → 1.4 → 1.5 → 1.6 → 1.7 (fondations).
2. **Phase 2** : 2.1 → 2.2 → 2.3 (check-out + RoomReleased + listener housekeeping).
3. **Phase 3** : 3.1 → 3.2 → 3.3 → 3.4 → 3.5 (housekeeping complet).
4. Puis Phases 4, 5, 6 selon priorité.

Chaque étape peut être demandée explicitement : « Implémente l’étape 1.2 du document ANALYSE_ET_IMPLEMENTATION_WORKFLOW_CHAMBRES » pour que l’IA applique exactement les modifications décrites.

---

## 5. Synthèse des avantages pour la maîtrise par l’IA

- **Analyse** : Ce document et le code actuel suffisent pour comprendre l’existant et la cible.
- **Méthode** : Une seule approche documentée (hybride), avec états indépendants, validation, historique unifié, événements.
- **Implémentation** : Étapes numérotées, fichiers nommés, ordre défini.
- **Reprise** : Référence unique `docs/ANALYSE_ET_IMPLEMENTATION_WORKFLOW_CHAMBRES.md` + numéros d’étapes (ex. « Étape 1.2 », « Phase 2 »).

Vous pouvez commencer par la Phase 1 ; une fois les fondations en place, les phases suivantes s’enchaînent sans ambiguïté.
