# 🚀 SICOFIL - Guide de Démarrage Rapide

## ⚡ Installation en 5 minutes

### 1. Prérequis
- ✅ XAMPP installé avec Apache et PostgreSQL
- ✅ PHP 8.1+ configuré
- ✅ Composer installé
- ✅ Redis Server (optionnel mais recommandé)

### 2. Installation Automatique

Double-cliquez sur `install_sicofil.bat` ou exécutez:

```bash
cd C:\xampp\htdocs\sicosa
install_sicofil.bat
```

Cette commande va:
- 📦 Installer les dépendances Composer
- 🔑 Générer la clé d'application
- 📊 Créer les tables de base de données
- 🌱 Insérer les données de test
- 📝 Configurer Laravel Horizon
- 🗂️ Créer le lien symbolique storage
- 🧹 Nettoyer le cache

### 3. Configuration Rapide

Éditez `.env` et configurez:

```env
# Base de données
DB_CONNECTION=pgsql
DB_DATABASE=sicosa_postgis
DB_USERNAME=postgres
DB_PASSWORD=VotreMotDePasse

# Redis (si disponible)
REDIS_HOST=127.0.0.1
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# SMS (optionnel pour l'instant)
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
```

### 4. Démarrage

Double-cliquez sur `start_sicofil.bat` ou exécutez:

```bash
start_sicofil.bat
```

L'application démarre automatiquement sur:
- 🌐 **Application principale:** http://localhost/sicosa
- 📊 **Laravel Horizon:** http://localhost/sicosa/horizon

## 🎯 Accès Rapides

### Interfaces Principales

#### 1. Portail Public (Contribuable)
**URL:** http://localhost/sicosa/contribuable/login

Test avec un numéro de téléphone (format: 77XXXXXXX):
- Le système envoie un code OTP (simulé en mode développement)
- Consultez les logs pour voir le code OTP généré

#### 2. Admin SICOFIL (Multi-Communes National)
**URL:** http://localhost/sicosa/admin-sicofil/dashboard
- **Email:** adminsicofil@sicofil.sn
- **Mot de passe:** sicofil2026

Fonctionnalités:
- Dashboard national avec statistiques globales
- Gestion des 500+ communes
- Abonnements SaaS et facturation
- Monitoring des webhooks et notifications

#### 3. Interface Maire
**URL:** http://localhost/sicosa/maire/dashboard
- **Identifiant:** maire2025
- **Mot de passe:** sicosa2025

#### 4. Interface Gestionnaire
**URL:** http://localhost/sicosa/gestionnaire/dashboard
- **Identifiant:** gestionnaire2025
- **Mot de passe:** sicosa2025

#### 5. Interface Collecteur
**URL:** http://localhost/sicosa/collecteur/dashboard
- **Identifiant:** collecteur2025
- **Mot de passe:** sicosa2025

#### 6. Laravel Horizon (Monitoring Queues)
**URL:** http://localhost/sicosa/horizon
- Surveillance des jobs asynchrones
- Webhooks Mobile Money
- Notifications SMS
- Statistiques de performance

## 🧪 Tests Fonctionnels

### Test 1: Connexion Contribuable (OTP)

1. Allez sur http://localhost/sicosa/contribuable/login
2. Entrez un numéro de téléphone (ex: 771234567)
3. En mode développement, le code OTP sera loggé dans `storage/logs/laravel.log`
4. Entrez le code pour accéder au dashboard

### Test 2: Webhook Wave (Postman)

```http
POST http://localhost/sicosa/webhooks/wave
Content-Type: application/json
X-Wave-Signature: test_signature

{
  "id": "wave_test_12345",
  "client_reference": "REF-TEST123",
  "amount": 5000,
  "currency": "XOF",
  "status": "successful",
  "customer_phone": "771234567"
}
```

Vérifiez dans Laravel Horizon que le job a été traité.

### Test 3: Envoi SMS Manuel

Dans `php artisan tinker`:

```php
use App\Jobs\SendSmsNotification;

SendSmsNotification::dispatch(
    '771234567',
    'Test SICOFIL - Message de test',
    'info_generale',
    1
);
```

### Test 4: Création d'Abonnement

```php
DB::table('abonnements')->insert([
    'commune_id' => 1,
    'statut' => 'actif',
    'formule' => 'standard',
    'montant_fixe_annuel' => 500000,
    'pourcentage_recettes' => 5,
    'date_debut' => now(),
    'date_expiration' => now()->addYear(),
    'nb_collecteurs_max' => 20,
    'acces_geomarketing' => true,
    'acces_rapports_avances' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

## 📊 Monitoring & Logs

### Laravel Horizon
- **Dashboard:** http://localhost/sicosa/horizon
- **Métriques:** Jobs traités, échoués, en attente
- **Queues:** default, webhooks, notifications

### Logs Application
```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Logs spécifiques
storage/logs/laravel-YYYY-MM-DD.log
```

### Base de Données - Tables de Monitoring

```sql
-- Logs webhooks
SELECT * FROM webhook_logs ORDER BY webhook_received_at DESC LIMIT 10;

-- Notifications en attente
SELECT * FROM notifications WHERE statut = 'en_attente';

-- Audit SICOFIL
SELECT * FROM audit_sicofil ORDER BY created_at DESC LIMIT 20;

-- Abonnements actifs
SELECT c.nom, a.* 
FROM abonnements a 
JOIN communes c ON a.commune_id = c.id 
WHERE a.statut = 'actif';
```

## 🔧 Commandes Utiles

### Artisan

```bash
# Nettoyage complet
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Relancer les migrations
php artisan migrate:fresh --seed

# Générer des factures mensuelles
php artisan app:generate-monthly-invoices

# Horizon
php artisan horizon
php artisan horizon:terminate
php artisan horizon:snapshot
```

### Redis (si installé)

```bash
# Vérifier la connexion Redis
redis-cli ping

# Voir les clés
redis-cli KEYS *

# Statistiques
redis-cli INFO stats

# Vider le cache Redis
redis-cli FLUSHALL
```

### PostgreSQL

```bash
# Se connecter
psql -U postgres -d sicosa_postgis

# Backup
pg_dump -U postgres sicosa_postgis > backup.sql

# Restore
psql -U postgres -d sicosa_postgis < backup.sql
```

## 🛠️ Résolution de Problèmes

### Problème: Erreur 500 après installation
**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
chmod -R 755 storage bootstrap/cache
```

### Problème: Redis non disponible
**Solution:**
- Installer Memurai (Redis pour Windows): https://www.memurai.com
- Ou modifier `.env`:
```env
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### Problème: Jobs non traités
**Solution:**
```bash
# Vérifier Horizon
php artisan horizon:status

# Redémarrer Horizon
php artisan horizon:terminate
php artisan horizon

# En dernier recours, utiliser le worker simple
php artisan queue:work
```

### Problème: Webhooks non reçus
**Solution:**
- Vérifier les routes: `php artisan route:list | grep webhook`
- Tester avec Postman
- Vérifier les logs: `storage/logs/laravel.log`
- Vérifier la table `webhook_logs`

### Problème: SMS non envoyés
**Solution:**
- Vérifier la configuration `.env` (TWILIO_*, INFOBIP_*)
- En mode `local`, les SMS sont simulés et loggés
- Vérifier la table `notifications`

## 📦 Structure des Fichiers Clés

```
sicosa/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminSicofilController.php      ← Admin national
│   │   ├── ContribuableController.php      ← Portail public
│   │   └── WebhookController.php           ← Webhooks Mobile Money
│   ├── Jobs/
│   │   ├── ProcessWaveWebhook.php          ← Job Wave
│   │   ├── ProcessOrangeMoneyWebhook.php   ← Job Orange Money
│   │   └── SendSmsNotification.php         ← Job SMS
│   └── Models/
│       ├── Contribuable.php                ← Modèle Contribuable
│       └── Commune.php                     ← Modèle Commune
├── config/
│   ├── horizon.php                         ← Config Laravel Horizon
│   ├── services.php                        ← Config APIs externes
│   └── auth.php                            ← Guards & providers
├── database/migrations/
│   ├── 2026_02_09_000001_create_contribuables_table.php
│   ├── 2026_02_09_000002_create_abonnements_table.php
│   ├── 2026_02_09_000003_create_webhooks_notifications_tables.php
│   └── 2026_02_09_000004_add_contribuable_id_to_paiements.php
├── resources/views/
│   ├── contribuable/
│   │   ├── login.blade.php                 ← OTP Login
│   │   └── dashboard.blade.php             ← Dashboard contribuable
│   └── admin/sicofil/
│       └── dashboard.blade.php             ← Dashboard Admin SICOFIL
├── routes/
│   └── web.php                             ← Routes (+ Contribuable + Webhooks)
├── install_sicofil.bat                     ← Installation automatique
├── start_sicofil.bat                       ← Démarrage rapide
├── SICOFIL_IMPLEMENTATION.md               ← Documentation complète
└── DEMARRAGE_RAPIDE.md                     ← Ce fichier
```

## 🚨 Checklist Avant Production

- [ ] Configurer les vraies APIs (Wave, Orange Money, Free Money)
- [ ] Configurer un fournisseur SMS réel (Twilio/InfoBip)
- [ ] Activer HTTPS (SSL/TLS)
- [ ] Configurer les backups automatiques PostgreSQL
- [ ] Installer et configurer Redis en production
- [ ] Configurer Laravel Horizon en production
- [ ] Configurer un reverse proxy (Nginx)
- [ ] Activer les logs centralisés (Sentry, LogStash)
- [ ] Mettre en place le monitoring (Grafana, New Relic)
- [ ] Dockeriser l'application
- [ ] Configurer le CI/CD (GitHub Actions)
- [ ] Tester la charge (Apache Bench, K6)
- [ ] Audit de sécurité complet
- [ ] Formation des utilisateurs finaux

## 📞 Support

**Documentation complète:** `SICOFIL_IMPLEMENTATION.md`

**Logs:** `storage/logs/laravel.log`

**Assistance technique:**
- Email: support@sicofil.sn
- GitHub Issues: (à définir)

---

🎉 **Bravo ! SICOFIL est maintenant opérationnel !**
