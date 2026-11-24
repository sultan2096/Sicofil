# SICOFIL - Implémentation Phase 2

## ✅ Nouveaux modules ajoutés

### 1. Docker & Infrastructure
- **Dockerfile** – Image PHP 8.2-fpm pour production
- **docker-compose.yml** – Stack complète :
  - app (Laravel)
  - nginx
  - postgres (PostGIS)
  - redis
  - minio
  - geoserver
  - horizon
  - reverb
- **docker/nginx/default.conf** – Configuration Nginx

### 2. GeoServer (WMS/WFS)
- **config/geoserver.php** – Configuration GeoServer
- **GeoServerController** – Endpoints WMS/WFS
- **resources/views/geoserver/index.blade.php** – Page d’administration
- **Routes** : `/geoserver`, `/api/geoserver/wms-url`, `/api/geoserver/wfs-features`

### 3. MinIO (Stockage objets)
- **config/filesystems.php** – Disque `minio` (S3-compatible)
- **MinIOStorageService** – Service pour photos UC et reçus PDF
- Variables `.env` : `MINIO_ACCESS_KEY`, `MINIO_SECRET_KEY`, `MINIO_ENDPOINT`, `MINIO_BUCKET`

### 4. Import Shapefiles
- **php artisan sicofil:import:shapefile** – Import zones fiscales
- Option `--commune=ID`, `--truncate`, `--dry-run`
- Dépendance : GDAL (ogr2ogr) pour conversion Shapefile → GeoJSON

### 5. Backups automatisés
- **php artisan sicofil:backup** – Backup PostgreSQL (pg_dump)
- Options : `--path=`, `--compress`
- Planification : tous les jours à 2h (`schedule`)

### 6. Laravel Reverb (WebSockets)
- **config/reverb.php** – Configuration Reverb
- **CollectorPositionUpdated** – Événement broadcast pour tracking
- **routes/channels.php** – Canaux `tracking.commune.{id}`, `collector.{id}`

### 7. MFA (Authentification à deux facteurs)
- Migration **add_mfa_to_users** – Colonnes `mfa_enabled`, `mfa_secret`, `mfa_otp_code`, `mfa_otp_expires_at`
- À brancher dans le flux de login (vérification OTP après mot de passe)

### 8. Laravel Passport (OAuth2)
- Configuration dans **AuthServiceProvider**
- Expiration des tokens : 15 jours (access), 30 jours (refresh), 6 mois (personal)
- Commandes : `php artisan passport:install`

### 9. CI/CD (GitHub Actions)
- **.github/workflows/ci.yml** – Pipeline CI
- Étapes : checkout, PHP 8.2, PostgreSQL/PostGIS, migrations, tests

---

## 🚀 Commandes utiles

```bash
# Docker
docker-compose up -d

# Import Shapefile (avec GDAL)
php artisan sicofil:import:shapefile /chemin/zones.shp --commune=1

# Backup
php artisan sicofil:backup --compress

# Passport (première installation)
php artisan passport:install

# Migrations MFA
php artisan migrate
```

---

## 📋 Variables .env à ajouter

```env
# GeoServer
GEOSERVER_ENABLED=false
GEOSERVER_BASE_URL=http://localhost:8081/geoserver
GEOSERVER_USERNAME=admin
GEOSERVER_PASSWORD=geoserver
GEOSERVER_WORKSPACE=sicofil

# MinIO
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_ENDPOINT=http://localhost:9001
MINIO_BUCKET=sicofil

# Reverb (WebSockets)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=sicofil-app
REVERB_APP_KEY=sicofil-key
REVERB_APP_SECRET=sicofil-secret
```
