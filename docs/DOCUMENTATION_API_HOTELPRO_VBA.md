# API HotelPro – Intégration avec le logiciel de gestion HotelPro (VBA)

Ce document décrit comment utiliser l’API REST de l’application HotelPro (serveur web Laravel) depuis un logiciel de gestion hôtelière développé en **VBA** (Excel, Access, etc.).

---

## 1. Vue d’ensemble

L’API est déjà en place sous le préfixe **`/api/v1`**. Elle permet notamment de :

- Consulter les hôtels, types de chambres et chambres  
- Vérifier les disponibilités  
- Créer des réservations  
- (Optionnel) Lister / gérer les réservations avec authentification par token  

**Base URL typique :**  
`https://votre-domaine.com/api/v1`  
ou en local :  
`http://localhost/api/v1`

---

## 2. Endpoints disponibles

### 2.1 Endpoints publics (sans authentification)

Utilisables directement depuis VBA sans token.

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/health` | Vérifier que l’API répond |
| GET | `/api/v1/hotels` | Liste des hôtels |
| GET | `/api/v1/hotels/{id}` | Détail d’un hôtel |
| GET | `/api/v1/hotels/{id}/room-types` | Types de chambres de l’hôtel |
| GET | `/api/v1/hotels/{id}/rooms` | Chambres de l’hôtel |
| GET | `/api/v1/hotels/{id}/availability` | Disponibilités (paramètres : `check_in_date`, `check_out_date`, optionnel `room_type_id`) |
| POST | `/api/v1/hotels/{id}/reservations` | Créer une réservation (rate limit : 10 req/min) |

### 2.2 Endpoints protégés (authentification requise)

Pour lire les réservations, valider/rejeter, ou statistiques, il faut un **token d’API** (Laravel Sanctum). Voir section 4.

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/v1/hotels/{id}/reservations` | Liste des réservations (filtres : `status`, `date_from`, `date_to`, `per_page`) |
| GET | `/api/v1/reservations` | Liste des réservations (avec `hotel_id`, `status`) |
| GET | `/api/v1/reservations/{id}` | Détail d’une réservation |
| POST | `/api/v1/reservations/{id}/validate` | Valider une réservation |
| POST | `/api/v1/reservations/{id}/reject` | Rejeter une réservation |
| GET | `/api/v1/stats` | Statistiques |

---

## 3. Exemples de requêtes (pour VBA)

### 3.1 Health check

- **GET** `https://votre-domaine.com/api/health`  
- Réponse attendue : `{"success":true,"status":"healthy",...}`

### 3.2 Liste des hôtels

- **GET** `https://votre-domaine.com/api/v1/hotels`  
- Réponse : `{"success":true,"data":[...],"count":n}`

### 3.3 Types de chambres d’un hôtel

- **GET** `https://votre-domaine.com/api/v1/hotels/1/room-types`  
- Remplacez `1` par l’ID de l’hôtel.

### 3.4 Disponibilités

- **GET**  
  `https://votre-domaine.com/api/v1/hotels/1/availability?check_in_date=2025-02-10&check_out_date=2025-02-12`  
- Optionnel : `&room_type_id=2` pour filtrer par type de chambre.

### 3.5 Créer une réservation (POST)

- **URL :** `https://votre-domaine.com/api/v1/hotels/1/reservations`  
- **Method :** POST  
- **Content-Type :** `application/json`  
- **Body (JSON) :**

```json
{
  "room_type_id": 1,
  "room_id": null,
  "check_in_date": "2025-02-10",
  "check_out_date": "2025-02-12",
  "client_name": "Dupont Jean",
  "client_email": "jean.dupont@email.com",
  "client_phone": "+33612345678",
  "guests_count": 2,
  "data": {}
}
```

- `room_id` peut être omis ou null (l’application peut assigner une chambre).  
- Réponse succès (201) : `{"success":true,"message":"Réservation créée avec succès","data":{...}}`

---

## 4. Authentification (endpoints protégés)

Pour appeler les routes protégées depuis VBA :

1. **Installer Laravel Sanctum** (si ce n’est pas déjà fait) :
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

2. **Créer un token pour un utilisateur** (une fois, via tinker ou une route dédiée) :
   ```php
   $user = User::find(1);
   $token = $user->createToken('HotelPro-VBA')->plainTextToken;
   ```

3. **Dans VBA**, envoyer le token dans chaque requête :
   - Header : `Authorization: Bearer VOTRE_TOKEN_ICI`

VBA (exemple pour une requête GET avec token) :

```vba
objRequest.SetRequestHeader "Authorization", "Bearer " & sToken
```

---

## 5. CORS et hébergement

- Si le front VBA tourne dans un navigateur (ex. contrôle WebBrowser), les requêtes sont soumises à CORS. Il faut autoriser l’origine dans la config CORS Laravel (`config/cors.php`).
- Depuis Excel/Access (WinHttp / MSXML2), les appels sont faits par le client Windows, pas par le navigateur : CORS ne s’applique pas. Il faut en revanche que le serveur soit accessible (URL publique ou IP du serveur dans le réseau local).

---

## 6. Résumé des étapes pour lier VBA à l’API

1. **Déployer** l’application Laravel (WAMP, serveur dédié, etc.) et noter l’URL de base (ex. `https://hotelpro.mon-domaine.com`).  
2. **Tester** dans un navigateur ou Postman :  
   - `GET /api/health`  
   - `GET /api/v1/hotels`  
   - `GET /api/v1/hotels/1/availability?check_in_date=...&check_out_date=...`  
   - `POST /api/v1/hotels/1/reservations` avec un JSON valide.  
3. **Dans le projet VBA** :  
   - Utiliser le module d’exemple fourni (`HotelProApiClient.bas`) en renseignant l’URL de base.  
   - Pour les actions sans login (dispos, création de réservation), rien d’autre à faire.  
   - Pour les actions protégées, générer un token Sanctum et le stocker de façon sécurisée (variable, config, etc.) et l’envoyer en `Authorization: Bearer ...`.  
4. **Sécurité** : en production, utiliser HTTPS et ne pas exposer le token dans le code source (lecture depuis config ou saisie sécurisée).

Une fois ces points en place, votre logiciel HotelPro en VBA peut interroger la disponibilité et créer des réservations via l’API, et optionnellement lister/valider les réservations avec un token.
