# 📧 Valeurs .env pour SendGrid

## Configuration Minimale

Remplacez **UNIQUEMENT** ces lignes dans votre fichier `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.VOTRE_API_KEY_SENDGRID_ICI
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**⚠️ IMPORTANT** :
- `MAIL_USERNAME` doit être **exactement** `apikey` (pas votre email)
- `MAIL_PASSWORD` = votre API Key SendGrid (commence par `SG.`)
- `MAIL_FROM_ADDRESS` = votre adresse email actuelle (ex: votre_email@gmail.com)
- Les autres valeurs `MAIL_*` peuvent rester telles quelles

## Exemple Complet

Si votre `.env` actuel contient :
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Remplacez par :
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note** : Gardez `MAIL_FROM_ADDRESS` avec votre email actuel, mais vous devrez vérifier cet email dans SendGrid (Single Sender Verification).

## Après Modification

```bash
php artisan config:clear
php artisan cache:clear
```

---

**Résumé des changements** :
- ✅ `MAIL_HOST` : `smtp.gmail.com` → `smtp.sendgrid.net`
- ✅ `MAIL_USERNAME` : votre email → `apikey`
- ✅ `MAIL_PASSWORD` : votre mot de passe → votre API Key SendGrid
- ✅ `MAIL_FROM_ADDRESS` : gardez votre email actuel
- ✅ `MAIL_ENCRYPTION` : reste `tls`
- ✅ `MAIL_PORT` : reste `587`

