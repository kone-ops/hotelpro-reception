# Hotel Pro - Application Desktop Electron

Application desktop pour Windows 11 utilisant Electron qui encapsule votre application Laravel.

## Installation

1. **Installer Node.js** (si pas déjà fait)
   - Télécharger depuis [nodejs.org](https://nodejs.org/)
   - Version LTS recommandée

2. **Installer les dépendances**
   ```bash
   npm install
   ```

3. **Configurer l'URL du serveur**
   - Ouvrir `main.js`
   - Modifier la constante `APP_URL` avec votre IP serveur :
   ```javascript
   const APP_URL = 'http://192.168.1.50'; // Votre IP locale
   ```

## Utilisation

### Mode développement
```bash
npm start
```

### Créer l'installateur Windows
```bash
npm run build-win
```

L'installateur sera créé dans le dossier `dist/`.

## Configuration

### Changer l'icône
- Remplacer `icon.ico` par votre propre icône
- Format recommandé : 256x256 pixels

### Personnaliser l'application
- Modifier `package.json` pour changer le nom, la description, etc.
- Modifier `main.js` pour ajuster la taille de la fenêtre, le comportement, etc.

## Distribution

1. Créer l'installateur avec `npm run build-win`
2. Distribuer le fichier `.exe` dans `dist/`
3. Les utilisateurs peuvent installer l'application comme n'importe quelle application Windows

## Notes

- L'application nécessite que le serveur Laravel soit accessible sur le réseau
- Pour un accès hors ligne, vous devrez implémenter une synchronisation locale
- L'application fonctionne comme un navigateur encapsulé pointant vers votre serveur

