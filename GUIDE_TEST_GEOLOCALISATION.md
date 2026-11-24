# 🧪 Guide de Test - Géolocalisation et Tracking SICOSA

## 📋 Prérequis

1. **Serveur Laravel démarré** : `http://localhost:8000`
2. **Base de données accessible** avec les tables `paiements` et `collector_tracking`
3. **Navigateur moderne** avec support Geolocation API
4. **Appareil avec GPS** (mobile/tablette recommandé)

## 🔧 Étape 1 : Exécution de la Migration

### Option A : Via le navigateur (Recommandé)
1. Ouvrez : `http://localhost:8000/run_migration.php`
2. Vérifiez que les champs sont ajoutés à la table `paiements`
3. Notez les nouveaux champs : `payment_latitude`, `payment_longitude`, `payment_accuracy`, `payment_method`

### Option B : Via SQL direct
1. Ouvrez phpMyAdmin ou votre client MySQL
2. Exécutez le script : `database/add_geolocation_fields.sql`
3. Vérifiez que les colonnes sont créées

## 🧪 Étape 2 : Test de la Géolocalisation des Paiements

### 👨‍💻 Test Collecteur

1. **Connexion Collecteur** :
   - URL : `http://localhost:8000/collecteur/login`
   - Identifiant : `collecteur01`
   - Mot de passe : `collecteur2025`

2. **Test de géolocalisation** :
   - Allez sur : `http://localhost:8000/collecteur/paiements/create`
   - **Vérifiez** : La carte interactive s'affiche
   - **Cliquez** sur "Capturer ma position GPS"
   - **Autorisez** la géolocalisation dans le navigateur
   - **Vérifiez** : Un marqueur apparaît sur la carte avec vos coordonnées
   - **Vérifiez** : Le bouton devient vert "Position capturée"

3. **Test de validation** :
   - **Essayez** d'enregistrer le paiement sans position GPS
   - **Vérifiez** : Le formulaire refuse la soumission
   - **Capturez** votre position GPS
   - **Vérifiez** : Le formulaire accepte maintenant la soumission

4. **Test de position manuelle** :
   - **Cliquez** sur "Position manuelle"
   - **Cliquez** sur la carte à un endroit différent
   - **Vérifiez** : Un nouveau marqueur apparaît
   - **Vérifiez** : Le formulaire accepte cette position

### 👑 Test Maire - Suivi des Collecteurs

1. **Connexion Maire** :
   - URL : `http://localhost:8000/maire/login`
   - Identifiant : `maire2025`
   - Mot de passe : `sicosa2025`

2. **Test de suivi** :
   - Allez sur : `http://localhost:8000/tracking/live`
   - **Vérifiez** : La carte s'affiche avec les positions des collecteurs
   - **Vérifiez** : Les marqueurs colorés selon le statut
   - **Cliquez** sur "Actualiser" pour mettre à jour
   - **Activez** l'auto-actualisation

3. **Test d'itinéraire** :
   - **Cliquez** sur "Voir l'itinéraire" pour un collecteur
   - **Vérifiez** : Une ligne bleue trace le parcours
   - **Vérifiez** : La carte se centre sur l'itinéraire

## 🧪 Étape 3 : Test de Tracking Temps Réel

### Page de Test Dédiée
1. Ouvrez : `http://localhost:8000/test-tracking.html`
2. **Vérifiez** : La carte s'affiche correctement
3. **Testez** l'auto-actualisation
4. **Vérifiez** les logs de test

### Test Complet
1. **Ouvrez 2 onglets** :
   - Onglet 1 : Collecteur connecté (`/collecteur/paiements/create`)
   - Onglet 2 : Maire connecté (`/tracking/live`)

2. **Dans l'onglet Collecteur** :
   - Capturez votre position GPS
   - Enregistrez un paiement

3. **Dans l'onglet Maire** :
   - **Vérifiez** : La position du collecteur apparaît
   - **Vérifiez** : Le statut est "collecting"
   - **Actualisez** la page si nécessaire

## 🔍 Étape 4 : Vérifications Techniques

### Base de Données
```sql
-- Vérifier les champs ajoutés
DESCRIBE paiements;

-- Vérifier les données de géolocalisation
SELECT id, payment_latitude, payment_longitude, payment_accuracy, payment_method 
FROM paiements 
WHERE payment_latitude IS NOT NULL 
ORDER BY created_at DESC 
LIMIT 5;

-- Vérifier le tracking
SELECT * FROM collector_tracking 
ORDER BY tracked_at DESC 
LIMIT 10;
```

### Console du Navigateur
1. **Ouvrez** les outils de développement (F12)
2. **Allez** dans l'onglet Console
3. **Vérifiez** qu'il n'y a pas d'erreurs JavaScript
4. **Vérifiez** les messages de géolocalisation

### Réseau
1. **Allez** dans l'onglet Network
2. **Capturez** une position GPS
3. **Vérifiez** que la requête POST vers `/tracking/position` est envoyée
4. **Vérifiez** que la réponse est `{"success": true}`

## 🐛 Dépannage

### Problèmes Courants

#### 1. GPS non autorisé
**Symptôme** : "Permission refusée" ou "Position non disponible"
**Solution** :
- Vérifiez les permissions du navigateur
- Utilisez HTTPS en production
- Testez sur un appareil mobile

#### 2. Carte ne se charge pas
**Symptôme** : Zone grise à la place de la carte
**Solution** :
- Vérifiez la connexion internet
- Vérifiez que Leaflet.js se charge
- Vérifiez la console pour les erreurs

#### 3. Tracking ne fonctionne pas
**Symptôme** : Positions ne s'affichent pas sur la carte du maire
**Solution** :
- Vérifiez que la route `/tracking/position` existe
- Vérifiez les permissions CSRF
- Vérifiez que le collecteur est connecté

#### 4. Migration échoue
**Symptôme** : Erreur lors de l'exécution de la migration
**Solution** :
- Vérifiez la connexion à la base de données
- Vérifiez les permissions de l'utilisateur
- Exécutez le script SQL manuellement

## ✅ Checklist de Validation

### Fonctionnalités Collecteur
- [ ] Carte interactive s'affiche
- [ ] Capture GPS fonctionne
- [ ] Position manuelle fonctionne
- [ ] Validation du formulaire fonctionne
- [ ] Paiement s'enregistre avec coordonnées

### Fonctionnalités Maire
- [ ] Carte de suivi s'affiche
- [ ] Positions des collecteurs visibles
- [ ] Statuts des collecteurs corrects
- [ ] Itinéraires tracés
- [ ] Auto-actualisation fonctionne

### Base de Données
- [ ] Champs de géolocalisation ajoutés
- [ ] Données GPS enregistrées
- [ ] Tracking des positions fonctionne
- [ ] Index géographiques créés

### APIs
- [ ] POST `/tracking/position` fonctionne
- [ ] GET `/tracking/positions` fonctionne
- [ ] GET `/tracking/collector/{id}` fonctionne

## 📊 Métriques de Succès

- **Précision GPS** : < 50m en extérieur
- **Temps de capture** : < 10 secondes
- **Temps de tracking** : < 5 secondes
- **Disponibilité** : > 95% des tentatives

## 🚀 Prochaines Étapes

1. **Tests en conditions réelles** avec des collecteurs
2. **Optimisation des performances** pour le tracking
3. **Ajout de géofencing** pour les zones
4. **Notifications push** pour les alertes
5. **Rapports de géolocalisation** détaillés

---

**Testez bien et signalez tout problème ! 🎯**
