-- Script SQL PostgreSQL pour ajouter les champs de géolocalisation à la table paiements
-- Ce script peut être exécuté directement dans pgAdmin ou tout autre client PostgreSQL

-- Vérifier si les colonnes existent déjà
DO $$
BEGIN
    -- Ajouter payment_latitude si elle n'existe pas
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name = 'paiements' AND column_name = 'payment_latitude') THEN
        ALTER TABLE paiements ADD COLUMN payment_latitude DECIMAL(10,8);
    END IF;

    -- Ajouter payment_longitude si elle n'existe pas  
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name = 'paiements' AND column_name = 'payment_longitude') THEN
        ALTER TABLE paiements ADD COLUMN payment_longitude DECIMAL(11,8);
    END IF;

    -- Ajouter payment_accuracy si elle n'existe pas
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name = 'paiements' AND column_name = 'payment_accuracy') THEN
        ALTER TABLE paiements ADD COLUMN payment_accuracy DECIMAL(8,2);
    END IF;

    -- Ajouter payment_method si elle n'existe pas
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name = 'paiements' AND column_name = 'payment_method') THEN
        ALTER TABLE paiements ADD COLUMN payment_method VARCHAR(50);
    END IF;
END $$;

-- Ajouter l'index pour les requêtes géographiques (si il n'existe pas)
CREATE INDEX IF NOT EXISTS idx_payment_location ON paiements (payment_latitude, payment_longitude);

-- Vérifier que les colonnes ont été ajoutées
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_schema = 'public' 
  AND table_name = 'paiements' 
  AND column_name LIKE 'payment_%'
ORDER BY ordinal_position;

-- Message de confirmation
SELECT 'Migration de géolocalisation PostgreSQL terminée avec succès!' as status;
