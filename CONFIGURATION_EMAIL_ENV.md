# 📧 Configuration .env pour les Emails

## ✅ Variables Nécessaires dans le .env

Pour que le système utilise automatiquement le nom de l'hôtel comme expéditeur, vous devez avoir ces variables dans votre `.env` :

```env
# Configuration SendGrid (Option 2)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.VOTRE_API_KEY_SENDGRID_ICI
MAIL_ENCRYPTION=tls

# Adresse email d'expéditeur (OBLIGATOIRE)
MAIL_FROM_ADDRESS=votre_email@gmail.com

# Nom d'expéditeur par défaut (utilisé en fallback seulement)
MAIL_FROM_NAME="Test Mohamed El Bachir"

# Pas de queue pour l'instantanéité
QUEUE_CONNECTION=sync
```

---

## 📋 Explication des Variables

### **MAIL_FROM_ADDRESS** (OBLIGATOIRE)
- **Rôle** : Adresse email qui enverra les emails
- **Exemple** : `votre_email@gmail.com`
- **Utilisation** : Utilisée comme adresse d'expéditeur de tous les emails

### **MAIL_FROM_NAME** (Fallback uniquement)
- **Rôle** : Nom d'expéditeur par défaut (utilisé SEULEMENT si le nom de l'hôtel n'est pas disponible)
- **Exemple** : `"Test Mohamed El Bachir"` ou `"HOTELPRO"` ou n'importe quel nom
- **Utilisation** : Fallback en cas d'erreur (normalement, le nom de l'hôtel sera utilisé)

---

## 🎯 Comment ça Fonctionne

### Fonctionnement Normal (99% des cas)

1. **Réservation créée/validée** → Le système charge l'hôtel
2. **Nom de l'hôtel récupéré** → Ex: "International Concord Hotel"
3. **Email envoyé depuis** : `International Concord Hotel <votre_email@gmail.com>`

### Fonctionnement Fallback (cas rare)

1. Si le nom de l'hôtel n'est pas disponible
2. Le système utilise `MAIL_FROM_NAME` du `.env`
3. Email envoyé depuis : `Test Mohamed El Bachir <votre_email@gmail.com>`

---

## ✅ Configuration Minimale

**Vous devez absolument avoir** :
```env
MAIL_FROM_ADDRESS=votre_email@gmail.com
```

**Vous pouvez garder** (sera utilisé en fallback seulement) :
```env
MAIL_FROM_NAME="Test Mohamed El Bachir"
```

---

## 📝 Exemple Complet .env

```env
# Application
APP_NAME="HotelPro"
APP_URL=http://localhost:8000

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelpro
DB_USERNAME=root
DB_PASSWORD=

# Email - SendGrid
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@gmail.com
MAIL_FROM_NAME="Test Mohamed El Bachir"

# Queue
QUEUE_CONNECTION=sync
```

---

## ⚠️ Important

- `MAIL_FROM_ADDRESS` : **DOIT être votre adresse email réelle**
- `MAIL_FROM_NAME` : Peut être n'importe quelle valeur (utilisée seulement en fallback)
- Le nom de l'expéditeur sera automatiquement remplacé par le nom de l'hôtel
- Vous n'avez **PAS besoin** de changer `MAIL_FROM_NAME` pour que ça fonctionne

---

## 🧪 Test

Pour vérifier que ça fonctionne :

1. Validez une réservation
2. Vérifiez l'email reçu
3. Le nom d'expéditeur devrait être le nom de l'hôtel
4. L'adresse devrait être celle de `MAIL_FROM_ADDRESS`

