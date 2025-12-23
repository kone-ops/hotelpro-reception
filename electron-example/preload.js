// Preload script pour Electron
// Permet la communication sécurisée entre le processus principal et le rendu

const { contextBridge } = require('electron');

// Exposer des APIs sécurisées au contenu web
contextBridge.exposeInMainWorld('electronAPI', {
  // Vous pouvez ajouter des APIs personnalisées ici si nécessaire
  platform: process.platform,
  versions: {
    node: process.versions.node,
    chrome: process.versions.chrome,
    electron: process.versions.electron
  }
});

