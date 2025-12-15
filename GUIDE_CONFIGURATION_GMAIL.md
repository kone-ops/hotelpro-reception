# 📧 GUIDE DE CONFIGURATION GMAIL POUR LES EMAILS

**Votre email** : mohamedelbachir237@gmail.com  
**Date** : 7 Novembre 2025

---

## 🎯 OBJECTIF

Activer l'envoi automatique d'emails depuis votre application Laravel :
- ✅ Confirmation réservation aux clients
- ✅ Validation/Rejet aux clients
- ✅ Notifications à l'hôtel

---

## 📋 ÉTAPES À SUIVRE

### ÉTAPE 1 : Créer un Mot de Passe d'Application Gmail

**Important** : Gmail ne permet plus d'utiliser votre mot de passe normal. Vous devez créer un "App Password".

#### A. Activer la Vérification en 2 Étapes (si pas déjà fait)

1. Allez sur : https://myaccount.google.com/security
2. Cliquez sur "Vérification en 2 étapes"
3. Suivez les instructions pour l'activer
4. ⚠️ **Vous DEVEZ avoir la 2FA activée pour créer un App Password**

#### B. Créer le Mot de Passe d'Application

1. Une fois la 2FA activée, allez sur : https://myaccount.google.com/apppasswords
2. Vous verrez "Mots de passe des applications"
3. Cliquez sur "Sélectionner une application" → Choisissez "Autre (nom personnalisé)"
4. Tapez : "Laravel Hotel App"
5. Cliquez sur "Générer"
6. **Google affichera un mot de passe de 16 caractères (ex: abcd efgh ijkl mnop)**
7. ⚠️ **COPIEZ CE MOT DE PASSE IMMÉDIATEMENT** (vous ne pourrez plus le voir)

---

### ÉTAPE 2 : Configurer le Fichier .env

**Fichier** : `.env` (à la racine du projet)

Cherchez les lignes qui commencent par `MAIL_` et remplacez-les par :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mohamedelbachir237@gmail.com
MAIL_PASSWORD=votre_app_password_16_caracteres
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mohamedelbachir237@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**⚠️ IMPORTANT** :
- Remplacez `votre_app_password_16_caracteres` par le mot de passe généré à l'étape 1
- N'utilisez PAS votre mot de passe Gmail normal
- Le mot de passe d'application ressemble à : `abcdefghijklmnop` (16 caractères, sans espaces)

---

### ÉTAPE 3 : Vérifier la Configuration

Ouvrez un terminal dans votre projet et tapez :

```bash
php artisan config:clear
php artisan cache:clear
```

---

### ÉTAPE 4 : Tester l'Envoi d'Email

#### Option A : Test Simple (Tinker)

```bash
php artisan tinker
```

Puis tapez :

```php
Mail::raw('Test email depuis Laravel', function($message) {
    $message->to('mohamedelbachir237@gmail.com')
            ->subject('Test Laravel Hotel');
});
```

Appuyez sur Entrée. Si vous voyez `=> true`, l'email est parti !

Pour quitter tinker : tapez `exit`

#### Option B : Test Réel (Créer une Réservation)

1. Allez sur votre formulaire public : `http://localhost:8000/f/1` (remplacez 1 par l'ID de votre hôtel)
2. Remplissez le formulaire
3. Soumettez
4. Vous devriez recevoir un email de confirmation !

---

## ✅ CONFIGURATION COMPLÈTE

Voici toutes les variables MAIL à avoir dans votre `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mohamedelbachir237@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mohamedelbachir237@gmail.com
MAIL_FROM_NAME="International Concord Hotel"
```

---

## 🔧 DÉPANNAGE

### Problème 1 : "Authentication failed"
**Cause** : Mauvais mot de passe  
**Solution** : 
- Vérifiez que vous utilisez le mot de passe d'application (16 caractères)
- PAS votre mot de passe Gmail normal
- Recréez un nouveau mot de passe d'application

### Problème 2 : "Connection could not be established"
**Cause** : Pare-feu ou antivirus  
**Solution** :
- Vérifiez que le port 587 n'est pas bloqué
- Désactivez temporairement l'antivirus pour tester

### Problème 3 : "Less secure app access"
**Cause** : Ancien message (plus valide)  
**Solution** : Utilisez les mots de passe d'application (étape 1)

### Problème 4 : "Mail from address not configured"
**Cause** : MAIL_FROM_ADDRESS manquant  
**Solution** : Ajoutez la ligne dans .env

---

## 📊 EMAILS QUI SERONT ENVOYÉS

### 1. À la Création d'une Réservation
- **Client reçoit** : Email de confirmation avec numéro de réservation
- **Hôtel reçoit** : Email de notification (admin + réception)

### 2. Lors de la Validation
- **Client reçoit** : Email de validation avec détails chambre et prix

### 3. Lors du Rejet
- **Client reçoit** : Email de rejet avec raison

---

## 🎨 PERSONNALISATION

### Changer le Nom de l'Expéditeur

Dans `.env` :
```env
MAIL_FROM_NAME="Mon Super Hôtel"
```

### Tester avec un Autre Email

Pour tester sans spammer les vrais clients, changez temporairement dans `PublicFormController.php` :

```php
// Ligne ~171
Mail::to('votre_email_test@gmail.com')->send(new ReservationCreated($reservation));
```

---

## ⚠️ LIMITES GMAIL

**Gmail a des limites d'envoi** :
- **500 emails/jour** pour un compte Gmail gratuit
- **100 destinataires par message**

**Si vous dépassez ces limites**, considérez :
- **SendGrid** (100 emails/jour gratuits)
- **Mailgun** (5000 emails/mois gratuits)
- **Amazon SES** (62 000 emails/mois gratuits la 1ère année)

---

## 📞 BESOIN D'AIDE ?

### Liens Utiles
- Créer App Password : https://myaccount.google.com/apppasswords
- Sécurité Gmail : https://myaccount.google.com/security
- Documentation Laravel Mail : https://laravel.com/docs/mail

### Logs Laravel
Si ça ne marche pas, regardez les logs :
```
storage/logs/laravel.log
```

---

## ✅ CHECKLIST FINALE

Avant de tester, vérifiez :

- [ ] Vérification en 2 étapes activée sur Gmail
- [ ] Mot de passe d'application créé (16 caractères)
- [ ] `.env` mis à jour avec les bonnes informations
- [ ] `MAIL_USERNAME` = mohamedelbachir237@gmail.com
- [ ] `MAIL_PASSWORD` = votre mot de passe d'application
- [ ] `MAIL_FROM_ADDRESS` = mohamedelbachir237@gmail.com
- [ ] Cache nettoyé (`php artisan config:clear`)
- [ ] Test envoyé via tinker ✅
- [ ] Email reçu dans votre boîte Gmail ✅

---

## 🎉 UNE FOIS CONFIGURÉ

Votre système enverra automatiquement :

1. **Formulaire public soumis** →
   - Email au client (confirmation)
   - Email à l'hôtel (notification)

2. **Réservation validée** →
   - Email au client (confirmation + détails)

3. **Réservation rejetée** →
   - Email au client (raison + contact)

**Tout automatique !** 🚀

---

**Créé le** : 7 Novembre 2025  
**Pour** : mohamedelbachir237@gmail.com  
**Projet** : Système de Gestion d'Hôtels

