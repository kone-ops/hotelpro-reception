const { app, BrowserWindow, Menu, dialog } = require('electron');
const path = require('path');

let mainWindow;

// Configuration - MODIFIEZ CETTE URL AVEC VOTRE IP SERVEUR
const APP_URL = 'http://192.168.1.50'; // Remplacez par votre IP locale
// Pour développement local avec Laragon: 'http://hotelpro.test'
// Pour développement local avec XAMPP: 'http://hotelpro.local'

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    minWidth: 1024,
    minHeight: 768,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      webSecurity: true,
      preload: path.join(__dirname, 'preload.js')
    },
    icon: path.join(__dirname, 'icon.ico'),
    titleBarStyle: 'default',
    autoHideMenuBar: true,
    show: false, // Ne pas afficher avant le chargement
    backgroundColor: '#1a4b8c'
  });

  // Charger l'application
  mainWindow.loadURL(APP_URL);
  
  // Afficher la fenêtre une fois chargée
  mainWindow.once('ready-to-show', () => {
    mainWindow.show();
    
    // Focus sur la fenêtre
    if (process.platform === 'win32') {
      mainWindow.focus();
    }
  });

  // Ouvrir les DevTools en développement (décommentez si nécessaire)
  // mainWindow.webContents.openDevTools();

  // Gérer les erreurs de connexion
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL) => {
    if (errorCode === -106 || errorCode === -105) {
      // Erreur de connexion
      mainWindow.loadFile('offline.html');
    }
  });

  // Gérer la fermeture
  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  // Détecter les liens externes et les ouvrir dans le navigateur
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    require('electron').shell.openExternal(url);
    return { action: 'deny' };
  });
}

// Créer le menu
function createMenu() {
  const template = [
    {
      label: 'Fichier',
      submenu: [
        {
          label: 'Actualiser',
          accelerator: 'F5',
          click: () => {
            if (mainWindow) {
              mainWindow.reload();
            }
          }
        },
        {
          label: 'Recharger complètement',
          accelerator: 'Ctrl+Shift+R',
          click: () => {
            if (mainWindow) {
              mainWindow.webContents.reloadIgnoringCache();
            }
          }
        },
        { type: 'separator' },
        {
          label: 'Quitter',
          accelerator: process.platform === 'darwin' ? 'Cmd+Q' : 'Ctrl+Q',
          click: () => {
            app.quit();
          }
        }
      ]
    },
    {
      label: 'Affichage',
      submenu: [
        {
          label: 'Plein écran',
          accelerator: 'F11',
          click: () => {
            if (mainWindow) {
              mainWindow.setFullScreen(!mainWindow.isFullScreen());
            }
          }
        },
        {
          label: 'Outils de développement',
          accelerator: 'F12',
          click: () => {
            if (mainWindow) {
              mainWindow.webContents.toggleDevTools();
            }
          }
        }
      ]
    },
    {
      label: 'Aide',
      submenu: [
        {
          label: 'À propos',
          click: () => {
            dialog.showMessageBox(mainWindow, {
              type: 'info',
              title: 'À propos de Hotel Pro',
              message: 'Hotel Pro - Gestion Hôtelière',
              detail: 'Version 1.0.0\n\nSystème de gestion hôtelière professionnel'
            });
          }
        }
      ]
    }
  ];

  const menu = Menu.buildFromTemplate(template);
  Menu.setApplicationMenu(menu);
}

// Quand l'application est prête
app.whenReady().then(() => {
  createWindow();
  createMenu();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

// Quitter quand toutes les fenêtres sont fermées
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

// Gérer les mises à jour (optionnel)
app.on('web-contents-created', (event, contents) => {
  contents.on('new-window', (event, navigationUrl) => {
    event.preventDefault();
    require('electron').shell.openExternal(navigationUrl);
  });
});

