# Plans d'action – Mises à jour HotelPro

Ce document détaille les plans d'action pour les améliorations identifiées lors de l'analyse du projet. Chaque plan est découpé en étapes concrètes avec critères de validation.

---

## Plan 1 – Cohérence du code (ReservationService)  
**Priorité : Haute**  
**Durée estimée : 30 min**

### Objectif
Uniformiser le nommage et les commentaires dans `app/Services/ReservationService.php` pour alignement avec le reste du projet.

### Étapes

| # | Action | Détail |
|---|--------|--------|
| 1.1 | Renommer la variable `$Reservation` | Remplacer par `$reservation` dans toute la classe (paramètres, variables locales, return). |
| 1.2 | Corriger les appels de relation | Remplacer `$hotel->Reservations()` par `$hotel->reservations()`. |
| 1.3 | Mettre à jour les commentaires | Remplacer "pré-réservation" / "Pré-réservation" par "réservation" / "Réservation" dans les blocs doc et les commentaires. |
| 1.4 | Vérifier les méthodes privées | Dans `saveIdentityDocuments()` et `saveSignature()`, utiliser `$reservation` si le paramètre est encore nommé `$Reservation`. |
| 1.5 | Exécuter les tests | `php artisan test` (ou `composer test`) et vérifier qu’aucun test ne régresse. |

### Critères de validation
- [ ] Aucune occurrence de `$Reservation` ou `Reservations()` dans `ReservationService.php`
- [ ] Aucun commentaire "pré-réservation" dans ce fichier
- [ ] Suite de tests verte

### Fichiers concernés
- `app/Services/ReservationService.php`

---

## Plan 2 – Documentation de l’API (OpenAPI / Swagger)  
**Priorité : Moyenne**  
**Durée estimée : 2–4 h**

### Objectif
Exposer une documentation API (type OpenAPI/Swagger) pour les endpoints `/api/v1/*` et clarifier la stratégie pour les routes legacy.

### Étapes

| # | Action | Détail |
|---|--------|--------|
| 2.1 | Choisir l’outil | Installer un package Laravel (ex. `darkaonline/l5-swagger` ou `dedoc/laravel-openapi`) et le configurer (config, route `/api/documentation`). |
| 2.2 | Décrire les endpoints publics | Documenter : `GET /api/v1/hotels`, `GET /api/v1/hotels/{id}`, `GET /api/v1/hotels/{id}/room-types`, `rooms`, `availability`, `POST .../reservations` (paramètres, réponses, codes HTTP). |
| 2.3 | Décrire les endpoints protégés | Documenter : `GET /api/v1/user`, `GET /api/v1/hotels/{id}/reservations`, routes sous `reservations` (index, show, validate, reject), `GET /api/v1/stats`. Indiquer l’auth Sanctum (Bearer). |
| 2.4 | Documenter modèles et erreurs | Décrire les schémas (Hotel, Reservation, RoomType, etc.) et les réponses d’erreur (422, 401, 404). |
| 2.5 | Stratégie backward compatibility | Dans un fichier (ex. `docs/api-versions.md`) : lister les routes sans version, date de dépréciation prévue, et redirection ou message dans les réponses. |
| 2.6 | Mettre à jour `api.php` | Remplacer le commentaire "Documentation: /api/documentation (à créer)" par "Documentation: /api/documentation" et ajouter un lien dans le README ou la doc projet si existante. |

### Critères de validation
- [ ] `/api/documentation` (ou équivalent) accessible et affiche tous les endpoints v1
- [ ] Exemples de requêtes/réponses cohérents avec le code
- [ ] Fichier de stratégie des versions créé et relu

### Fichiers concernés
- `routes/api.php`
- Nouveaux : `config/swagger.php` (ou équivalent), annotations/classes OpenAPI, `docs/api-versions.md`

---

## Plan 3 – Métadonnées du projet (composer.json)  
**Priorité : Moyenne**  
**Durée estimée : 15 min**

### Objectif
Rendre le projet identifiable dans Composer et dans les outils (dépôts, docs).

### Étapes

| # | Action | Détail |
|---|--------|--------|
| 3.1 | Mettre à jour `name` | Remplacer `"laravel/laravel"` par un nom de namespace (ex. `"phoenixgroup/hotelpro"` ou `"votre-org/hotelpro"`). |
| 3.2 | Rédiger la description | Remplacer la description squelette par une phrase claire (ex. "Application de gestion hôtelière multi-établissements – réservations, réception, fiches de police."). |
| 3.3 | Ajouter des mots-clés | Ajouter un tableau `keywords` : ex. `["hotel", "reservation", "reception", "laravel", "multi-tenant"]`. |
| 3.4 | Vérifier l’autoload | Lancer `composer dump-autoload` et s’assurer qu’aucune régression (tests, artisan). |

### Critères de validation
- [ ] `composer.json` contient un `name`, une `description` et des `keywords` adaptés à HotelPro
- [ ] `composer validate` sans erreur
- [ ] Aucun impact sur le fonctionnement (tests ou smoke test)

### Fichiers concernés
- `composer.json`

---

## Plan 4 – Documentation Oracle et modules annexes  
**Priorité : Basse**  
**Durée estimée : 1–2 h**

### Objectif
Clarifier l’usage d’Oracle et le rôle du launcher / electron-example pour les futurs mainteneurs.

### Étapes

| # | Action | Détail |
|---|--------|--------|
| 4.1 | Auditer l’usage Oracle | Rechercher dans le code les usages de `oracle_dsn`, `oracle_username`, `oracle_password`, `oracle_synced_at`, `oracle_id`. Lister les modèles, jobs, commandes et configs concernés. |
| 4.2 | Rédiger une note Oracle | Créer `docs/oracle-integration.md` (ou section dans un README technique) : objectif (synchro PMS ?), champs en BDD, où la synchro est appelée, comment configurer (variables d’env), et si la fonctionnalité est activée, en beta ou désactivée. |
| 4.3 | Documenter le launcher | Dans `launcher/README.md` (ou à la racine) : objectif (lancement local, poste réception ?), prérequis, commandes (ex. scripts .bat/.ps1), et lien avec l’app (URL, env). |
| 4.4 | Documenter electron-example | Dans `electron-example/README.md` : but (app desktop, kiosque ?), comment lancer, build éventuel, et relation avec l’URL de l’app HotelPro. |

### Critères de validation
- [ ] Un document décrit clairement l’état et l’usage d’Oracle
- [ ] Launcher et electron-example ont une courte doc (objectif + comment lancer)

### Fichiers concernés
- `app/Models/Hotel.php` (référence)
- Nouveaux ou à compléter : `docs/oracle-integration.md`, `launcher/README.md`, `electron-example/README.md`

---

## Ordre de réalisation recommandé

1. **Plan 1** (ReservationService) – rapide, sans impact fonctionnel, améliore la cohérence.
2. **Plan 3** (composer.json) – rapide, utile pour tout le monde.
3. **Plan 2** (API) – dès que l’API est utilisée par des tiers ou une autre app.
4. **Plan 4** (Oracle + launcher + electron) – quand vous avez un peu de temps pour la doc technique.

---

## Suivi

Vous pouvez cocher les critères de validation au fur et à mesure et noter la date de réalisation :

| Plan | Date réalisé | Notes |
|------|--------------|-------|
| Plan 1 – ReservationService | | |
| Plan 2 – Doc API | | |
| Plan 3 – composer.json | | |
| Plan 4 – Oracle & modules | | |

---

*Document généré pour le projet HotelPro – à adapter selon votre organisation et vos priorités.*
