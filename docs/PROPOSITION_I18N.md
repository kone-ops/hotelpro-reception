# Proposition détaillée – Système bilingue (FR/EN) avec changement de langue dans l’interface

## 1. Objectif

Permettre à l’utilisateur de **changer la langue directement depuis l’interface** (topbar une fois connecté, ou bandeau sur les pages login/register), sans intervention technique. L’application affiche les libellés en **français** ou **anglais** selon le choix, avec une **implémentation déjà en place** : rien à coder pour utiliser le système.

---

## 2. Expérience utilisateur (UX)

### 2.1 Utilisateur connecté (interface principale)

- **Emplacement** : dans la **topbar** (barre du haut), à gauche du bloc « Profil utilisateur ».
- **Comportement** :
  - Un bouton **« Langue : Français »** (ou **« Langue : English »**) avec une icône globe.
  - Au clic : menu déroulant avec **Français** et **English**.
  - La langue active est indiquée par une coche.
  - Clic sur une langue → enregistrement du choix en session → **rechargement de la page** dans la nouvelle langue (redirection vers la même page).
- **Visibilité** : le libellé « Langue » est masqué sur très petit écran (icône globe conservée).

### 2.2 Utilisateur non connecté (login, register, mot de passe oublié, etc.)

- **Emplacement** : en **haut à droite** de l’écran (pages avec layout `guest`).
- **Comportement** : liens **Français | English**. Clic → même page rechargée dans la langue choisie.
- Le choix est stocké en session et reste actif après connexion.

### 2.3 Ordre de détermination de la langue

1. **Session** : si l’utilisateur a déjà choisi une langue (bouton topbar ou lien guest), cette valeur est utilisée.
2. **Préférence utilisateur** (optionnel, pour plus tard) : si une colonne `locale` existe sur `users` et une méthode `getPreferredLocale()` est définie, elle peut être utilisée à la connexion.
3. **Navigateur** : en-tête HTTP `Accept-Language` si aucune session ni préférence.
4. **Défaut** : valeur de `config('app.locale')` (ex. `fr`).

---

## 3. Architecture technique (déjà en place)

### 3.1 Fichiers de traduction

| Emplacement | Rôle |
|------------|------|
| `lang/fr.json` et `lang/en.json` | Clés courtes pour les chaînes utilisées en `__('Dashboard')`, `__('Profile')`, etc. |
| `lang/fr/common.php` et `lang/en/common.php` | Libellés communs : Langue, Français, Anglais, Actions, Retour, Enregistrer, etc. |
| `lang/fr/modules.php` et `lang/en/modules.php` | Libellés des modules (Housekeeping, Laundry). |
| `lang/fr/super.php` et `lang/en/super.php` | Interface Super Admin : hôtels, modules, liste, configurer, etc. |
| `lang/fr/reception.php` et `lang/en/reception.php` | Réception : tableau de bord, fiche de police. |
| `lang/fr/design.php` et `lang/en/design.php` | Champs du formulaire (design hôtel) : Nom, Prénom, Type de chambre, etc. |

Aucune action requise pour ces fichiers : ils sont déjà créés et utilisés par le middleware et les vues adaptées.

### 3.2 Configuration

- **Fichier** : `config/app.php`
  - `locale` : langue par défaut (ex. `fr`).
  - `fallback_locale` : langue de repli si une clé manque (ex. `en`).
  - `supported_locales` : `['fr', 'en']` (langues proposées dans le sélecteur).

- **Variables d’environnement** (optionnel) : `.env`
  - `APP_LOCALE=fr`
  - `APP_FALLBACK_LOCALE=en`

### 3.3 Route de changement de langue

- **URL** : `GET /locale/{locale}` (ex. `/locale/en`).
- **Nom de route** : `locale.switch`.
- **Comportement** : vérification que `{locale}` est dans `supported_locales`, enregistrement en session, puis `redirect()->back()` pour rester sur la page courante (avec la nouvelle langue).

Aucune action requise : la route est déjà définie dans `routes/web.php`.

### 3.4 Middleware

- **Classe** : `App\Http\Middleware\SetLocale`.
- **Rôle** : à chaque requête web, détermine la locale (session → utilisateur → Accept-Language → défaut) et appelle `App::setLocale($locale)`.
- **Enregistrement** : middleware ajouté au groupe `web` dans `bootstrap/app.php`.

Aucune action requise : le middleware est déjà en place.

### 3.5 Interfaces déjà modifiées

- **Topbar** (`resources/views/layouts/topbar.blade.php`) : sélecteur de langue en dropdown « Langue : Français / English » avec icône globe.
- **Layout invité** (`resources/views/layouts/guest.blade.php`) : liens « Français | English » en haut à droite.
- **Navigation guest** (`resources/views/layouts/navigation.blade.php`) : liens FR/EN si ce layout est utilisé.
- **Exemple de page** : `super/hotels/modules-index.blade.php` utilise les clés de traduction (`super.hotels.*`, `modules.*`).
- **SettingsResolver** : `getAvailableModules()` utilise `__('modules.housekeeping.label')` et `__('modules.laundry.description')`.

---

## 4. Implémentation directe – Récapitulatif

Tout est déjà implémenté côté code. En résumé :

| Élément | Statut |
|--------|--------|
| Fichiers de langue (fr/en, JSON + PHP par domaine) | Créés |
| Config `app.locale`, `fallback_locale`, `supported_locales` | Configuré |
| Middleware `SetLocale` et enregistrement dans `web` | En place |
| Route `locale.switch` (GET /locale/{locale}) | En place |
| Sélecteur dans la **topbar** (dropdown Langue : Français / English) | En place |
| Sélecteur sur les pages **guest** (Français \| English) | En place |
| Exemple de vue et `SettingsResolver` en traduction | En place |
| Documentation (I18N.md, la présente proposition) | En place |

**Vous n’avez rien à faire dans le code** pour que l’utilisateur puisse changer la langue depuis la topbar (et depuis les pages login/register). Il suffit de tester en cliquant sur « Langue » dans la barre du haut puis sur « English » ou « Français ».

---

## 5. Pour aller plus loin (optionnel)

- **Étendre les traductions** : remplacer progressivement le texte en dur dans les autres vues par `{{ __('fichier.clé') }}` en s’appuyant sur les fichiers existants dans `lang/`.
- **Persister la préférence** : ajouter une colonne `locale` sur la table `users`, une méthode `getPreferredLocale()` sur le modèle User, et éventuellement une option « Langue » dans la page Profil pour enregistrer la préférence en base.
- **Nouvelles langues** : créer `lang/{code}/` et `lang/{code}.json`, puis ajouter le code dans `supported_locales` ; le sélecteur affichera automatiquement la nouvelle langue dans le dropdown de la topbar.

---

## 6. Résumé en une phrase

L’utilisateur peut **changer la langue directement sur son interface** (topbar en dropdown « Langue : Français / English », ou liens « Français | English » sur la page de connexion) ; l’architecture bilingue est en place et opérationnelle sans modification de code supplémentaire.
