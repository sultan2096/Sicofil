-- ========================================
-- Script de Migration Géolocalisation SICOSA
-- PostgreSQL - À exécuter dans pgAdmin ou psql
-- ========================================

-- Vérifier si les colonnes existent déjà
DO $$
DECLARE
    column_exists INTEGER;
BEGIN
    -- Vérifier payment_latitude
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = 'paiements' 
      AND column_name = 'payment_latitude';
    
    IF column_exists = 0 THEN
        ALTER TABLE paiements ADD COLUMN payment_latitude DECIMAL(10,8);
        RAISE NOTICE 'Colonne payment_latitude ajoutée';
    ELSE
        RAISE NOTICE 'Colonne payment_latitude existe déjà';
    END IF;
    
    -- Vérifier payment_longitude
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = 'paiements' 
      AND column_name = 'payment_longitude';
    
    IF column_exists = 0 THEN
        ALTER TABLE paiements ADD COLUMN payment_longitude DECIMAL(11,8);
        RAISE NOTICE 'Colonne payment_longitude ajoutée';
    ELSE
        RAISE NOTICE 'Colonne payment_longitude existe déjà';
    END IF;
    
    -- Vérifier payment_accuracy
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = 'paiements' 
      AND column_name = 'payment_accuracy';
    
    IF column_exists = 0 THEN
        ALTER TABLE paiements ADD COLUMN payment_accuracy DECIMAL(8,2);
        RAISE NOTICE 'Colonne payment_accuracy ajoutée';
    ELSE
        RAISE NOTICE 'Colonne payment_accuracy existe déjà';
    END IF;
    
    -- Vérifier payment_method
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns 
    WHERE table_schema = 'public' 
      AND table_name = 'paiements' 
      AND column_name = 'payment_method';
    
    IF column_exists = 0 THEN
        ALTER TABLE paiements ADD COLUMN payment_method VARCHAR(50);
        RAISE NOTICE 'Colonne payment_method ajoutée';
    ELSE
        RAISE NOTICE 'Colonne payment_method existe déjà';
    END IF;
    
END $$;

-- Créer l'index géographique s'il n'existe pas
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

-- Test d'insertion (optionnel)
-- Décommentez les lignes suivantes pour tester l'insertion
/*
DO $$
DECLARE
    test_unite_id INTEGER;
    test_paiement_id INTEGER;
BEGIN
    -- Trouver une unité commerciale existante
    SELECT id INTO test_unite_id FROM unites_commerciales LIMIT 1;
    
    IF test_unite_id IS NOT NULL THEN
        -- Insérer un paiement de test avec géolocalisation
        INSERT INTO paiements (
            collecteur_id, unite_id, montant, montant_avance, montant_restant,
            date_paiement, mode_paiement, reference_transaction, commentaire, statut,
            payment_latitude, payment_longitude, payment_accuracy, payment_method,
            created_at, updated_at
        ) VALUES (
            1, test_unite_id, 1000.00, 0.00, 1000.00,
            CURRENT_DATE, 'cash', NULL, 'Test de géolocalisation', 'valide',
            14.43540, -16.79153, 10.5, 'GPS',
            NOW(), NOW()
        ) RETURNING id INTO test_paiement_id;
        
        RAISE NOTICE 'Test d''insertion réussi ! ID du paiement: %', test_paiement_id;
        
        -- Supprimer le test
        DELETE FROM paiements WHERE id = test_paiement_id;
        RAISE NOTICE 'Données de test supprimées';
    ELSE
        RAISE NOTICE 'Aucune unité commerciale trouvée pour le test';
    END IF;
END $$;
*/
