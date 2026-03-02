# Procédures – Gestion des espaces (5 catégories) du Service technique en base de données

**Référence officielle** des procédures pour le module **Espaces** du Service technique. L’implémentation correspondante dans le projet se trouve dans : migration `database/migrations/2026_02_04_100000_create_maintenance_areas_table.php`, modèle `app/Modules/Maintenance/Models/MaintenanceArea.php`, contrôleur `app/Modules/Maintenance/Controllers/MaintenanceAreaController.php`, vues `resources/views/maintenance/areas/`, routes sous le préfixe `maintenance` (voir `routes/web.php`).

Ce document décrit **comment gérer en base de données** les cinq catégories d’espaces (Espaces publics, Espaces techniques, Espaces extérieurs, Loisirs, Administration), **sans modifier le code** : structure des données et procédures à suivre (utilisation de l’application ou intervention directe en BDD).

---

## 1. Structure des données en base

### 1.1 Table concernée : `maintenance_areas`

| Colonne | Type | Description |
|--------|------|-------------|
| `id` | entier (clé primaire) | Identifiant unique de l’espace |
| `hotel_id` | entier (clé étrangère → `hotels.id`) | Hôtel auquel appartient l’espace |
| `category` | chaîne (50 car.) | Code de la catégorie (voir ci‑dessous) |
| `name` | chaîne (255) | Nom de l’espace (ex. Hall, Piscine, Toiture) |
| `description` | texte (optionnel) | Description ou précisions |
| `technical_state` | chaîne (20) | État technique : `normal`, `issue`, `maintenance`, `out_of_service` |
| `notes` | texte (optionnel) | Notes libres (ex. remarques du technicien) |
| `created_at` | date/heure | Date de création |
| `updated_at` | date/heure | Dernière mise à jour |

### 1.2 Valeurs autorisées pour `category`

| Code en BDD | Libellé affiché dans l’application |
|-------------|-------------------------------------|
| `espaces_publics` | Espaces publics |
| `espaces_techniques` | Espaces techniques |
| `espaces_exterieurs` | Espaces extérieurs |
| `loisirs` | Loisirs |
| `administration` | Administration |

### 1.3 Valeurs autorisées pour `technical_state`

| Valeur en BDD | Signification |
|---------------|----------------|
| `normal` | Aucun problème, espace OK |
| `issue` | Problème signalé (à traiter) |
| `maintenance` | En maintenance |
| `out_of_service` | Hors service |

---

## 2. Procédures d’utilisation via l’application (sans toucher à la BDD)

Une fois le module implémenté et les migrations exécutées, le service technique gère les espaces **uniquement via l’interface** :

1. **Accéder aux espaces**  
   Connexion avec un compte ayant le rôle **maintenance** → menu **Service technique** → **Espaces** (ou bouton « Espaces (publics, techniques, etc.) » sur le tableau de bord).

2. **Choisir une catégorie**  
   Sur la page « Espaces à suivre », cliquer sur **Voir et gérer** pour l’une des 5 catégories (Espaces publics, Espaces techniques, Espaces extérieurs, Loisirs, Administration).

3. **Ajouter un espace**  
   Dans la liste de la catégorie → **Ajouter un espace** → renseigner **Nom** (obligatoire) et **Description** (optionnel) → **Enregistrer**. L’application crée une ligne dans `maintenance_areas` avec `technical_state = normal`.

4. **Changer l’état d’un espace**  
   Dans la liste, utiliser les boutons **Normal**, **Problème**, **Maintenance**, **Hors service**. L’application met à jour `technical_state` (et éventuellement `notes`) dans `maintenance_areas`.

5. **Supprimer un espace**  
   Cliquer sur l’icône **Supprimer** (corbeille) à côté de l’espace → confirmer. L’application supprime la ligne correspondante dans `maintenance_areas`.

Aucune manipulation directe en base n’est nécessaire pour un usage normal.

---

## 3. Procédures d’intervention directe en base de données

Utile pour **diagnostic**, **import**, **correction** ou **sauvegarde** sans passer par l’interface.

### 3.1 Prérequis

- Accès à la base (phpMyAdmin, client SQL, ou ligne de commande).
- Connaissance de l’`id` de l’hôtel concerné (table `hotels`, colonne `id`).

### 3.2 Consulter les espaces d’un hôtel

Requête type (à adapter selon le SGBD) :

```sql
SELECT id, hotel_id, category, name, technical_state, notes, created_at
FROM maintenance_areas
WHERE hotel_id = 1
ORDER BY category, name;
```

Remplacer `1` par l’`id` de l’hôtel. Pour filtrer par catégorie, ajouter par exemple :  
`AND category = 'espaces_publics'`.

### 3.3 Ajouter un espace manuellement

Exemple : ajouter « Hall » en Espaces publics pour l’hôtel 1.

```sql
INSERT INTO maintenance_areas (hotel_id, category, name, description, technical_state, notes, created_at, updated_at)
VALUES (1, 'espaces_publics', 'Hall', 'Hall d''entrée', 'normal', NULL, NOW(), NOW());
```

- `category` : une des valeurs listées au § 1.2.  
- `technical_state` : une des valeurs du § 1.3.  
- `description` et `notes` : optionnels (NULL si vide).

### 3.4 Modifier l’état technique d’un espace

Exemple : passer l’espace d’`id` 5 en « problème signalé ».

```sql
UPDATE maintenance_areas
SET technical_state = 'issue', notes = 'Problème signalé manuellement en BDD', updated_at = NOW()
WHERE id = 5 AND hotel_id = 1;
```

Toujours restreindre par `hotel_id` (et éventuellement `id`) pour éviter de modifier un autre hôtel.

### 3.5 Supprimer un espace

Exemple : supprimer l’espace d’`id` 5 pour l’hôtel 1.

```sql
DELETE FROM maintenance_areas
WHERE id = 5 AND hotel_id = 1;
```

Vérifier d’abord avec un `SELECT` que la ligne correspond bien à l’espace voulu.

### 3.6 Exporter / importer des espaces

- **Export** : depuis phpMyAdmin, onglet **Exporter** sur la table `maintenance_areas`, en filtrant éventuellement par `hotel_id` (requête personnalisée ou export puis filtrage).
- **Import** : préparer un fichier CSV ou SQL avec les colonnes attendues (`hotel_id`, `category`, `name`, etc.) et les valeurs autorisées pour `category` et `technical_state`, puis utiliser **Importer** (phpMyAdmin) ou une commande `LOAD DATA` / script d’import. Vérifier après import que `hotel_id` et les codes de catégorie/état sont valides.

---

## 4. Cohérence des données (règles à respecter)

Lors d’une intervention directe en BDD, respecter les règles suivantes pour rester cohérent avec l’application :

1. **`hotel_id`** : doit correspondre à un `id` existant dans la table `hotels`.
2. **`category`** : uniquement `espaces_publics`, `espaces_techniques`, `espaces_exterieurs`, `loisirs`, `administration`.
3. **`technical_state`** : uniquement `normal`, `issue`, `maintenance`, `out_of_service`.
4. **`name`** : non vide, longueur raisonnable (≤ 255 caractères si la base le limite).
5. Ne pas supprimer des lignes référencées ailleurs si d’autres tables pointent vers `maintenance_areas` (actuellement, dans l’implémentation décrite, il n’y a pas de clés étrangères vers `maintenance_areas`).

---

## 5. Résumé des procédures

| Objectif | Procédure recommandée |
|----------|------------------------|
| Utilisation normale (ajout, modification d’état, suppression d’espaces) | Utiliser l’interface **Service technique** → **Espaces** → choisir la catégorie, puis les boutons et formulaires prévus. Aucune action en BDD. |
| Vérifier ou analyser les données | Se connecter à la BDD (phpMyAdmin, SQL) et exécuter des `SELECT` sur `maintenance_areas` en filtrant par `hotel_id` et éventuellement `category` ou `technical_state`. |
| Créer ou modifier des espaces en masse / après import | Utiliser des `INSERT` ou `UPDATE` SQL en respectant les valeurs autorisées pour `category` et `technical_state`, et en gardant `hotel_id` cohérent. |
| Corriger une valeur (état, nom, etc.) | Utiliser un `UPDATE` ciblé sur la ligne concernée (`id` + `hotel_id`). |
| Supprimer un espace en BDD | Utiliser un `DELETE` sur `maintenance_areas` en ciblant l’`id` (et `hotel_id`) de l’espace. |

En suivant ces procédures, la gestion des cinq catégories d’espaces reste cohérente avec le module Service technique, que les opérations soient faites via l’application ou ponctuellement en base de données.
