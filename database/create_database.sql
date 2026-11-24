-- Script de création de la base de données SICOSA
-- Exécuter ce script dans PostgreSQL avant de lancer les migrations Laravel

-- Supprimer la base de données si elle existe (ATTENTION : cela supprimera toutes les données)
-- DROP DATABASE IF EXISTS sicosa_db;

-- Créer la base de données
CREATE DATABASE sicosa_db
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    LC_COLLATE = 'French_France.1252'
    LC_CTYPE = 'French_France.1252'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1;

-- Se connecter à la base de données
\c sicosa_db;

-- Créer l'extension pour la gestion des UUID (optionnel mais recommandé)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Créer l'extension pour la gestion géospatiale (si vous voulez utiliser PostGIS plus tard)
-- CREATE EXTENSION IF NOT EXISTS postgis;

-- Message de confirmation
SELECT 'Base de données sicosa_db créée avec succès!' AS message;

-- Note : Après avoir exécuté ce script, lancez les migrations Laravel avec :
-- php artisan migrate --seed 