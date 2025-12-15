# 🏨 HotelPro - Système de Gestion Hôtelière Multi-Hôtel

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **Système professionnel de gestion hôtelière** avec pré-réservations en ligne, QR codes, impression réseau automatisée et gestion multi-hôtel.

---

## ✨ Fonctionnalités Principales

### 🏢 Multi-Hôtel
- ✅ Gestion de plusieurs hôtels depuis une seule application
- ✅ Isolation complète des données entre hôtels
- ✅ Interface adaptée selon le rôle (Super Admin, Hotel Admin, Receptionist)

### 📝 Système de Réservations
- ✅ Formulaires publics accessibles via QR code
- ✅ Pré-réservations avec validation par l'hôtel
- ✅ Gestion des groupes avec accompagnants
- ✅ Documents d'identité enrichis (lieu et date de délivrance)
- ✅ Signatures numériques

### 🖨️ Impression Réseau Révolutionnaire
- ✅ Test de connexion RÉEL aux imprimantes réseau
- ✅ Envoi d'impressions RÉEL via ESC/POS
- ✅ Scan automatique du réseau local
- ✅ Support multi-fabricants (HP, Canon, Epson, Brother, Samsung, etc.)
- ✅ Gestion avancée des erreurs avec retry automatique
- ✅ Logs détaillés de chaque impression

### 📄 Fiches de Police
- ✅ Génération automatique en PDF
- ✅ Preview avant impression
- ✅ Impression batch (plusieurs à la fois)
- ✅ Envoi direct à l'imprimante réseau

### 🔐 Sécurité
- ✅ Authentification Laravel Breeze
- ✅ Gestion des rôles et permissions (Spatie)
- ✅ Protection CSRF
- ✅ Rate limiting sur formulaires publics
- ✅ Validation stricte des données

### 📊 Administration
- ✅ Dashboard statistiques par hôtel
- ✅ Gestion des utilisateurs
- ✅ Journal d'activité
- ✅ Rapports détaillés
- ✅ **Module de gestion des données (Reset, Export, Import, Purge)** ⭐

---

## 🚀 Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js & NPM
- MySQL 8.0+ ou PostgreSQL
- Redis (recommandé pour le cache)

### Étapes d'Installation

1. **Cloner le dépôt**

```bash
git clone https://github.com/votre-repo/hotelpro.git
cd hotelpro
```

2. **Installer les dépendances PHP**

```bash
composer install
```

3. **Installer les dépendances JavaScript**

```bash
npm install
```

4. **Configuration de l'environnement**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurer la base de données**

Éditer `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelpro
DB_USERNAME=root
DB_PASSWORD=

# Cache Redis (recommandé)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

6. **Exécuter les migrations**

```bash
php artisan migrate --seed
```

7. **Créer un lien symbolique pour le storage**

```bash
php artisan storage:link
```

8. **Compiler les assets**

```bash
npm run build
# ou en mode développement
npm run dev
```

9. **Lancer le serveur**

```bash
php artisan serve
```

L'application sera accessible sur `http://127.0.0.1:8000`

---

## 👥 Comptes par Défaut

Après le seeding :

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Super Admin | admin@hotelpro.com | password |
| Hotel Admin | hotel@example.com | password |
| Receptionist | reception@example.com | password |

---

## 📁 Structure du Projet

```
hotelpro/
├── app/
│   ├── Http/Controllers/
│   │   ├── SuperAdmin/        # Contrôleurs Super Admin
│   │   ├── HotelAdmin/         # Contrôleurs Hotel Admin
│   │   ├── Reception/          # Contrôleurs Réception
│   │   └── PrinterController   # Gestion des imprimantes ⭐
│   ├── Models/                 # Modèles Eloquent
│   ├── Services/               # Services métier
│   └── Jobs/                   # Jobs asynchrones
├── database/
│   └── migrations/             # 35+ migrations
├── resources/
│   ├── views/                  # Vues Blade
│   └── js/                     # JavaScript
├── routes/
│   └── web.php                 # Routes organisées par rôle
├── docs/                       # 📚 Documentation complète
│   ├── guides/                 # Guides utilisateur
│   ├── corrections/            # Historique corrections
│   └── rapports/               # Rapports techniques
└── tests/                      # Tests
```

---

## 🔧 Configuration des Imprimantes

### Scan Automatique du Réseau

1. Accéder à **Impression > Gestion des imprimantes**
2. Cliquer sur **Scanner le réseau**
3. L'application détectera automatiquement les imprimantes disponibles
4. Cliquer sur **Ajouter** pour enregistrer une imprimante détectée

### Ajout Manuel

1. **Nom** : Nom de l'imprimante (ex: "Imprimante Reception")
2. **Adresse IP** : Adresse IP de l'imprimante (ex: `192.168.1.100`)
3. **Port** : Port réseau (défaut: `9100` pour ESC/POS)
4. **Type** : thermique, laser, jet d'encre, multifonction
5. **Module** : reception, administration, cuisine

### Test de Connexion

Cliquer sur le bouton **Test connexion** pour vérifier que l'imprimante est accessible.

### Impression de Test

Cliquer sur **Test impression** pour envoyer un document de test à l'imprimante.

---

## 📊 Module de Gestion des Données (Super Admin)

### Accès

`Super Admin > Gestion des Données > Données Hôtels`

### Fonctionnalités

#### 1. 📥 Export
- Télécharge toutes les données d'un hôtel au format JSON
- Inclut : réservations, utilisateurs, imprimantes, logs
- Utile pour backup ou migration

#### 2. 📤 Import
- Importe des données depuis un fichier JSON
- Deux modes :
  - **Fusionner** : Ajouter aux données existantes
  - **Remplacer** : Supprimer et remplacer (dangereux !)

#### 3. 🗑️ Purge
- Supprime les anciennes données (soft clean)
- Configurable par période (30-365 jours)
- Types : pré-réservations annulées, logs d'impression, logs d'activité

#### 4. ⚠️ Reset Complet
- Réinitialise TOUTES les données d'un hôtel
- **ATTENTION : Irréversible !**
- Nécessite confirmation avec le nom de l'hôtel
- Option : supprimer les utilisateurs (sauf super-admin)

---

## ⚡ Optimisation des Performances

### Caches Laravel

```bash
# En production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Redis

Activer Redis dans `.env` :

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Indexes Database

Les indexes de performance sont automatiquement créés avec la migration :

```bash
php artisan migrate
```

### Queues

Pour les tâches asynchrones (génération PDF, envoi emails) :

```bash
php artisan queue:work
```

En production, utiliser **Supervisor** pour gérer les workers (voir `docs/guides/OPTIMISATION_PERFORMANCES.md`).

---

## 📚 Documentation

Toute la documentation est disponible dans le dossier `docs/` :

- 📖 **Guides** : `docs/guides/`
  - Installation
  - Configuration imprimantes
  - Optimisation performances
  - Tests
  
- 🔧 **Corrections** : `docs/corrections/`
  - Historique des bugs corrigés
  
- 📊 **Rapports** : `docs/rapports/`
  - Rapports d'expertise
  - Analyses techniques

---

## 🧪 Tests

### Tests Manuels

Les scripts de tests manuels sont dans `tests/manual/`

### Tests Automatisés

```bash
php artisan test
```

---

## 🛠️ Technologies Utilisées

### Backend
- **Laravel 12.0** - Framework PHP
- **Spatie Laravel Permission** - Gestion des rôles
- **DomPDF** - Génération de PDF
- **SimpleSoftwareIO/simple-qrcode** - QR codes
- **mike42/escpos-php** - Impression ESC/POS thermique
- **Predis** - Client Redis

### Frontend
- **Blade** - Moteur de templates
- **TailwindCSS** - Framework CSS
- **Alpine.js** - Interactivité JavaScript
- **Flowbite** - Composants UI
- **Vite** - Build tool

### Database
- **MySQL** ou **PostgreSQL**
- **Redis** - Cache et sessions

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📝 License

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 👨‍💻 Auteurs

- **Équipe HotelPro** - *Développement initial*

---

## 📞 Support

Pour toute question ou problème :

- 📧 Email : support@hotelpro.com
- 📖 Documentation : `docs/`
- 🐛 Issues : [GitHub Issues](https://github.com/votre-repo/hotelpro/issues)

---

## 🎯 Roadmap

- [ ] Application mobile (React Native)
- [ ] API REST publique
- [ ] Module de facturation
- [ ] Intégration avec Oracle Hospitality
- [ ] Système de notification push
- [ ] Dashboard analytics avancé
- [ ] Multi-langue (i18n)

---

## ⭐ Fonctionnalités Uniques

Ce projet se démarque par :

1. **Système d'impression réseau révolutionnaire** avec test RÉEL et envoi RÉEL aux imprimantes
2. **Module de gestion des données** pour reset/export/import/purge
3. **Architecture multi-hôtel** avec isolation complète
4. **QR codes dynamiques** pour formulaires publics
5. **Sécurité maximale** avec permissions granulaires

---

<div align="center">

**Développé avec ❤️ pour l'industrie hôtelière**

[Documentation](docs/) • [Installation](#-installation) • [Support](#-support)

</div>
