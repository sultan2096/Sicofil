# 🗺️ Fonctionnalités de la Carte SICOSA

## 📋 Vue d'ensemble

La carte SICOSA offre plusieurs fonctionnalités avancées pour l'analyse géomarketing et la gestion des unités commerciales de la commune de Sandiara.

## 🚀 Fonctionnalités Principales

### 1. **Carte Interactive**
- **Carte de base** : OpenStreetMap avec vue centrée sur Sandiara
- **Zones fiscales** : Affichage des limites des zones avec couleurs distinctes
- **Unités commerciales** : Marqueurs personnalisés selon le type d'activité
- **Limites communales** : Contour de la commune de Sandiara

### 2. **Filtres et Recherche**
- **Par zone fiscale** : Filtrer les unités par zone géographique
- **Par catégorie** : Activités commerciales vs Points d'intérêt
- **Par nature** : Boutique, Restaurant, Salon, Atelier, etc.
- **Recherche textuelle** : Recherche par nom, type ou adresse
- **Réinitialisation** : Bouton pour effacer tous les filtres

### 3. **Carte Thématique Avancée**
- **Statut de paiement** : Filtrer par unités payées/impayées
- **Période** : Sélection de dates pour l'analyse temporelle
- **Type de commerce** : Filtrage par catégorie commerciale
- **Statistiques en temps réel** : Compteurs mis à jour dynamiquement

### 4. **Export et Rapports**
- **Export Image** : Capture de la carte en PNG haute qualité
- **Export PDF** : Génération de rapports cartographiques
- **Mode rapport** : Affichage des éléments d'export (titre, nord, QR)
- **Personnalisation** : Titres dynamiques selon les filtres appliqués

## 🔧 Comment Utiliser

### **Accès à la Carte**
1. **Carte principale** : `/map` - Vue générale des unités commerciales
2. **Carte thématique** : `/rapports/carte-thematique` - Analyse avancée avec filtres

### **Utilisation des Filtres**
1. **Sélectionner les critères** dans les menus déroulants
2. **Cliquer sur "Appliquer"** pour mettre à jour la carte
3. **Observer les changements** en temps réel
4. **Utiliser la recherche** pour trouver des unités spécifiques

### **Navigation sur la Carte**
- **Zoom** : Molette de souris ou boutons +/- 
- **Déplacement** : Clic et glisser
- **Informations** : Clic sur un marqueur pour voir les détails
- **Centrage** : Bouton "Recentrer" pour revenir à la vue d'ensemble

## 🧪 Test des Fonctionnalités

Un fichier de test est disponible à `/test-map.html` pour vérifier :
- ✅ Fonctionnement de l'API
- ✅ Chargement des données
- ✅ Application des filtres
- ✅ Affichage sur la carte

## 🔍 Dépannage

### **Problèmes Courants**

1. **"Une erreur est survenue lors du chargement des données"**
   - Vérifier la connexion à la base de données
   - S'assurer que les routes API sont accessibles
   - Vérifier les permissions d'authentification

2. **Les filtres ne fonctionnent pas**
   - Vérifier que le serveur Laravel est démarré
   - Contrôler la console du navigateur pour les erreurs JavaScript
   - Vérifier que les routes API sont bien définies

3. **La carte ne s'affiche pas**
   - Vérifier la connexion internet (OpenStreetMap)
   - Contrôler que Leaflet.js est bien chargé
   - Vérifier les erreurs dans la console du navigateur

### **Vérifications Système**

1. **Serveur Laravel** : `php artisan serve`
2. **Base de données** : Vérifier la connexion MySQL
3. **Routes API** : Tester `/test-api` et `/api/map/thematic-data`
4. **Authentification** : S'assurer d'être connecté pour les routes protégées

## 📊 Données Disponibles

### **Zones Fiscales**
- 5 zones géographiques définies
- Géométries polygonales pour l'affichage
- Statistiques par zone (nombre d'unités, collecteurs)

### **Unités Commerciales**
- 12 unités recensées
- Coordonnées GPS précises
- Informations détaillées (propriétaire, type, adresse)
- Statut de paiement des taxes

### **Types d'Activités**
- Boutiques et magasins
- Restaurants et cantines
- Salons de coiffure
- Ateliers mécaniques
- Pharmacies
- Stations-service
- Boulangeries
- Épiceries
- Quincailleries
- Cybercafés

## 🎯 Cas d'Usage

### **Pour les Collecteurs**
- Visualiser leur zone de collecte
- Identifier les unités non recensées
- Planifier les tournées de collecte

### **Pour les Administrateurs**
- Analyser la densité commerciale
- Suivre les paiements par zone
- Générer des rapports cartographiques

### **Pour les Décideurs**
- Identifier les zones d'opportunité
- Analyser l'évolution commerciale
- Planifier l'aménagement urbain

## 🔮 Évolutions Futures

- **Couches additionnelles** : Population, routes, bâti
- **Heatmaps** : Visualisation de la densité
- **Analyse temporelle** : Évolution dans le temps
- **Intégration mobile** : Application mobile dédiée
- **API publique** : Accès aux données pour tiers

---

*Documentation mise à jour le : {{ date('d/m/Y') }}*
*Version SICOSA : 1.0*

