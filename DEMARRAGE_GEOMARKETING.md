# 🚀 Guide de Démarrage Rapide - Géomarketing SICOSA

## ⚡ Démarrage Express

### **Option 1 : Script Automatique (Recommandé)**
1. **Double-cliquez** sur `start_sicosa_geomarketing.bat`
2. **Attendez** que le script configure tout automatiquement
3. **Ouvrez** http://127.0.0.1:8000/maire/geomarketing/advanced

### **Option 2 : Démarrage Manuel**
```bash
# 1. Démarrer XAMPP (Apache + MySQL)
# 2. Ouvrir PowerShell dans le projet
cd C:\xampp\htdocs\sicosa-sandiara-main

# 3. Installer les dépendances (si nécessaire)
C:\xampp\php\php.exe composer install

# 4. Créer les tables de géomarketing
C:\xampp\php\php.exe artisan migrate

# 5. Ajouter les données de démonstration
C:\xampp\php\php.exe artisan db:seed --class=GeomarketingDemoSeeder

# 6. Démarrer le serveur
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

## 🎯 Accès aux Interfaces

| Interface | URL | Description |
|-----------|-----|-------------|
| **Géomarketing Avancé** | `/maire/geomarketing/advanced` | Interface principale du maire |
| **Page de Test** | `/test-geomarketing.html` | Tests des fonctionnalités |
| **API de Base** | `/test-api` | Test de connexion API |

## 🔧 Configuration Requise

### **Système**
- ✅ Windows 10/11
- ✅ XAMPP (Apache + MySQL + PHP 8.0+)
- ✅ 4GB RAM minimum
- ✅ 2GB espace disque libre

### **Laravel**
- ✅ Laravel 8+ installé
- ✅ Composer installé
- ✅ Base de données configurée

## 📊 Fonctionnalités Disponibles

### **🎯 Analyse des Opportunités**
- **Score d'opportunité (0-100)** basé sur 4 critères
- **Densité commerciale** : Identifie les zones sous-développées
- **Accessibilité routière** : Évalue la proximité des axes principaux
- **Potentiel bâti** : Analyse l'espace disponible
- **Population** : Évalue le potentiel client

### **🗺️ Couches Géographiques**
- **👥 Population** : Données démographiques par zone
- **🛣️ Routes** : Réseau routier avec classification
- **🏢 Bâti** : Bâtiments et espaces disponibles
- **🔥 Heatmap** : Visualisation de la densité
- **🔗 Multi-critères** : Score combiné

### **💡 Propositions d'Investissement**
- **Types** : Alimentation, atelier, supermarché, restaurant, pharmacie
- **Budgets** : Faible (1-5M), Moyen (5-20M), Élevé (20M+)
- **Population cible** : Faible, Moyenne, Élevée
- **Zones préférées** : Sélection multiple

## 🎮 Utilisation Rapide

### **1. Première Analyse**
1. **Ouvrez** `/maire/geomarketing/advanced`
2. **Laissez** tous les filtres vides
3. **Cliquez** sur "Analyser"
4. **Observez** les scores d'opportunité par zone

### **2. Filtrage Avancé**
1. **Sélectionnez** une nature d'activité (ex: Commerce de détail)
2. **Choisissez** un type de commerce (ex: Alimentation générale)
3. **Spécifiez** une zone (optionnel)
4. **Cliquez** sur "Analyser"

### **3. Utilisation des Couches**
1. **Cochez** "Population" pour voir la densité démographique
2. **Cochez** "Routes" pour voir l'accessibilité
3. **Cochez** "Bâti" pour voir l'espace disponible
4. **Cochez** "Heatmap" pour la visualisation de densité
5. **Cochez** "Carte multi-critères" pour le score combiné

### **4. Génération de Propositions**
1. **Cliquez** sur "Générer des propositions"
2. **Sélectionnez** le type d'investissement
3. **Choisissez** le niveau de budget
4. **Définissez** la population cible
5. **Générez** les propositions

## 🔍 Tests et Diagnostic

### **Page de Test Complète**
- **URL** : `/test-geomarketing.html`
- **Fonction** : Test de toutes les API et couches
- **Utilisation** : Diagnostic en cas de problème

### **Tests API Individuels**
```bash
# Test de base
curl http://127.0.0.1:8000/test-api

# Test population
curl http://127.0.0.1:8000/maire/geomarketing/population

# Test routes
curl http://127.0.0.1:8000/maire/geomarketing/roads

# Test bâti
curl http://127.0.0.1:8000/maire/geomarketing/bati
```

## 🚨 Résolution de Problèmes

### **Problème : "API non accessible"**
```bash
# Solution 1 : Vérifier XAMPP
# - Démarrer Apache et MySQL dans XAMPP

# Solution 2 : Vérifier le serveur Laravel
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000

# Solution 3 : Vérifier les routes
C:\xampp\php\php.exe artisan route:list | findstr geomarketing
```

### **Problème : "Tables manquantes"**
```bash
# Créer les tables
C:\xampp\php\php.exe artisan migrate

# Ajouter les données de démonstration
C:\xampp\php\php.exe artisan db:seed --class=GeomarketingDemoSeeder
```

### **Problème : "Erreur de géométrie"**
```bash
# Vérifier la base de données
C:\xampp\php\php.exe artisan tinker --execute="
    echo 'Zones: ' . App\Models\ZoneFiscale::count();
    echo 'Population: ' . App\Models\PopulationZone::count();
    echo 'Routes: ' . App\Models\RouteVoirie::count();
    echo 'Bâti: ' . App\Models\Batiment::count();
"
```

## 📈 Exemples d'Utilisation

### **Scénario 1 : Analyse Globale**
- **Objectif** : Identifier toutes les zones d'opportunité
- **Filtres** : Aucun (analyse complète)
- **Résultat** : Carte avec scores d'opportunité par zone

### **Scénario 2 : Analyse Ciblée**
- **Objectif** : Trouver des zones pour un supermarché
- **Filtres** : Type = Supermarché, Budget = Élevé
- **Résultat** : Zones recommandées avec justifications

### **Scénario 3 : Analyse Comparative**
- **Objectif** : Comparer plusieurs zones
- **Filtres** : Zones spécifiques sélectionnées
- **Résultat** : Analyse détaillée avec recommandations

## 🎉 Félicitations !

Vous avez maintenant un système de géomarketing avancé complet qui vous permet de :

✅ **Analyser** les opportunités commerciales par zone  
✅ **Visualiser** les données géographiques en couches  
✅ **Générer** des propositions d'investissement intelligentes  
✅ **Prendre** des décisions stratégiques basées sur des données  

**Prochaine étape** : Explorez les fonctionnalités et adaptez-les à vos besoins spécifiques !

---

*Guide créé pour SICOSA Sandiara - Version Géomarketing 2.0*



