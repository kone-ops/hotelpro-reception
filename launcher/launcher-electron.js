// Launcher Electron qui démarre automatiquement le serveur
// Solution la plus élégante - tout est intégré dans l'application

const { app, BrowserWindow } = require('electron');
const { spawn, exec } = require('child_process');
const path = require('path');
const http = require('http');

let mainWindow;
let serverProcess = null;

// Configuration
const SERVER_CONFIG = {
    laragon: {
        path: 'C:\\laragon\\laragon.exe',
        startCommand: 'start',
        checkURL: 'http://hotelpro.test'
    },
    wamp: {
        path: 'C:\\wamp64',
        startCommand: 'net start wampapache64 && net start wampmysqld64',
        checkURL: 'http://localhost'
    },
    xampp: {
        path: 'C:\\xampp',
        startCommand: 'apache_start.bat && mysql_start.bat',
        checkURL: 'http://localhost'
    }
};

// Détecter le type de serveur
function detectServer() {
    const fs = require('fs');
    
    if (fs.existsSync(SERVER_CONFIG.laragon.path)) {
        return 'laragon';
    } else if (fs.existsSync(SERVER_CONFIG.wamp.path)) {
        return 'wamp';
    } else if (fs.existsSync(SERVER_CONFIG.xampp.path)) {
        return 'xampp';
    }
    
    return null;
}

// Démarrer le serveur
function startServer(serverType) {
    return new Promise((resolve, reject) => {
        const config = SERVER_CONFIG[serverType];
        
        console.log(`Démarrage du serveur: ${serverType}`);
        
        if (serverType === 'laragon') {
            serverProcess = spawn(config.path, [config.startCommand], {
                detached: true,
                stdio: 'ignore'
            });
            serverProcess.unref();
        } else if (serverType === 'wamp') {
            exec(config.startCommand, { cwd: config.path }, (error) => {
                if (error) {
                    console.error('Erreur démarrage WAMP:', error);
                }
            });
        } else if (serverType === 'xampp') {
            exec(`cd /d ${config.path} && ${config.startCommand}`, (error) => {
                if (error) {
                    console.error('Erreur démarrage XAMPP:', error);
                }
            });
        }
        
        // Attendre que le serveur soit prêt
        waitForServer(config.checkURL, resolve, reject);
    });
}

// Attendre que le serveur soit prêt
function waitForServer(url, resolve, reject) {
    const maxAttempts = 30;
    let attempts = 0;
    
    const checkServer = () => {
        http.get(url, (res) => {
            console.log('Serveur prêt!');
            resolve();
        }).on('error', (err) => {
            attempts++;
            if (attempts < maxAttempts) {
                setTimeout(checkServer, 2000);
            } else {
                reject(new Error('Le serveur n\'a pas pu démarrer'));
            }
        });
    };
    
    setTimeout(checkServer, 2000);
}

// Créer la fenêtre
function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1024,
        minHeight: 768,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            webSecurity: true
        },
        icon: path.join(__dirname, 'icon.ico'),
        titleBarStyle: 'default',
        autoHideMenuBar: true,
        show: false,
        backgroundColor: '#1a4b8c'
    });

    // Afficher un écran de chargement
    mainWindow.loadFile('loading.html');

    // Détecter et démarrer le serveur
    const serverType = detectServer();
    
    if (!serverType) {
        mainWindow.loadFile('error.html');
        return;
    }

    startServer(serverType)
        .then(() => {
            // Serveur prêt, charger l'application
            const appURL = SERVER_CONFIG[serverType].checkURL;
            mainWindow.loadURL(appURL);
            mainWindow.show();
        })
        .catch((error) => {
            console.error('Erreur:', error);
            mainWindow.loadFile('error.html');
        });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// Quand l'application est prête
app.whenReady().then(() => {
    createWindow();

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            createWindow();
        }
    });
});

// Quitter quand toutes les fenêtres sont fermées
app.on('window-all-closed', () => {
    // Optionnel : Arrêter le serveur quand l'application se ferme
    // if (serverProcess) {
    //     serverProcess.kill();
    // }
    
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

