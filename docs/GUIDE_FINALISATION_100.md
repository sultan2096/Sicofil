# Guide de finalisation SICOFIL à 100 %

Ce document décrit les étapes réalisées et celles à compléter pour une plateforme SICOFIL pleinement opérationnelle.

## ✅ Réalisé

### Phase 1 : Base stabilisée
- Migrations SaaS exécutées : `abonnements`, `factures_abonnement`, `webhook_logs`, `notifications`, `audit_sicofil`
- Middleware `BlockTestRoutesInProduction` : bloque `/test-*` et `/debug-*` en production
- Migration `set_commune_id_on_existing_taxes` exécutée

### Phase 2–4 : Portail contribuable
- Correction création contribuable : `commune_id` attribué automatiquement
- OTP en mode dev : sans SMS configuré, le code est retourné dans la réponse JSON et affiché
- Flux OTP : `verifyOtp` retourne JSON pour les requêtes AJAX
- Commande `sicofil:generate-factures` pour génération automatique des factures
- Planificateur : factures mensuelles générées le 1er du mois à 3h

### Configuration
- `.env.example` enrichi avec variables Mobile Money et SMS

## 🔧 À configurer pour 100 %

### 1. Mobile Money (Wave / Orange Money)

1. Créer un compte marchand chez Wave et/ou Orange Money Sénégal
2. Remplir `.env` :
   ```
   WAVE_API_KEY=votre_clé
   WAVE_API_SECRET=votre_secret
   WAVE_WEBHOOK_SECRET=secret_webhook
   WAVE_MODE=sandbox   # puis production
   ```
3. Configurer l’URL webhook dans le tableau de bord Wave :  
   `https://votre-domaine.com/webhooks/wave`
4. Tester un paiement depuis le portail contribuable

### 2. SMS (OTP et notifications)

1. Choisir Twilio, InfoBip ou Orange SMS API
2. Remplir `.env` :
   ```
   SMS_PROVIDER=twilio
   TWILIO_ACCOUNT_SID=xxx
   TWILIO_AUTH_TOKEN=xxx
   TWILIO_PHONE_NUMBER=+221...
   ```
3. Tester l’OTP sur `/contribuable/login`

### 3. Redis (optionnel mais recommandé)

1. Installer Redis (Docker ou local)
2. Dans `.env` :
   ```
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```
3. Lancer `php artisan horizon` si Horizon est installé

### 4. Cron / Scheduler

Ajouter au crontab du serveur :

```
* * * * * cd /chemin/sicosa && php artisan schedule:run >> /dev/null 2>&1
```

## Commandes utiles

```bash
# Générer les factures manuellement
php artisan sicofil:generate-factures

# Générer pour un mois précis
php artisan sicofil:generate-factures --mois=1 --annee=2026

# Réensemencer les données de démo
php artisan db:seed --class=DemoSicofilSeeder
```

## Abonnements et import SHP multi-communes

- **Abonnements** : `/admin-sicofil/abonnements` — liste, détail, suspendre/activer. Bouton **Créer un abonnement** pour une commune sans abonnement.
- **Import SHP autre commune** : les zones importées vont dans la table `zones_fiscales` avec `commune_id`. Elles s’affichent **directement sur la carte** via la couche « zones » (toutes communes).
  - Commande :  
    `php artisan sicofil:import:shapefile chemin/vers/fichier.shp --commune=ID`  
  - Exemple pour la commune Thiès (ID 2) :  
    `php artisan sicofil:import:shapefile C:\data\zones_thies.shp --commune=2`  
  - La carte (`/map`) charge par défaut la couche **zones** (violette) = toutes les zones fiscales, y compris SHP importés.

## URLs principales

| Page                    | URL                              |
|-------------------------|-----------------------------------|
| Portail contribuable    | `/contribuable/login`            |
| Admin SICOFIL           | `/multi-communes/login`          |
| Dashboard Admin         | `/admin-sicofil/dashboard`       |
| Abonnements             | `/admin-sicofil/abonnements`     |
| Créer abonnement        | `/admin-sicofil/abonnements/create` |
| Factures                | `/admin-sicofil/factures`        |
| Carte (zones plateforme)| `/map`                            |
