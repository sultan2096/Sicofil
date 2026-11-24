# Commandes SICOFIL

## Script batch `run_sicofil.bat`

Depuis le dossier du projet :

```batch
run_sicofil.bat backup
run_sicofil.bat import-shp "Sandiara_commune\polygone loulybentegne.shp" --commune=1
run_sicofil.bat import-geojson zones.json --commune=1 --truncate
run_sicofil.bat install-deps
```

## Commandes directes (avec PHP dans le PATH)

```bash
# Docker (si installé)
docker-compose up -d

# Backup (local)
php artisan sicofil:backup --compress

# Backup + upload MinIO + chiffrement + rétention
php artisan sicofil:backup --compress --disk=minio --encrypt --keep=7 --retention-days=14 --delete-local

# Import Shapefile (nécessite php-shapefile)
php artisan sicofil:import:shapefile "Sandiara_commune/polygone loulybentegne.shp" --commune=1

# Import GeoJSON (sans dépendance)
php artisan sicofil:import:geojson-zones zones.json --commune=1 --truncate
```

## Prérequis

### Import Shapefile
- **php-shapefile** : `composer require gasparesganga/php-shapefile` (déjà dans composer.json)
- Ou **GDAL/ogr2ogr** pour conversion manuelle vers GeoJSON

### Backup
- **PostgreSQL** installé avec `pg_dump` dans le PATH
- Ou PostgreSQL dans `C:\Program Files\PostgreSQL\XX\bin\`
- Pour upload : configurer `BACKUP_DISK` (s3/minio) + variables MinIO/S3
- Pour chiffrement : définir `BACKUP_ENCRYPTION_KEY`
- Alertes échec : `BACKUP_ALERT_EMAIL`, `BACKUP_ALERT_PHONE`

### Restauration
```bash
# Depuis fichier local
php artisan sicofil:restore --file=storage/app/backups/sicofil_backup_2025-02-09_020000.sql.gz --force

# Depuis disque distant (MinIO)
php artisan sicofil:restore --disk=minio --remote-file=sicofil_backup_2025-02-09_020000.sql.gz.enc --decrypt-key=... --force
```

### Docker
- Docker Desktop pour Windows
