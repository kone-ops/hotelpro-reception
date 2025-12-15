# ⚡ GUIDE RAPIDE - CONFIGURATION SENDGRID

## 🚀 Configuration en 3 Étapes

### 1️⃣ Créer une API Key SendGrid

1. Allez sur : https://app.sendgrid.com/
2. **Settings** → **API Keys** → **Create API Key**
3. Nom : `Laravel Hotel App`
4. Permissions : **Full Access**
5. **COPIEZ L'API KEY** (commence par `SG.`)

### 2️⃣ Mettre à jour le .env

Remplacez **UNIQUEMENT** ces lignes dans votre `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.VOTRE_API_KEY_ICI
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
QUEUE_CONNECTION=sync
```

**⚠️ IMPORTANT** :
- `MAIL_USERNAME` = exactement `apikey` (pas votre email)
- `MAIL_PASSWORD` = votre API Key complète
- `MAIL_FROM_ADDRESS` = votre adresse email actuelle (vous pouvez garder la même)

### 3️⃣ Vérifier l'Expéditeur

1. **Settings** → **Sender Authentication** → **Verify a Single Sender**
2. Entrez votre email d'expéditeur
3. Vérifiez votre email (cliquez sur le lien reçu)

### 4️⃣ Tester

```bash
php artisan config:clear
php artisan cache:clear
```

Puis validez une réservation et vérifiez que :
- ✅ Email reçu en < 1 seconde
- ✅ PDF attaché avec nom du client
- ✅ Nom du fichier : `Reservation_[NomClient]_[ID].pdf`

---

## 📎 Fonctionnalité PDF

L'email de validation inclut automatiquement :
- ✅ **PDF de confirmation** avec toutes les infos
- ✅ **Nom personnalisé** : `Reservation_KING_EL_BACHIR_0000123.pdf`
- ✅ **Format A5** optimisé

---

## ⚡ Performance

- **Latence** : 100-500ms (généralement < 1 seconde)
- **Gratuit** : 100 emails/jour
- **Taux de livraison** : > 99%

---

## 🔧 Dépannage Rapide

**"Authentication failed"** → Vérifiez que `MAIL_USERNAME=apikey` (exactement)

**"Sender not verified"** → Vérifiez votre Single Sender dans SendGrid

**Pas de PDF** → Vérifiez les logs : `storage/logs/laravel.log`

---

**Documentation complète** : Voir `CONFIGURATION_SENDGRID.md`

