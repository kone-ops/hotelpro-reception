# 📥 GUIDE COMPLET : Téléchargement et Organisation des Assets

## 📋 Liste des fichiers à télécharger

### 1. **Bootstrap 5.3.0**

#### Fichiers à télécharger :
- **CSS** : `bootstrap.min.css`
  - Lien : `https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css`
  - Destination : `public/assets/vendor/bootstrap/bootstrap.min.css`

- **JavaScript** : `bootstrap.bundle.min.js`
  - Lien : `https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js`
  - Destination : `public/assets/vendor/bootstrap/bootstrap.bundle.min.js`

---

### 2. **Bootstrap Icons 1.11.0**

#### Fichiers à télécharger :
- **CSS** : `bootstrap-icons.css`
  - Lien : `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css`
  - Destination : `public/assets/vendor/bootstrap-icons/bootstrap-icons.css`

- **Font WOFF** : `bootstrap-icons.woff`
  - Lien : `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/fonts/bootstrap-icons.woff`
  - Destination : `public/assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff`

- **Font WOFF2** : `bootstrap-icons.woff2`
  - Lien : `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/fonts/bootstrap-icons.woff2`
  - Destination : `public/assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2`

---

### 3. **jQuery 3.7.0**

#### Fichiers à télécharger :
- **JavaScript** : `jquery-3.7.0.min.js`
  - Lien : `https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js`
  - Destination : `public/assets/vendor/jquery/jquery-3.7.0.min.js`

---

### 4. **Select2 4.1.0**

#### Fichiers à télécharger :
- **CSS Principal** : `select2.min.css`
  - Lien : `https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css`
  - Destination : `public/assets/vendor/select2/css/select2.min.css`

- **CSS Theme Bootstrap 5** : `select2-bootstrap-5-theme.min.css`
  - Lien : `https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css`
  - Destination : `public/assets/vendor/select2/css/select2-bootstrap-5-theme.min.css`

- **JavaScript** : `select2.min.js`
  - Lien : `https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js`
  - Destination : `public/assets/vendor/select2/js/select2.min.js`

---

### 5. **IntlTelInput 18.2.1**

#### Fichiers à télécharger :
- **CSS** : `intlTelInput.css`
  - Lien : `https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css`
  - Destination : `public/assets/vendor/intl-tel-input/css/intlTelInput.css`

- **JavaScript Principal** : `intlTelInput.min.js`
  - Lien : `https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js`
  - Destination : `public/assets/vendor/intl-tel-input/js/intlTelInput.min.js`

- **JavaScript Utils** : `utils.js`
  - Lien : `https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js`
  - Destination : `public/assets/vendor/intl-tel-input/js/utils.js`

---

### 6. **Signature Pad 4.1.7**

#### Fichiers à télécharger :
- **JavaScript** : `signature-pad.umd.min.js`
  - Lien : `https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js`
  - Destination : `public/assets/vendor/signature-pad/signature-pad.umd.min.js`

---

### 7. **SweetAlert2 11.x**

#### Fichiers à télécharger :
- **CSS** : `sweetalert2.min.css`
  - Lien : `https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css`
  - Destination : `public/assets/vendor/sweetalert2/sweetalert2.min.css`

- **JavaScript** : `sweetalert2.min.js`
  - Lien : `https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js`
  - Destination : `public/assets/vendor/sweetalert2/sweetalert2.min.js`

---

## 📁 Structure finale des dossiers

```
public/
└── assets/
    └── vendor/
        ├── bootstrap/
        │   ├── bootstrap.min.css
        │   └── bootstrap.bundle.min.js
        │
        ├── bootstrap-icons/
        │   ├── bootstrap-icons.css
        │   └── fonts/
        │       ├── bootstrap-icons.woff
        │       └── bootstrap-icons.woff2
        │
        ├── jquery/
        │   └── jquery-3.7.0.min.js
        │
        ├── select2/
        │   ├── css/
        │   │   ├── select2.min.css
        │   │   └── select2-bootstrap-5-theme.min.css
        │   └── js/
        │       └── select2.min.js
        │
        ├── intl-tel-input/
        │   ├── css/
        │   │   └── intlTelInput.css
        │   └── js/
        │       ├── intlTelInput.min.js
        │       └── utils.js
        │
        ├── signature-pad/
        │   └── signature-pad.umd.min.js
        │
        └── sweetalert2/
            ├── sweetalert2.min.css
            └── sweetalert2.min.js
```

---

## 🔧 Comment télécharger

### Méthode 1 : Navigateur Web (Recommandé)

1. **Ouvrir le lien** dans votre navigateur
2. **Clic droit** sur la page → **Enregistrer sous...**
3. **Choisir le bon emplacement** selon la structure ci-dessus
4. **Nommer le fichier** exactement comme indiqué

### Méthode 2 : Outil de téléchargement (wget, curl, etc.)

#### Windows PowerShell :
```powershell
# Exemple pour Bootstrap CSS
Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" -OutFile "public/assets/vendor/bootstrap/bootstrap.min.css"
```

#### Linux/Mac :
```bash
# Exemple pour Bootstrap CSS
curl -L -o public/assets/vendor/bootstrap/bootstrap.min.css https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css
```

---

## ✅ Checklist de vérification

Après téléchargement, vérifiez que :

- [ ] Tous les dossiers sont créés
- [ ] Tous les fichiers sont présents
- [ ] Les noms de fichiers sont exacts (respecter la casse)
- [ ] Les fichiers ne sont pas vides (taille > 0)
- [ ] Les fonts Bootstrap Icons sont dans le dossier `fonts/`

---

## 📝 Notes importantes

### 1. **Noms de fichiers**
- ⚠️ **Respecter exactement** les noms indiqués (y compris les extensions)
- ⚠️ **Respecter la casse** : `bootstrap.min.css` ≠ `Bootstrap.min.css`

### 2. **Structure des dossiers**
- Créer les dossiers **avant** de télécharger les fichiers
- Respecter la hiérarchie exacte

### 3. **Bootstrap Icons**
- Le fichier CSS fait référence aux fonts avec le chemin `fonts/bootstrap-icons.woff`
- ⚠️ **Important** : Les fonts DOIVENT être dans `bootstrap-icons/fonts/`

### 4. **IntlTelInput**
- Le fichier `utils.js` est **obligatoire** pour le fonctionnement complet
- Il est chargé dynamiquement par le script principal

---

## 🚀 Après le téléchargement

Une fois tous les fichiers téléchargés, il faudra :

1. **Mettre à jour le template** `resources/views/public/form.blade.php`
2. **Remplacer les URLs CDN** par les chemins locaux
3. **Tester** que tout fonctionne

---

## 📊 Résumé rapide

| Bibliothèque | Version | Fichiers | Dossier |
|--------------|---------|----------|---------|
| Bootstrap | 5.3.0 | 2 | `bootstrap/` |
| Bootstrap Icons | 1.11.0 | 3 | `bootstrap-icons/` |
| jQuery | 3.7.0 | 1 | `jquery/` |
| Select2 | 4.1.0 | 3 | `select2/` |
| IntlTelInput | 18.2.1 | 3 | `intl-tel-input/` |
| Signature Pad | 4.1.7 | 1 | `signature-pad/` |
| SweetAlert2 | 11.x | 2 | `sweetalert2/` |
| **TOTAL** | | **15 fichiers** | |

---

## 🔗 Liens directs (copier-coller)

### Bootstrap
```
https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css
https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js
```

### Bootstrap Icons
```
https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css
https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/fonts/bootstrap-icons.woff
https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/fonts/bootstrap-icons.woff2
```

### jQuery
```
https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js
```

### Select2
```
https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css
https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css
https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js
```

### IntlTelInput
```
https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css
https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js
https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js
```

### Signature Pad
```
https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js
```

### SweetAlert2
```
https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css
https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js
```

---

**Bon téléchargement ! 🎉**




