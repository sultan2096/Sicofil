-- Corriger le SRID de sandiaracommune si importé avec SRID 0
-- Exécuter dans PostgreSQL (psql ou pgAdmin) sur la base sicosa_postgis

-- Option 1: Si les coordonnées sont déjà en WGS84 (lat/lon), définir SRID 4326
UPDATE sandiaracommune SET geom = ST_SetSRID(geom, 4326) WHERE ST_SRID(geom) = 0;

-- Option 2: Si les coordonnées sont en UTM Zone 28N (Sénégal), transformer vers WGS84
-- UPDATE sandiaracommune SET geom = ST_Transform(ST_SetSRID(geom, 32628), 4326) WHERE ST_SRID(geom) = 0;
