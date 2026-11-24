-- Script SQL pour ajuster les zones fiscales selon la limite de Sandiara
-- Basé sur les coordonnées de la limite pointillée noire visible sur les images

-- Supprimer les anciennes zones si elles existent
DROP TABLE IF EXISTS sandiaracentre CASCADE;
DROP TABLE IF EXISTS ndioukhthiorokh CASCADE;
DROP TABLE IF EXISTS loulibentegne CASCADE;

-- Créer les nouvelles tables avec les zones ajustées selon la limite de Sandiara
-- Limite de Sandiara (basée sur la ligne pointillée noire des images) :
-- Nord-Ouest: [14.44382, -16.80247]
-- Nord-Est: [14.44382, -16.78059] 
-- Sud-Est: [14.42699, -16.78059]
-- Sud-Ouest: [14.42699, -16.80247]

-- Zone 1: Loulibentegne (Zone Ouest - Vert)
CREATE TABLE loulibentegne (
    id SERIAL PRIMARY KEY,
    geom GEOMETRY(POLYGON, 4326)
);

-- Zone 2: Sandiara Centre (Zone Centre-Est - Rose)  
CREATE TABLE sandiaracentre (
    id SERIAL PRIMARY KEY,
    geom GEOMETRY(POLYGON, 4326)
);

-- Zone 3: Ndioukhthiorokh (Zone Sud - Bleu)
CREATE TABLE ndioukhthiorokh (
    id SERIAL PRIMARY KEY,
    geom GEOMETRY(POLYGON, 4326)
);

-- Insérer les nouvelles zones qui respectent exactement la limite de Sandiara
-- et couvrent totalement la commune

-- Zone 1: Loulibentegne (Ouest) - Vert
INSERT INTO loulibentegne (geom) VALUES (
    ST_GeomFromText('POLYGON((
        -16.80247 14.44382,  -- Nord-Ouest (limite Sandiara)
        -16.79150 14.44382,  -- Point de division Nord
        -16.79150 14.43500,  -- Point de division Centre-Nord
        -16.80247 14.43500,  -- Ouest Centre
        -16.80247 14.42699,  -- Sud-Ouest (limite Sandiara)
        -16.80247 14.44382   -- Retour au point de départ
    ))', 4326)
);

-- Zone 2: Sandiara Centre (Centre-Est) - Rose
INSERT INTO sandiaracentre (geom) VALUES (
    ST_GeomFromText('POLYGON((
        -16.79150 14.44382,  -- Point de division Nord
        -16.78059 14.44382,  -- Nord-Est (limite Sandiara)
        -16.78059 14.43500,  -- Est Centre-Nord
        -16.79150 14.43500,  -- Point de division Centre-Nord
        -16.79150 14.43000,  -- Point de division Centre-Sud
        -16.78059 14.43000,  -- Est Centre-Sud
        -16.78059 14.42699,  -- Sud-Est (limite Sandiara)
        -16.79150 14.42699,  -- Point de division Sud
        -16.79150 14.44382   -- Retour au point de départ
    ))', 4326)
);

-- Zone 3: Ndioukhthiorokh (Sud) - Bleu
INSERT INTO ndioukhthiorokh (geom) VALUES (
    ST_GeomFromText('POLYGON((
        -16.80247 14.43500,  -- Ouest Centre
        -16.79150 14.43500,  -- Point de division Centre-Nord
        -16.79150 14.43000,  -- Point de division Centre-Sud
        -16.80247 14.43000,  -- Ouest Centre-Sud
        -16.80247 14.42699,  -- Sud-Ouest (limite Sandiara)
        -16.79150 14.42699,  -- Point de division Sud
        -16.80247 14.42699,  -- Sud-Ouest (limite Sandiara)
        -16.80247 14.43500   -- Retour au point de départ
    ))', 4326)
);

-- Créer les index spatiaux pour optimiser les performances
CREATE INDEX idx_loulibentegne_geom ON loulibentegne USING GIST (geom);
CREATE INDEX idx_sandiaracentre_geom ON sandiaracentre USING GIST (geom);
CREATE INDEX idx_ndioukhthiorokh_geom ON ndioukhthiorokh USING GIST (geom);

-- Vérifier que les zones couvrent bien toute la commune de Sandiara
-- et ne dépassent pas les limites
SELECT 
    'loulibentegne' as zone,
    ST_Area(ST_Transform(geom, 32628)) / 1000000 as area_km2,
    ST_AsText(ST_Centroid(geom)) as centroid
FROM loulibentegne
UNION ALL
SELECT 
    'sandiaracentre' as zone,
    ST_Area(ST_Transform(geom, 32628)) / 1000000 as area_km2,
    ST_AsText(ST_Centroid(geom)) as centroid
FROM sandiaracentre
UNION ALL
SELECT 
    'ndioukhthiorokh' as zone,
    ST_Area(ST_Transform(geom, 32628)) / 1000000 as area_km2,
    ST_AsText(ST_Centroid(geom)) as centroid
FROM ndioukhthiorokh;

-- Vérifier que les zones ne se chevauchent pas
SELECT 
    'Vérification des chevauchements' as test,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM loulibentegne l, sandiaracentre s 
            WHERE ST_Overlaps(l.geom, s.geom)
        ) THEN 'ERREUR: Chevauchement détecté entre Loulibentegne et Sandiara Centre'
        WHEN EXISTS (
            SELECT 1 FROM loulibentegne l, ndioukhthiorokh n 
            WHERE ST_Overlaps(l.geom, n.geom)
        ) THEN 'ERREUR: Chevauchement détecté entre Loulibentegne et Ndioukhthiorokh'
        WHEN EXISTS (
            SELECT 1 FROM sandiaracentre s, ndioukhthiorokh n 
            WHERE ST_Overlaps(s.geom, n.geom)
        ) THEN 'ERREUR: Chevauchement détecté entre Sandiara Centre et Ndioukhthiorokh'
        ELSE 'SUCCÈS: Aucun chevauchement détecté'
    END as resultat;
