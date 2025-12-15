/**
 * Système de raccourcis clavier professionnels
 * Améliore la productivité des utilisateurs avancés
 */

class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.enabled = true;
        this.init();
    }

    init() {
        this.registerDefaultShortcuts();
        this.setupEventListeners();
        this.createHelpModal();
    }

    registerDefaultShortcuts() {
        // Navigation
        this.register('ctrl+h', () => this.navigateTo('/'), 'Accueil');
        this.register('ctrl+d', () => this.navigateTo('/dashboard'), 'Dashboard');
        this.register('ctrl+p', () => this.navigateTo(window.location.pathname.includes('reception') ? '/reception/pre-reservations' : '/hotel/pre-reservations'), 'Pré-réservations');
        
        // Actions
        this.register('ctrl+n', () => this.triggerAction('new'), 'Nouvelle réservation');
        this.register('ctrl+s', () => this.triggerAction('save'), 'Sauvegarder');
        this.register('ctrl+f', () => this.triggerAction('search'), 'Rechercher');
        this.register('esc', () => this.triggerAction('cancel'), 'Annuler/Fermer');
        
        // UI
        this.register('ctrl+k', () => this.showCommandPalette(), 'Palette de commandes');
        this.register('ctrl+/', () => this.showHelp(), 'Aide raccourcis');
        this.register('ctrl+shift+d', () => this.toggleTheme(), 'Changer thème');
        
        // Utilitaires
        this.register('ctrl+e', () => this.exportData(), 'Exporter données');
        this.register('ctrl+r', () => this.refreshData(), 'Actualiser');
    }

    register(shortcut, callback, description = '') {
        this.shortcuts.set(shortcut.toLowerCase(), {
            callback,
            description,
            keys: this.parseShortcut(shortcut)
        });
    }

    parseShortcut(shortcut) {
        return shortcut.toLowerCase().split('+').map(k => k.trim());
    }

    setupEventListeners() {
        document.addEventListener('keydown', (e) => {
            if (!this.enabled) return;
            
            // Ignorer si dans un input/textarea
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                // Sauf pour ESC
                if (e.key !== 'Escape') return;
            }

            const pressedKeys = [];
            if (e.ctrlKey) pressedKeys.push('ctrl');
            if (e.shiftKey) pressedKeys.push('shift');
            if (e.altKey) pressedKeys.push('alt');
            if (e.metaKey) pressedKeys.push('meta');
            
            const key = e.key.toLowerCase();
            if (!['control', 'shift', 'alt', 'meta'].includes(key)) {
                pressedKeys.push(key === ' ' ? 'space' : key);
            }

            const shortcutKey = pressedKeys.join('+');
            const shortcut = this.shortcuts.get(shortcutKey);

            if (shortcut) {
                e.preventDefault();
                shortcut.callback(e);
            }
        });
    }

    navigateTo(path) {
        window.location.href = path;
    }

    triggerAction(action) {
        switch(action) {
            case 'new':
                const newBtn = document.querySelector('[data-action="new"], .btn-new, #newBtn');
                if (newBtn) newBtn.click();
                break;
            
            case 'save':
                const saveBtn = document.querySelector('[type="submit"], .btn-save, #saveBtn');
                if (saveBtn) saveBtn.click();
                break;
            
            case 'search':
                const searchInput = document.querySelector('[name="search"], #searchInput, .search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
                break;
            
            case 'cancel':
                // Fermer les modals
                const closeBtn = document.querySelector('.modal.show .btn-close, .modal.show [data-bs-dismiss="modal"]');
                if (closeBtn) {
                    closeBtn.click();
                } else {
                    // Retour en arrière
                    window.history.back();
                }
                break;
        }
    }

    showCommandPalette() {
        // Créer une palette de commandes style VS Code
        const palette = document.getElementById('command-palette');
        if (palette) {
            palette.classList.add('show');
            const input = palette.querySelector('input');
            if (input) input.focus();
        } else {
            this.createCommandPalette();
        }
    }

    createCommandPalette() {
        const palette = document.createElement('div');
        palette.id = 'command-palette';
        palette.className = 'command-palette';
        palette.innerHTML = `
            <div class="command-palette-backdrop" onclick="document.getElementById('command-palette').classList.remove('show')"></div>
            <div class="command-palette-content">
                <input type="text" class="command-palette-input" placeholder="Tapez une commande..." autofocus>
                <div class="command-palette-results"></div>
            </div>
        `;
        document.body.appendChild(palette);
        
        const input = palette.querySelector('input');
        input.addEventListener('input', (e) => this.filterCommands(e.target.value));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                palette.classList.remove('show');
            }
        });
        
        palette.classList.add('show');
    }

    filterCommands(query) {
        const results = document.querySelector('.command-palette-results');
        if (!results) return;
        
        const filtered = Array.from(this.shortcuts.entries())
            .filter(([key, shortcut]) => 
                shortcut.description.toLowerCase().includes(query.toLowerCase()) ||
                key.includes(query.toLowerCase())
            )
            .slice(0, 10);
        
        results.innerHTML = filtered.map(([key, shortcut]) => `
            <div class="command-palette-item" onclick="window.keyboardShortcuts.executeShortcut('${key}')">
                <span class="command-name">${shortcut.description}</span>
                <span class="command-keys">${key}</span>
            </div>
        `).join('');
    }

    executeShortcut(key) {
        const shortcut = this.shortcuts.get(key);
        if (shortcut) {
            shortcut.callback();
            document.getElementById('command-palette').classList.remove('show');
        }
    }

    showHelp() {
        const modal = document.getElementById('shortcuts-help-modal');
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    createHelpModal() {
        const modal = document.createElement('div');
        modal.id = 'shortcuts-help-modal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-keyboard me-2"></i>Raccourcis Clavier
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Navigation</h6>
                                <table class="table table-sm">
                                    ${this.generateShortcutsTable(['ctrl+h', 'ctrl+d', 'ctrl+p'])}
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Actions</h6>
                                <table class="table table-sm">
                                    ${this.generateShortcutsTable(['ctrl+n', 'ctrl+s', 'ctrl+f', 'esc'])}
                                </table>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6 class="text-primary">Interface</h6>
                                <table class="table table-sm">
                                    ${this.generateShortcutsTable(['ctrl+k', 'ctrl+/', 'ctrl+shift+d'])}
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Utilitaires</h6>
                                <table class="table table-sm">
                                    ${this.generateShortcutsTable(['ctrl+e', 'ctrl+r'])}
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    generateShortcutsTable(keys) {
        return keys.map(key => {
            const shortcut = this.shortcuts.get(key);
            if (!shortcut) return '';
            return `
                <tr>
                    <td><kbd>${key.replace(/\+/g, '</kbd> + <kbd>')}</kbd></td>
                    <td>${shortcut.description}</td>
                </tr>
            `;
        }).join('');
    }

    toggleTheme() {
        if (window.themeSwitcher) {
            window.themeSwitcher.toggleTheme();
        }
    }

    exportData() {
        const exportBtn = document.querySelector('[data-action="export"], .btn-export');
        if (exportBtn) exportBtn.click();
    }

    refreshData() {
        window.location.reload();
    }

    enable() {
        this.enabled = true;
    }

    disable() {
        this.enabled = false;
    }
}

// Initialiser
document.addEventListener('DOMContentLoaded', () => {
    window.keyboardShortcuts = new KeyboardShortcuts();
});

// Styles pour la palette de commandes
const style = document.createElement('style');
style.textContent = `
    .command-palette {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }
    
    .command-palette.show {
        display: block;
    }
    
    .command-palette-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .command-palette-content {
        position: absolute;
        top: 20%;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 600px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    
    [data-theme="dark"] .command-palette-content {
        background: #242831;
    }
    
    .command-palette-input {
        width: 100%;
        padding: 20px;
        border: none;
        font-size: 18px;
        outline: none;
        background: transparent;
    }
    
    .command-palette-results {
        max-height: 400px;
        overflow-y: auto;
        border-top: 1px solid #dee2e6;
    }
    
    [data-theme="dark"] .command-palette-results {
        border-top-color: #495057;
    }
    
    .command-palette-item {
        padding: 12px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s;
    }
    
    .command-palette-item:hover {
        background: #f8f9fa;
    }
    
    [data-theme="dark"] .command-palette-item:hover {
        background: #2d3139;
    }
    
    .command-keys {
        font-family: monospace;
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
    
    [data-theme="dark"] .command-keys {
        background: #495057;
    }
    
    kbd {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        padding: 2px 6px;
        font-size: 12px;
        font-family: monospace;
    }
    
    [data-theme="dark"] kbd {
        background: #2d3139;
        border-color: #495057;
    }
`;
document.head.appendChild(style);





