/**
 * Système de thème clair/sombre professionnel
 * Persiste les préférences utilisateur
 */

class ThemeSwitcher {
    constructor() {
        this.storageKey = 'app-theme';
        this.init();
    }

    init() {
        // Charger le thème sauvegardé ou utiliser la préférence système
        const savedTheme = localStorage.getItem(this.storageKey);
        const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        const theme = savedTheme || systemPreference;
        
        this.setTheme(theme, false);
        this.setupToggleButton();
        this.watchSystemPreference();
    }

    setTheme(theme, save = true) {
        document.documentElement.setAttribute('data-theme', theme);
        
        if (save) {
            localStorage.setItem(this.storageKey, theme);
        }

        // Mettre à jour l'icône du bouton
        this.updateToggleIcon(theme);
        
        // Dispatch event pour d'autres composants
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    setupToggleButton() {
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleTheme());
        }
    }

    updateToggleIcon(theme) {
        const icon = document.querySelector('#theme-toggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
    }

    watchSystemPreference() {
        // Surveiller les changements de préférence système
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem(this.storageKey)) {
                this.setTheme(e.matches ? 'dark' : 'light', false);
            }
        });
    }

    getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', () => {
    window.themeSwitcher = new ThemeSwitcher();
});

// Export pour utilisation dans d'autres scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}





