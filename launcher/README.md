# 🚀 Launcher Automatique - Hotel Pro

## Vue d'ensemble

Ce dossier contient plusieurs solutions pour lancer automatiquement le serveur web (Laragon/WAMP/XAMPP) en arrière-plan et ouvrir l'application, sans que le client ne voie quoi que ce soit.

---

## 🎯 Solutions disponibles

### 1. **launcher-vbs.vbs** ⭐ RECOMMANDÉ
- ✅ **Complètement silencieux** - Aucune fenêtre visible
- ✅ **Simple et efficace**
- ✅ **Détecte automatiquement** Laragon/WAMP/XAMPP
- ✅ **Ouvre l'application** automatiquement

**Utilisation :**
1. Double-cliquer sur `launcher-vbs.vbs`
2. Ou créer un raccourci vers ce fichier

### 2. **launcher.bat**
- ✅ Script batch simple
- ⚠️ Peut afficher brièvement une fenêtre
- ✅ Compatible avec tous les systèmes

### 3. **launcher.ps1**
- ✅ PowerShell avec interface graphique
- ✅ Masque la fenêtre PowerShell
- ✅ Affiche des messages d'erreur si nécessaire

### 4. **launcher-electron.js** (Avancé)
- ✅ Solution la plus élégante
- ✅ Intègre le démarrage du serveur dans l'application Electron
- ✅ Affiche un écran de chargement
- ⚠️ Nécessite de modifier l'application Electron

---

## 📋 Installation pour le client

### Option 1 : Raccourci simple (Recommandé)

1. **Exécuter le script de création de raccourci :**
   ```powershell
   .\create-shortcut.ps1
   ```

2. **Un raccourci "Hotel Pro.lnk" sera créé sur le Bureau**

3. **Le client double-clique simplement sur ce raccourci**

4. **Tout se lance automatiquement :**
   - Le serveur démarre en arrière-plan
   - L'application s'ouvre dans le navigateur ou Electron
   - Aucune fenêtre de serveur visible

### Option 2 : Création manuelle du raccourci

1. **Clic droit sur le Bureau** → Nouveau → Raccourci
2. **Cible :** `wscript.exe "C:\chemin\vers\launcher-vbs.vbs"`
3. **Nom :** Hotel Pro
4. **Icône :** Choisir le logo de l'application

---

## 🔧 Configuration

### Modifier l'URL de l'application

Dans `launcher-vbs.vbs` (ou les autres fichiers), modifier :

```vbs
AppURL = "http://hotelpro.test"  ' Pour Laragon
' ou
AppURL = "http://localhost"      ' Pour WAMP/XAMPP
' ou
AppURL = "http://192.168.1.50"   ' Pour accès réseau
```

### Modifier le chemin du serveur

Si votre serveur est installé ailleurs :

```vbs
ServerPath = "C:\laragon"        ' Laragon
' ou
ServerPath = "C:\wamp64"         ' WAMP
' ou
ServerPath = "C:\xampp"          ' XAMPP
```

---

## 🎨 Personnalisation

### Changer l'icône du raccourci

1. Créer ou télécharger un fichier `icon.ico`
2. Le placer dans le dossier `launcher/`
3. Le script `create-shortcut.ps1` l'utilisera automatiquement

### Ajouter un écran de chargement

Pour la solution Electron, créer un fichier `loading.html` :

```html
<!DOCTYPE html>
<html>
<head>
    <title>Chargement...</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #1a4b8c;
            color: white;
            font-family: Arial, sans-serif;
        }
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <div class="spinner"></div>
        <p style="margin-top: 20px;">Démarrage de Hotel Pro...</p>
    </div>
</body>
</html>
```

---

## 🚀 Déploiement

### Pour distribuer au client :

1. **Copier le dossier `launcher/`** avec votre application
2. **Exécuter `create-shortcut.ps1`** sur la machine du client
3. **Le raccourci est créé** sur le Bureau
4. **Le client peut maintenant lancer l'application** d'un simple double-clic

### Structure recommandée :

```
Hotel Pro/
├── launcher/
│   ├── launcher-vbs.vbs      ← Fichier principal
│   ├── create-shortcut.ps1   ← Script d'installation
│   └── README.md
├── public/
├── app/
└── ...
```

---

## ⚠️ Notes importantes

1. **Le serveur doit être installé** sur la machine du client (Laragon/WAMP/XAMPP)
2. **Les services doivent être configurés** pour démarrer automatiquement (optionnel)
3. **Le pare-feu** peut bloquer le démarrage - à configurer une fois
4. **Premier lancement** peut prendre quelques secondes (démarrage du serveur)

---

## 🔒 Sécurité

- Les scripts ne nécessitent pas de droits administrateur (sauf pour WAMP services)
- Le serveur démarre uniquement quand l'application est lancée
- Aucune donnée sensible n'est stockée dans les scripts

---

## 📞 Dépannage

### Le serveur ne démarre pas
- Vérifier que Laragon/WAMP/XAMPP est installé
- Vérifier les chemins dans le script
- Vérifier les permissions

### L'application ne s'ouvre pas
- Vérifier l'URL dans le script
- Vérifier que le serveur répond (tester manuellement)
- Vérifier le pare-feu Windows

### Fenêtre visible brièvement
- Utiliser `launcher-vbs.vbs` au lieu de `.bat`
- Vérifier que wscript.exe est utilisé pour lancer le VBS

