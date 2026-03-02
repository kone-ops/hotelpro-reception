# Workflows linge client / réception / étages et buanderie

## 1. Création d’un utilisateur Buanderie (Laundry)

### Comment créer un utilisateur laundry

- **Où :** Super Admin → **Utilisateurs** → **Créer un utilisateur** (ou formulaire depuis la liste).
- **Champs :** Nom, Email, Mot de passe, **Hôtel** (obligatoire pour ce rôle), **Rôle** = **Laundry** (Buanderie).
- Le rôle `laundry` existe déjà dans l’application ; il suffit de le sélectionner et d’associer l’utilisateur à l’hôtel concerné.

### Ce que voit l’utilisateur Buanderie après connexion

- **Tableau de bord Buanderie** : statistiques (en attente, en lavage, terminées aujourd’hui, collectes du jour) et liste des collectes en attente.
- **Collectes de linge** : liste des collectes (linge d’étage après nettoyage), détail par collecte, saisie des quantités par type de linge, passage en « en lavage » / « terminé ».
- **Types de linge** : consultation (et gestion si permission) des types de linge de l’hôtel (draps, serviettes, etc.).
- **Historique / Mes activités** (à ajouter) : filtre par **période** (date début – date fin) pour voir uniquement ses propres actions (collectes mises à jour, statuts modifiés, types de linge créés/modifiés).

---

## 2. Distinction linge hôtel / linge client

| Type | Origine | Gestion actuelle / prévue |
|------|--------|---------------------------|
| **Linge d’hôtel (linge d’étage)** | Chambre après check-out → nettoyage → collecte | Déjà en place : tâche housekeeping → fin de nettoyage → création d’une **LaundryCollection** (chambre + lignes par type de linge). |
| **Linge client à la réception** | Client dépose du linge à la réception (ex. à laver / à repasser, à récupérer plus tard). | À implémenter : parcours **réception → buanderie** ; client peut **repasser récupérer** → statuts (en attente récupération, récupéré, envoyé en lavage, etc.). |
| **Linge client en chambre** | Client a oublié du linge en chambre ; le service des étages le signale. | À implémenter : **housekeeping** signale « linge client oublié en chambre X » → **notification buanderie** ; buanderie voit ces linges (liste dédiée) et peut les traiter à part ou les associer à une collecte. |

---

## 3. Workflows détaillés

### 3.1 Linge d’hôtel (déjà en place)

```
Check-out → occupation_state = released
    → Événement RoomReleased
        → Création HousekeepingTask (si module housekeeping activé)
            → Agent étages : début / fin nettoyage
                → Événement CleaningCompleted
                    → Création LaundryCollection (si module laundry activé)
                        → Buanderie : saisie quantités, statuts (pending → in_wash → done)
```

- **Housekeeping** voit : chambres à nettoyer, tâches du jour, filtres par état.
- **Laundry** voit : collectes créées après chaque fin de nettoyage, avec chambre et date/heure.

### 3.2 Linge client déposé à la réception (à implémenter)

**Idée :** Le client laisse du linge à la réception (à laver / à repasser). Il peut **revenir le récupérer**. La buanderie doit être notifiée et avoir une liste dédiée.

```
Réception : "Client dépose linge à la réception"
    → Création d’un enregistrement "Linge client - Réception"
        → Statut initial : en_attente_reception (ou stocké à la réception)
    → Notification → Buanderie : "Nouveau linge client à la réception (à récupérer)"
    → Buanderie récupère le linge (ou réception le envoie)
        → Statut : en_laverie / en_attente_retrait_client
    → Client repasse à la réception pour récupérer
        → Réception : "Client a récupéré son linge"
        → Statut : recupere_par_client
```

- **Réception** : écran pour enregistrer un dépôt de linge client (optionnel : nom/client/réservation, description, date).
- **Buanderie** : liste « Linge client – Réception » avec filtre par période et statut ; notifications pour nouveaux dépôts.

### 3.3 Linge client oublié en chambre (à implémenter)

**Idée :** Lors du nettoyage, l’agent des étages signale « linge client oublié en chambre X ». La buanderie est notifiée et retrouve ce linge dans une liste « par chambre ».

```
Housekeeping : fin de nettoyage chambre X
    → Option : "Linge client oublié dans la chambre" (case à cocher / liste d’articles)
        → Création "Linge client - Chambre" (lié à la chambre / à la tâche housekeeping)
    → Notification → Buanderie : "Linge client signalé en chambre X"
    → Buanderie : liste "Linge client – Chambre" (retrouver les linges par chambre)
        → Traitement (lavage, stockage) et éventuellement association à un retrait client / réception
```

- **Housekeeping** : lors de la complétion du nettoyage, champ optionnel « Linge client laissé en chambre » (description ou types).
- **Buanderie** : liste « Linge client – Chambre » avec chambre, date, statut ; filtre par période.

---

## 4. Notifications

| Émetteur | Destinataire | Événement |
|----------|--------------|-----------|
| Réception | Buanderie | Client a déposé du linge à la réception → notification + lien vers la fiche / liste « Linge réception ». |
| Housekeeping | Buanderie | Linge client signalé en chambre X → notification + lien vers « Linge chambre » / chambre X. |

- Utiliser le `NotificationService::notifyLaundry($hotelId, ...)` existant pour toutes les notifications vers la buanderie.

---

## 5. Proposition de modèles de données (linge client)

### Option A : Table dédiée `client_linen` (recommandée)

Une seule table pour les deux sources (réception et chambre), avec un champ `source` :

- **source** : `reception` | `room`
- **room_id** : nullable (renseigné si source = room)
- **reservation_id** : nullable (pour tracer le séjour si besoin)
- **housekeeping_task_id** : nullable (si signalé depuis une tâche étages)
- **received_at**, **received_by** (user_id) : réception ou étages
- **status** : `pending_pickup` (à récupérer par la buanderie) | `at_laundry` | `ready_for_pickup` (client peut récupérer) | `picked_up` | `sent_to_laundry` (traitement interne)
- **notes**, **picked_up_at**, **picked_up_by**
- Optionnel : lignes (quantité par type de linge) pour détail, ou un simple champ texte description.

### Option B : Étendre `laundry_collections`

- Ajouter un champ **source** sur `laundry_collections` : `room` (actuel) | `client_reception` | `client_room`.
- Pour `client_reception` : `room_id` nullable, et un champ type « client » (ex. `is_client_linen` + `client_linen_status`).
- Plus rapide à brancher mais mélange linge d’étage et linge client dans la même table ; filtres et écrans à bien séparer.

**Recommandation :** Option A (table `client_linen`) pour une séparation claire et des statuts dédiés au parcours client (récupération par le client, etc.).

---

## 6. Historique et filtres par période

### Buanderie (Laundry)

- **Mes activités** : page listant les actions de l’utilisateur connecté (causer_id = moi) sur les collectes et types de linge.
- **Filtre par période** : date début + date fin (obligatoire ou défaut : aujourd’hui / cette semaine).
- Afficher : date/heure, type d’action (collecte mise à jour, statut changé, type de linge créé/modifié/supprimé), description, chambre si applicable.

### Housekeeping (Service des étages)

- **Mes activités** : même principe pour les actions housekeeping (début/fin de nettoyage, etc.).
- **Filtre par période** : date début + date fin.
- Afficher : tâches démarrées/terminées, chambres, horaires.

Implémentation : réutiliser le modèle `ActivityLog` (causer_id, properties->action_type, created_at) et filtrer côté contrôleur avec `where('causer_id', auth()->id())` et `whereBetween('created_at', [$dateDebut, $dateFin])`.

---

## 7. Plan d’implémentation proposé

| Phase | Contenu |
|-------|--------|
| **Phase 1** | Création utilisateur laundry (déjà possible) ; ajout dans la doc / interface des descriptions des rôles « Housekeeping » et « Laundry » ; **Historique avec filtre par période** pour Laundry et Housekeeping (pages « Mes activités »). |
| **Phase 2** | Modèle **linge client** (table `client_linen` ou équivalent) ; statuts et écrans **Réception** (dépôt linge client) et **Buanderie** (liste linge client réception + récupération / traitement). |
| **Phase 3** | **Housekeeping** : signalement « linge client oublié en chambre » à la fin du nettoyage ; **Buanderie** : liste « Linge client – Chambre » + notifications. |
| **Phase 4** | Notifications réception → buanderie et housekeeping → buanderie ; rappels ou synthèses (optionnel). |

Ce document servira de référence pour les prochaines implémentations (linges client réception/chambre, notifications, historiques).

---

## 8. Implémenté (Phase 1)

- **Création utilisateur Laundry** : Super Admin → Utilisateurs → Créer (ou modal) → choisir Rôle « Laundry » et Hôtel. La page de création utilise un sélecteur de rôle unique et affiche les descriptions de tous les rôles (dont Housekeeping et Laundry).
- **Interfaces Buanderie** : Tableau de bord, Collectes de linge, Types de linge, **Mes activités** (nouveau).
- **Interfaces Service des étages** : Tableau de bord, Chambres à nettoyer, **Mes activités** (nouveau).
- **Historique filtré par période** : Pour Laundry et Housekeeping, la page « Mes activités » permet de filtrer par date début / date fin et affiche uniquement les actions personnelles (causer = utilisateur connecté) sur la période choisie.
