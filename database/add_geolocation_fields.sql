-- Script SQL pour ajouter les champs de géolocalisation à la table paiements
-- Ce script peut être exécuté directement dans phpMyAdmin ou tout autre client MySQL

-- Vérifier si les colonnes existent déjà
SET @sql = '';

-- Ajouter payment_latitude si elle n'existe pas
SET @sql = CONCAT(@sql, 'ALTER TABLE paiements ADD COLUMN IF NOT EXISTS payment_latitude DECIMAL(10,8) NULL AFTER reference_transaction; ');

-- Ajouter payment_longitude si elle n'existe pas  
SET @sql = CONCAT(@sql, 'ALTER TABLE paiements ADD COLUMN IF NOT EXISTS payment_longitude DECIMAL(11,8) NULL AFTER payment_latitude; ');

-- Ajouter payment_accuracy si elle n'existe pas
SET @sql = CONCAT(@sql, 'ALTER TABLE paiements ADD COLUMN IF NOT EXISTS payment_accuracy DECIMAL(8,2) NULL AFTER payment_longitude; ');

-- Ajouter payment_method si elle n'existe pas
SET @sql = CONCAT(@sql, 'ALTER TABLE paiements ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL AFTER payment_accuracy; ');

-- Ajouter l'index pour les requêtes géographiques
SET @sql = CONCAT(@sql, 'ALTER TABLE paiements ADD INDEX IF NOT EXISTS idx_payment_location (payment_latitude, payment_longitude); ');

-- Exécuter les commandes
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Vérifier que les colonnes ont été ajoutées
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'paiements' 
  AND COLUMN_NAME LIKE 'payment_%'
ORDER BY ORDINAL_POSITION;

-- Message de confirmation
SELECT 'Migration de géolocalisation terminée avec succès!' as Status;
