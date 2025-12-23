# 🚀 Guide de Lancement Automatique - Hotel Pro

## 🎯 Objectif

Permettre au client de lancer l'application d'un simple **double-clic sur un raccourci**, sans voir le serveur web démarrer en arrière-plan.

---

## ⭐ Solution Recommandée : launcher-vbs.vbs

### Pourquoi cette solution ?
- ✅ **100% silencieux** - Aucune fenêtre visible
- ✅ **Simple** - Un seul fichier à double-cliquer
- ✅ **Automatique** - Détecte Laragon/WAMP/XAMPP
- ✅ **Rapide** - Démarrage en quelques secondes

---

## 📋 Installation (3 étapes)

### Étape 1 : Créer le raccourci sur le Bureau

**Option A : Automatique (Recommandé)**
```powershell
cd launcher
.\create-shortcut.ps1
```

**Option B : Manuel**
1. Clic droit sur le Bureau → **Nouveau** → **Raccourci**
2. **Cible :** 
   ```
   wscript.exe "C:\chemin\vers\hotelpro\launcher\launcher-vbs.vbs"
   ```
3. **Nom :** Hotel Pro
4. **Icône :** Choisir le logo de l'application

### Étape 2 : Tester le raccourci

1. **Double-cliquer** sur le raccourci "Hotel Pro"
2. **Attendre 3-5 secondes** (démarrage du serveur)
3. **L'application s'ouvre automatiquement** dans le navigateur

### Étape 3 : Distribuer au client

1. **Copier le dossier `launcher/`** avec votre application
2. **Exécuter `create-shortcut.ps1`** sur la machine du client
3. **C'est tout !** Le client peut maintenant utiliser le raccourci

---

## 🔧 Configuration

### Modifier l'URL de l'application

Ouvrir `launcher-vbs.vbs` et modifier la ligne :

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

1. Créer ou télécharger un fichier `icon.ico` (256x256 pixels)
2. Le placer dans le dossier `launcher/`
3. Le script `create-shortcut.ps1` l'utilisera automatiquement

### Ajouter un message de chargement

Pour afficher un message pendant le démarrage, vous pouvez modifier le VBS pour afficher une notification :

```vbs
Set objShell = CreateObject("WScript.Shell")
objShell.Popup "Démarrage de Hotel Pro...", 2, "Hotel Pro", 64
```

---

## 🚀 Solutions Alternatives

### Solution 2 : Application Electron avec launcher intégré

Si vous voulez une vraie application desktop :

1. Utiliser `launcher-electron.js` comme `main.js` dans Electron
2. L'application Electron démarre automatiquement le serveur
3. Affiche un écran de chargement pendant le démarrage
4. Ouvre l'application une fois le serveur prêt

**Avantages :**
- ✅ Expérience utilisateur parfaite
- ✅ Aucune fenêtre de serveur visible
- ✅ Application native Windows

### Solution 3 : Service Windows (Avancé)

Pour que le serveur démarre automatiquement au démarrage de Windows :

```powershell
.\install-service.ps1
```

**Avantages :**
- ✅ Serveur toujours prêt
- ✅ Démarrage plus rapide
- ✅ Fonctionne même si l'application n'est pas lancée

**Inconvénients :**
- ⚠️ Nécessite des droits administrateur
- ⚠️ Consomme des ressources même quand l'app n'est pas utilisée

---

## 📊 Comparaison des Solutions

| Solution | Silencieux | Rapidité | Complexité | Recommandé |
|----------|-----------|----------|------------|------------|
| **launcher-vbs.vbs** | ✅✅✅ | ⚡⚡⚡ | ⭐ | ⭐⭐⭐⭐⭐ |
| launcher.bat | ⚠️ | ⚡⚡⚡ | ⭐ | ⭐⭐⭐ |
| launcher.ps1 | ✅✅ | ⚡⚡⚡ | ⭐⭐ | ⭐⭐⭐⭐ |
| Electron intégré | ✅✅✅ | ⚡⚡ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| Service Windows | ✅✅✅ | ⚡⚡⚡ | ⭐⭐⭐⭐ | ⭐⭐⭐ |

---

## ⚠️ Notes Importantes

1. **Le serveur doit être installé** sur la machine du client
   - Laragon, WAMP, ou XAMPP
   - Le launcher détecte automatiquement lequel est installé

2. **Premier lancement** peut prendre 5-10 secondes
   - Le serveur doit démarrer
   - Les connexions suivantes sont plus rapides

3. **Pare-feu Windows**
   - Peut demander une autorisation la première fois
   - À configurer une fois, puis fonctionne automatiquement

4. **Services Windows (WAMP)**
   - Peuvent nécessiter des droits administrateur
   - Le launcher gère cela automatiquement

---

## 🔍 Dépannage

### Le serveur ne démarre pas

**Vérifier :**
1. Laragon/WAMP/XAMPP est installé
2. Les chemins dans `launcher-vbs.vbs` sont corrects
3. Les permissions sont suffisantes

**Solution :**
- Ouvrir manuellement Laragon/WAMP/XAMPP
- Vérifier que le serveur démarre correctement
- Ajuster les chemins dans le launcher si nécessaire

### L'application ne s'ouvre pas

**Vérifier :**
1. L'URL dans le launcher est correcte
2. Le serveur répond (tester manuellement dans le navigateur)
3. Le pare-feu n'bloque pas

**Solution :**
- Tester l'URL manuellement : `http://hotelpro.test` ou `http://localhost`
- Vérifier la configuration dans `.env` (APP_URL)

### Fenêtre visible brièvement

**Solution :**
- Utiliser `launcher-vbs.vbs` au lieu de `.bat`
- S'assurer que `wscript.exe` est utilisé (pas `cscript.exe`)

---

## 📞 Support

Pour toute question ou problème :
1. Vérifier les logs : `storage/logs/laravel.log`
2. Tester le serveur manuellement
3. Vérifier la configuration du launcher

---

## ✅ Checklist de Déploiement

- [ ] Launcher créé et testé
- [ ] Raccourci créé sur le Bureau
- [ ] Icône personnalisée (optionnel)
- [ ] URL configurée correctement
- [ ] Testé sur la machine du client
- [ ] Instructions données au client

---

**🎉 C'est tout ! Le client peut maintenant lancer l'application d'un simple double-clic, sans voir le serveur démarrer.**

