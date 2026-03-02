# Analyse complète du projet HotelPro – Workflows, scénarios, interactions et améliorations

Ce document synthétise l’analyse du projet **HotelPro** (Laravel) : workflows, scénarios possibles, interactions entre utilisateurs et entités, remarques et pistes d’amélioration.

---

## 1. Vue d’ensemble du projet

**HotelPro** est une application de gestion hôtelière multi-établissements avec :

- **Stack :** Laravel (PHP), base SQLite/MySQL, authentification, rôles (Spatie), API REST (Sanctum), formulaire public (QR).
- **Entités principales :** Hotel, Room, RoomType, Reservation, Client, User, HousekeepingTask, LaundryCollection, ClientLinen, MaintenanceArea, RoomStateHistory.
- **Rôles :** `super-admin`, `hotel-admin`, `receptionist`, `housekeeping`, `laundry`, `maintenance`.

---

## 2. Workflows identifiés

### 2.1 Workflow Réservation (réception / formulaire public)

| Étape | Acteur | Action | Statut réservation |
|-------|--------|--------|--------------------|
| 1 | Client (formulaire QR) ou Réception | Création réservation | `pending` |
| 2 | Réception / Hotel-admin | Validation ou rejet | `validated` ou `rejected` |
| 3 | Réception | Check-in (attribution chambre) | `checked_in` |
| 4 | Réception | Check-out | `checked_out` |

- **Formulaire public** : `/f/{hotel}` → soumission → réservation en `pending`, notifications possibles (email).
- **Réception** : liste, détail, édition, validation, rejet, check-in, check-out, feuilles de police.
- **Hotel-admin** : même accès réservations + calendrier, types de chambres, chambres, QR, espaces (maintenance).

### 2.2 Workflow Chambres (états décentralisés)

Les chambres ont **3 états indépendants** + un **état global** dérivé :

| Domaine | Champ | Valeurs |
|---------|--------|---------|
| Occupation (réception) | `occupation_state` | `free`, `occupied`, `released` |
| Nettoyage (étages) | `cleaning_state` | `none`, `pending`, `in_progress`, `done` |
| Technique (maintenance) | `technical_state` | `normal`, `issue`, `maintenance`, `out_of_service` |

**État global** (`getGlobalStatus()`) : priorité **technique > occupation > nettoyage** → `available` | `occupied` | `cleaning` | `issue` | `maintenance` | `out_of_service`.

**Séquence type :**

1. **Check-out** (réception) → `occupation_state = released` → événement `RoomReleased`.
2. **Listener** (si module housekeeping activé) → `cleaning_state = pending`, création `HousekeepingTask`, notification housekeeping.
3. **Housekeeping** : start cleaning → `cleaning_state = in_progress` ; complete cleaning → `cleaning_state = done`, `occupation_state = free`, `status` synchronisé → événement `CleaningCompleted`.
4. **Listener** (si module laundry activé) → création `LaundryCollection` (linge d’étage).
5. **Maintenance** : peut mettre `technical_state` en `issue` / `maintenance` / `out_of_service` et revenir à `normal`.

Tout changement pertinent est enregistré dans **RoomStateHistory** (traçabilité).

### 2.3 Workflow Linge d’hôtel (buanderie)

- **Déclencheur :** fin de nettoyage (`CleaningCompleted`) → création **LaundryCollection** (liée chambre / tâche housekeeping).
- **Buanderie :** voit les collectes, saisit les quantités par type de linge, fait évoluer le statut (pending → in_wash → done).
- **Types de linge :** gérés par super-admin par hôtel ; laundry peut les consulter et selon permissions les gérer.

### 2.4 Workflow Linge client

Deux sources documentées :

| Source | Statut actuel | Parcours |
|--------|----------------|----------|
| **Réception** | Implémenté | Réception enregistre dépôt → Buanderie notifiée → liste « Linge client » (réception) → statuts jusqu’à « récupéré par client ». |
| **Chambre** | Implémenté | Housekeeping signale « linge client oublié » en fin de nettoyage → Buanderie notifiée → liste « Linge client – Chambre ». |

Modèle : **ClientLinen** (`source`: `reception` | `room`, statuts dédiés, `room_id` / `reservation_id` optionnels).

### 2.5 Workflow Service technique (Maintenance)

- **Chambres :** liste par état technique, mise à jour de `technical_state` (issue, maintenance, out_of_service, normal), historique.
- **Espaces :** catégories (publics, techniques, extérieurs, loisirs, admin), création d’espaces, mise à jour d’état. Partagé avec Hotel-admin et Réception (mêmes routes/contrôleur).

### 2.6 Autres flux

- **Feuilles de police :** réception → génération (preview, batch) à partir des réservations.
- **Notifications :** NotificationService (housekeeping, laundry, etc.) + UserNotification ; page `/notifications` et API pour marquer lu / tout lire.
- **Activité :** ActivityLog (Spatie) ; super-admin a une page activité ; Housekeeping/Laundry ont « Mes activités » avec filtre par période.
- **Sessions utilisateur :** liste, révoquer, trust, « révoquer les autres ».

---

## 3. Scénarios possibles (résumés)

### 3.1 Réservation

- Création (formulaire public ou réception) → pending.
- Validation / rejet par réception ou hotel-admin.
- Check-in : choix de la chambre, mise à jour room (occupied), réservation checked_in.
- Check-out : room released, RoomReleased → housekeeping (si activé).
- Annulation / modification de réservation (édition possible côté réception/hotel).

### 3.2 Chambre

- Réception : changement manuel de statut (patch status) pour cas particuliers.
- Housekeeping : start / complete cleaning (avec option « linge client en chambre »).
- Maintenance : mise à jour état technique.
- Une chambre n’est **disponible pour réservation** que si `getGlobalStatus() === 'available'` (technique normal, occupation free, nettoyage terminé ou none).

### 3.3 Buanderie

- Collectes automatiques après nettoyage (linge d’étage).
- Linge client : réception (dépôt) ou housekeeping (oubli en chambre) → liste dédiée, mise à jour statut, récupération client.
- Types de linge : consultation / CRUD selon permissions.

### 3.4 Super-admin

- Gestion hôtels, utilisateurs, rôles, modules par hôtel (housekeeping, laundry, maintenance), design/formulaires, types de linge par hôtel.
- Réservations (lecture), activité, rapports, hotel-data (reset, purge, import, export), base globale, optimisation, paramètres (dont UI), impression.

### 3.5 Formulaire public (client)

- Accès via lien/QR → formulaire par hôtel.
- Sélection type de chambre, dates, chambres disponibles (API), saisie client → envoi → réservation en pending.
- Rate limiting : 60 req/min (GET), 20 req/60 min (POST).

### 3.6 API externe (V1)

- Publique : liste hôtels, détail, room-types, rooms, availability, création réservation (rate limited).
- Authentifiée (Sanctum) : user, réservations par hôtel, validate/reject, stats.

---

## 4. Interactions utilisateurs ↔ entités

| Utilisateur | Entités principales avec lesquelles il interagit |
|-------------|---------------------------------------------------|
| **Super-admin** | Hotel, User, RoomType (via hôtel), Reservation (lecture), Setting, UiSetting, ActivityLog, modules (config), LaundryItemType |
| **Hotel-admin** | Hotel (son hôtel), Room, RoomType, Reservation, Calendar, MaintenanceArea, QR |
| **Réceptionniste** | Reservation (CRUD, validate, reject, check-in, check-out), Room (statut), Guest (staying), PoliceSheet, ClientLinen (dépôt), MaintenanceArea |
| **Housekeeping** | Room (cleaning_state), HousekeepingTask, ClientLinen (linge en chambre), MaintenanceArea, ActivityLog (mes activités) |
| **Laundry** | LaundryCollection, LaundryCollectionLine, LaundryItemType, ClientLinen, ActivityLog (mes activités) |
| **Maintenance** | Room (technical_state), MaintenanceArea, ActivityLog (historique) |
| **Client (public)** | PreReservation / Reservation (création via formulaire), RoomType, Room (disponibilité via API) |

---

## 5. Remarques et points d’attention

### 5.1 Points forts

- **Workflow chambres** bien pensé : 3 états indépendants, historique unifié, événements (RoomReleased, CleaningCompleted) et listeners (housekeeping, laundry) pour un bon découplage.
- **Modules activables par hôtel** (SettingsResolver) : housekeeping, laundry, maintenance.
- **Traçabilité** : RoomStateHistory, ActivityLog, notifications.
- **Séparation réception / housekeeping / laundry / maintenance** claire dans les routes et les modules.
- **API V1** structurée (publique + Sanctum) et formulaire public avec rate limiting.

### 5.2 Problèmes ou incohérences déjà documentés

- **Imprimantes :** Printer supprimé mais seeders (PrinterSeeder, etc.) encore appelés → erreur au seed. Section imprimantes dans hotel-data/show et FixAbsolutePaths à adapter ou retirer (cf. ANALYSE_PROBLEMES_PROJET.md).
- **Routes paramètres :** `super.settings.update`, `super.settings.impression`, etc. utilisées dans des vues admin mais routes absentes ou incohérentes → risque « route not defined ».
- **Vues super/hotels/create et edit** : présentes alors que la ressource est en `except(['create','edit'])` → à clarifier ou supprimer.

### 5.3 Autres remarques

- **Réservation sans chambre assignée :** possible en `pending`/`validated` ; la chambre est assignée au check-in. Cohérent avec le flux métier.
- **Compatibilité BDD :** ActivityController gère SQLite vs MySQL (DATE) ; à étendre si support PostgreSQL.
- **Mots de passe par défaut** (import hotel-data/database) : à réserver au démo/test ; en prod, forcer changement ou inviter à modifier.

---

## 6. Améliorations proposées

### 6.1 Corrections prioritaires

1. **Seeders imprimantes**  
   Retirer `PrinterSeeder`, `PrinterSettingsSeeder`, `ImpressionSettingsSeeder` de `DatabaseSeeder` (ou réintégrer le modèle Printer si le module est conservé).

2. **Routes paramètres super-admin**  
   Soit ajouter les routes manquantes pour `SettingsController` et page impression (`super.settings.*`), soit retirer les liens vers ces vues et utiliser uniquement les routes existantes (ex. ui-settings).

3. **Section imprimantes et FixAbsolutePaths**  
   Si le module imprimantes est abandonné : masquer/supprimer le bloc imprimantes dans `super/hotel-data/show` et adapter la commande FixAbsolutePaths pour ne pas dépendre de la table `printers`.

### 6.2 Fonctionnel

4. **Linge client – récupération côté réception**  
   Écran ou action « Client a récupéré son linge » (passage à `recupere_par_client` ou équivalent) pour clôturer le parcours dépôt → buanderie → retrait client.

5. **Notifications linge client**  
   S’assurer que les notifications réception → buanderie et housekeeping → buanderie sont bien envoyées et visibles (NotificationService + liste « Linge client »).

6. **Historique / Mes activités**  
   Vérifier que les filtres par période (date début / date fin) sont bien appliqués partout (Laundry, Housekeeping) et que les actions affichées sont bien limitées à l’utilisateur connecté (causer_id).

7. **RoomStateValidator**  
   Document d’implémentation workflow chambres prévoit un `RoomStateValidator` (combinaisons interdites, ex. occupée + nettoyage en cours). À implémenter si pas déjà fait et appeler avant les transitions sensibles.

### 6.3 Qualité et maintenabilité

8. **Tests**  
   Exécuter la suite après chaque changement (seeders, routes, contrôleurs) ; ajouter ou compléter des tests sur les flux critiques : réservation (création, validation, check-in/out), RoomReleased → housekeeping, CleaningCompleted → laundry.

9. **Documentation API**  
   Routes web.php indiquent une doc API ; s’assurer qu’une doc (ex. OpenAPI/Postman) pour l’API V1 est à jour (endpoints, auth Sanctum, exemples).

10. **Paramètres et sécurité**  
    Vérifier que les paramètres sensibles (import, purge, reset) sont protégés par rôle et confirmation ; `.env` sans secrets commités, `APP_DEBUG=false` en production.

### 6.4 UX / Interfaces

11. **Vues maintenance**  
    Selon INTERFACES_NECESSAIRES_NON_CREEES.md, les vues maintenance (dashboard, rooms, éventuellement history) ont été listées comme à créer ; si les contrôleurs existent déjà (Maintenance\Controllers), confirmer que les vues correspondantes existent et sont utilisées.

12. **Cohérence des libellés**  
    Unifier les libellés des statuts (chambre, réservation, linge client) entre back-office et éventuels messages/emails (et i18n si multi-langue).

13. **Retour après action**  
    Après check-in, check-out, fin de nettoyage, mise à jour linge client : messages flash et redirections claires pour éviter la double soumission et confirmer la prise en compte.

### 6.5 Évolutions possibles

14. **Événement « Linge client déposé »**  
    Pour un découplage plus propre : événement dédié quand la réception enregistre un dépôt de linge client, avec listener qui notifie la buanderie (au lieu d’appeler directement le service de notification).

15. **Tableau de bord réception**  
    Widgets ou liens directs vers : réservations du jour, chambres à libérer, linge client en attente de récupération, tâches housekeeping en attente (si besoin de visibilité transversale).

16. **Export / rapports**  
    Exporter les « Mes activités » (Housekeeping/Laundry) en CSV/PDF sur une période ; rapports super-admin déjà présents, les étendre si besoin (par module, par hôtel).

---

## 7. Synthèse

- **Workflows principaux :** Réservation (pending → validated/rejected → check-in → check-out), Chambres (occupation / nettoyage / technique avec événements et listeners), Linge d’hôtel (nettoyage → collecte buanderie), Linge client (réception + chambre), Maintenance (chambres + espaces).
- **Scénarios** couvrent création/réception/réceptionniste, validation, check-in/out, nettoyage, buanderie, linge client, technique, super-admin, formulaire public et API.
- **Interactions** sont clairement réparties par rôle (super-admin, hotel-admin, réception, housekeeping, laundry, maintenance, client).
- **Améliorations** à prioriser : correction seeders/routes/imprimantes, renforcement du parcours linge client et des validations, tests et documentation API ; puis UX, RoomStateValidator et petites évolutions (événements, dashboards, exports).

Ce document peut servir de référence pour les prochaines itérations et pour prioriser les tâches (correctives vs évolutions).
