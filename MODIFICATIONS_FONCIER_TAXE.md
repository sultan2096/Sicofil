# Modifications : Foncier, Taxe Foncière et Dessin de Polygones

## Résumé des modifications

Ce document décrit les modifications apportées au système pour :
1. Renommer "Location" en "Foncier"
2. Ajouter la taxe foncière avec montant par m²
3. Ajouter le dessin de polygones pour les terrains
4. Sauvegarder les polygones dans PostGIS

## 1. Renommage "Location" → "Foncier"

### Fichiers modifiés :
- `database/seeders/NatureActiviteSeeder.php` : Nature "Location" renommée en "Foncier"
- `resources/views/unites/create.blade.php` : Mise à jour des données JavaScript
- `app/Http/Controllers/UniteCommercialeController.php` : Mise à jour du mapping

### Commande pour mettre à jour la base de données existante :
```bash
php artisan update:location-to-foncier
```

## 2. Taxe Foncière

### Modifications de la base de données :
- **Migration** : `2025_01_15_000001_add_foncier_fields_to_taxes_table.php`
  - Ajout du champ `is_fonciere` (boolean)
  - Ajout du champ `montant_par_m2` (decimal)

### Fichiers modifiés :
- `app/Models/Taxe.php` : Ajout des champs `is_fonciere` et `montant_par_m2`
- `app/Http/Controllers/TaxeController.php` : Validation et gestion de la taxe foncière
- `resources/views/taxes/create.blade.php` : Interface pour créer une taxe foncière
- `resources/views/taxes/edit.blade.php` : Interface pour modifier une taxe foncière

### Utilisation :
1. Aller sur `/taxes/create`
2. Cocher "Taxe foncière (montant par m²)"
3. Saisir le montant par m² (ex: 100 FCFA/m²)
4. La périodicité reste la même (mensuel, annuel, etc.)

## 3. Dessin de Polygones pour Terrains

### Modifications de la base de données :
- **Migration** : `2025_01_15_000002_add_polygon_fields_to_unites_commerciales_table.php`
  - Ajout du champ `superficie` (decimal) : superficie en m²
  - Ajout du champ `geometrie_polygon` (json) : géométrie au format GeoJSON

- **Migration PostGIS** : `2025_01_15_000003_create_foncier_polygons_postgis_table.php`
  - Création de la table `public.foncier_polygons` dans PostGIS
  - Stockage des polygones avec géométrie spatiale
  - Index spatial pour améliorer les performances

### Fichiers modifiés :
- `app/Models/UniteCommerciale.php` : Ajout des champs `superficie` et `geometrie_polygon`
- `app/Http/Controllers/UniteCommercialeController.php` : 
  - Validation des champs polygonaux
  - Sauvegarde dans PostGIS
- `resources/views/unites/create.blade.php` : 
  - Ajout de Leaflet.draw
  - Interface de dessin de polygone
  - Calcul automatique de la superficie

### Fonctionnement :
1. Lors du recensement, sélectionner :
   - Catégorie : "Points d'Intérêt Commerciaux"
   - Nature : "Foncier"
   - Type : "Location de terrain"

2. La section "Dessin du terrain (Polygone)" s'affiche automatiquement

3. Cliquer sur "Commencer le dessin"

4. Cliquer sur la carte pour créer les points du polygone (minimum 4 points)

5. Double-cliquer pour terminer le dessin

6. La superficie est calculée automatiquement en m²

7. Le polygone est sauvegardé :
   - Dans la table `unites_commerciales` (champ `geometrie_polygon` en JSON)
   - Dans la table PostGIS `public.foncier_polygons` (géométrie spatiale)

## 4. Exécution des migrations

### Étape 1 : Exécuter les migrations
```bash
php artisan migrate
```

### Étape 2 : Mettre à jour les natures d'activité existantes
```bash
php artisan update:location-to-foncier
```

### Étape 3 : (Optionnel) Reseeder les natures d'activité
```bash
php artisan db:seed --class=NatureActiviteSeeder
```

## 5. Création d'une taxe foncière

1. Aller sur `/taxes/create`
2. Remplir les champs :
   - Nom : "Taxe foncière"
   - Description : "Taxe sur les terrains selon la superficie"
   - Cocher "Taxe foncière (montant par m²)"
   - Montant par m² : Par exemple, 100 FCFA/m²
   - Périodicité : Mensuel ou Annuel

## 6. Recensement d'un terrain avec polygone

1. Aller sur `/unites/create`
2. Capturer la position GPS
3. Remplir les informations de base
4. Sélectionner :
   - Catégorie : "Points d'Intérêt Commerciaux"
   - Nature : "Foncier"
   - Type : "Location de terrain"
5. La section de dessin de polygone s'affiche
6. Cliquer sur "Commencer le dessin"
7. Dessiner le polygone sur la carte (cliquer aux 4 coins du terrain)
8. Double-cliquer pour terminer
9. Vérifier la superficie calculée
10. Sélectionner la taxe foncière si applicable
11. Enregistrer

## 7. Architecture PostGIS

### Table `public.foncier_polygons`
- `id` : Identifiant unique
- `unite_commerciale_id` : Référence vers l'unité commerciale
- `geom` : Géométrie polygonale (GEOMETRY(POLYGON, 4326))
- `superficie_m2` : Superficie en m²
- `created_at` / `updated_at` : Timestamps

### Index spatial
- Index GIST sur `geom` pour améliorer les performances des requêtes spatiales

## 8. Calcul de la taxe foncière

Lors du paiement, si l'unité a une taxe foncière :
- Montant = `superficie` (m²) × `taxe.montant_par_m2` (FCFA/m²)

Exemple :
- Superficie : 500 m²
- Montant par m² : 100 FCFA/m²
- Montant de la taxe : 500 × 100 = 50 000 FCFA

## 9. Notes importantes

1. **PostGIS requis** : La fonctionnalité de dessin de polygone nécessite que PostGIS soit installé et activé dans PostgreSQL.

2. **Calcul de superficie** : Le calcul utilise la formule de Shoelace adaptée pour les coordonnées géographiques. La précision dépend de la taille du terrain et de la latitude.

3. **Validation** : Le polygone doit avoir au moins 4 points et ne peut pas se croiser.

4. **Sauvegarde** : Si la sauvegarde dans PostGIS échoue, l'unité commerciale est quand même créée (la géométrie reste dans le champ JSON).

## 10. Prochaines étapes possibles

- Affichage des polygones sur la carte principale (`/map`)
- Calcul automatique de la taxe lors de la sélection d'une taxe foncière
- Export des polygones en format Shapefile
- Statistiques sur les terrains recensés par zone
- Validation de chevauchement de polygones

## 11. Dépannage

### Erreur : "Table public.foncier_polygons does not exist"
```bash
php artisan migrate
```

### Erreur : "PostGIS extension not found"
Vérifier que PostGIS est installé dans PostgreSQL :
```sql
CREATE EXTENSION IF NOT EXISTS postgis;
```

### La section de dessin ne s'affiche pas
- Vérifier que la nature est "Foncier"
- Vérifier que le type est "Location de terrain"
- Vérifier que Leaflet.draw est chargé (vérifier la console du navigateur)

### Le calcul de superficie est incorrect
- Vérifier que le polygone est bien fermé
- Vérifier que les coordonnées sont valides
- Le calcul est une approximation, surtout pour les grands terrains

