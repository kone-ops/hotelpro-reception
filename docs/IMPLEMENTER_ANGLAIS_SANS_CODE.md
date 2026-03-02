# Implémenter l’anglais sans toucher au code

L’anglais est déjà activé dans l’application (sélecteur Langue dans la topbar). Pour que **tout** s’affiche en anglais quand l’utilisateur choisit « English », il suffit de **compléter ou modifier les fichiers de traduction**. Aucune modification de code (PHP, Blade, JS) n’est nécessaire.

---

## Méthode en 3 étapes (uniquement des fichiers de traduction)

### Étape 1 : Fichiers concernés

Tout se fait dans le dossier **`lang/`** à la racine du projet :

- **`lang/en.json`** — textes courts déjà utilisés dans l’app (Dashboard, Profile, Log Out, etc.).
- **`lang/en/`** — un fichier par thème :
  - `common.php` — boutons, actions, « Langue », « Français », « Anglais ».
  - `modules.php` — Housekeeping, Laundry.
  - `super.php` — écrans Super Admin (hôtels, modules, liste, configurer, etc.).
  - `reception.php` — réception, fiche de police.
  - `design.php` — champs du formulaire (Nom, Prénom, Type de chambre, etc.).

Vous ne modifiez **que** ces fichiers (et éventuellement vous en créez d’autres dans `lang/en/` si vous ajoutez de nouveaux thèmes plus tard). Aucun fichier en dehors de `lang/` n’est à toucher.

---

### Étape 2 : Compléter l’anglais en vous calant sur le français

1. **Ouvrir le fichier français correspondant** (ex. `lang/fr/super.php`).
2. **Ouvrir le fichier anglais** avec la **même structure** (ex. `lang/en/super.php`).
3. **Vérifier que les mêmes clés existent** dans les deux. Si une clé manque en anglais, l’ajouter avec la traduction en anglais (en gardant exactement le même nom de clé).
4. **Remplacer ou saisir le texte anglais** pour chaque valeur. Ne pas modifier les noms de clés (à gauche du `=>`), seulement les chaînes à droite.

Exemple dans `lang/en/super.php` :

```php
// Même structure que lang/fr/super.php, valeurs en anglais
'hotels' => [
    'list' => 'Hotel list',
    'create' => 'Create hotel',
    'configure' => 'Configure',
    // ...
],
```

Pour **`lang/en.json`** : même principe. Ouvrir `lang/fr.json`, reprendre les **mêmes clés** (à gauche), et mettre la traduction anglaise à droite. Ne pas modifier la structure JSON (guillemets, virgules).

---

### Étape 3 : Vérifier dans l’interface

1. Se connecter à l’application.
2. Dans la **topbar**, cliquer sur « Langue » puis choisir **« English »**.
3. Parcourir les écrans (dashboard, Super Admin, réception, etc.) : tout ce qui est géré par les fichiers `lang/en/` et `lang/en.json` s’affichera en anglais.
4. Si un libellé reste en français, c’est qu’il manque une entrée (ou une clé) dans le fichier anglais correspondant : retour à l’étape 2 pour ajouter/corriger **uniquement** dans `lang/en/` ou `lang/en.json`.

---

## Règles à respecter (pour ne rien casser)

- **Ne pas modifier** les noms de clés (ex. `'hotels'`, `'list'`, `'configure'`). Seul le **texte** (valeur) doit être en anglais.
- **Ne pas modifier** les fichiers dans `app/`, `resources/views/`, `routes/`, etc. Tout se fait dans `lang/`.
- **Garder la même structure** que le fichier français : mêmes tableaux, mêmes clés. Seules les chaînes affichées changent.

---

## Résumé

| Objectif | Action |
|----------|--------|
| Avoir l’anglais partout quand l’utilisateur choisit « English » | Compléter/corriger **uniquement** les fichiers dans `lang/en/` et `lang/en.json`. |
| Ajouter un nouveau libellé en anglais | Ajouter la même clé dans le bon fichier sous `lang/en/` (ou dans `lang/en.json`) avec la traduction. |
| Corriger une traduction anglaise | Modifier la valeur dans `lang/en/` ou `lang/en.json`. |

Aucun code à écrire : **seulement édition des fichiers de traduction** dans `lang/`.
