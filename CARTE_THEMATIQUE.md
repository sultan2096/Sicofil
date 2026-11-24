# Carte Thématique Géospatiale - SICOSA

## 📋 Vue d'ensemble

La carte thématique géospatiale est une fonctionnalité avancée du système SICOSA qui permet de visualiser spatialement les unités commerciales avec leur statut de paiement. Cette carte interactive offre une vue d'ensemble géographique des données fiscales de la commune de Sandiara.

## 🎯 Fonctionnalités principales

### 1. Visualisation géospatiale
- **Marqueurs colorés** selon le statut de paiement :
  - 🟢 **Vert** : Unités payées (à jour)
  - 🔴 **Rouge** : Unités impayées (en retard)
  - 🟡 **Jaune** : Paiements partiels
  - ⚫ **Gris** : En retard

### 2. Filtres avancés
- **Statut de paiement** : Payé, Impayé, Tous
- **Zone fiscale** : Filtrage par zone géographique
- **Type de commerce** : Filtrage par type d'activité
- **Période** : Filtrage par date de début et fin

### 3. Statistiques en temps réel
- Nombre total d'unités
- Unités payées vs impayées
- Montant total dû
- Répartition par zone

### 4. Fonctionnalités interactives
- **Clustering** : Regroupement automatique des marqueurs proches
- **Popups détaillés** : Informations complètes au clic
- **Modal de détails** : Vue approfondie des données
- **Légende interactive** : Explication des codes couleur

### 5. Export et intégration
- **Export PNG** : Capture d'écran de la carte
- **Export PDF** : Rapport avec carte intégrée
- **Intégration dans les rapports** : Carte incluse dans les rapports PDF

## 🛠️ Architecture technique

### Backend (Laravel)
- **API REST** : `/api/map/thematic-data`
- **Filtrage intelligent** : Par statut, zone, période
- **Calculs automatiques** : Statut de paiement et montants dus
- **Optimisation des requêtes** : Eager loading et indexation

### Frontend (JavaScript)
- **Leaflet.js** : Bibliothèque de cartographie
- **Clustering** : Regroupement des marqueurs
- **Export** : html2canvas + jsPDF
- **Responsive** : Adaptation mobile/desktop

### Base de données
- **Géolocalisation** : Coordonnées lat/lng
- **Relations** : Unités ↔ Paiements ↔ Zones
- **Indexation** : Optimisation des requêtes spatiales

## 📁 Structure des fichiers

```
sicosa-sandiara-main/
├── app/Http/Controllers/
│   ├── MapController.php              # API carte thématique
│   └── RapportController.php          # Rapport avec carte
├── resources/views/
│   ├── rapports/
│   │   ├── carte-thematique.blade.php # Vue carte interactive
│   │   └── rapport-avec-carte.blade.php # Rapport PDF
│   └── layouts/
├── public/css/
│   └── thematic-map.css              # Styles dédiés
└── routes/
    └── web.php                       # Routes API et vues
```

## 🚀 Utilisation

### 1. Accès à la carte interactive
```
URL : /carte-thematique
Rôle requis : Maire, Gestionnaire
```

### 2. Accès au rapport avec carte
```
URL : /rapport-avec-carte
Paramètres : période, zone_id, statut_paiement, format
```

### 3. API de données
```
GET /api/map/thematic-data
Paramètres de filtrage :
- statut: paye|impaye|tous
- zone_id: ID de la zone
- periode_debut: Date de début
- periode_fin: Date de fin
- type_commerce: Type d'activité
- categorie: Catégorie d'unité
```

## 📊 Données affichées

### Informations des unités
- **Propriétaire** : Nom et prénom
- **Commerce** : Nom et type d'activité
- **Localisation** : Adresse et coordonnées GPS
- **Zone fiscale** : Zone d'appartenance
- **Statut fiscal** : Payé/Impayé/Partiel
- **Montant dû** : Calcul automatique
- **Historique** : Nombre de paiements

### Statistiques agrégées
- **Par zone** : Performance territoriale
- **Par type** : Répartition sectorielle
- **Tendances** : Évolution temporelle
- **Indicateurs** : Taux de recouvrement

## 🎨 Personnalisation

### Couleurs des marqueurs
```css
.marker-paye { background-color: #28a745; }    /* Vert */
.marker-impaye { background-color: #dc3545; }  /* Rouge */
.marker-partiel { background-color: #ffc107; } /* Jaune */
.marker-retard { background-color: #6c757d; }  /* Gris */
```

### Configuration du clustering
```javascript
clusterGroup = L.markerClusterGroup({
    chunkedLoading: true,
    maxClusterRadius: 50,
    spiderfyOnMaxZoom: true
});
```

## 🔧 Configuration

### 1. Coordonnées de Sandiara
```javascript
const sandiaraCenter = [14.43540, -16.79153]; // Lat, Lng
```

### 2. Limites de la commune
```javascript
const sandiaraBoundary = [
    [14.44382, -16.80247], // Nord-Ouest
    [14.44382, -16.78059], // Nord-Est
    [14.42699, -16.78059], // Sud-Est
    [14.42699, -16.80247], // Sud-Ouest
];
```

### 3. Zoom par défaut
```javascript
map.setView(sandiaraCenter, 15); // Niveau de zoom 15
```

## 📱 Responsive Design

### Breakpoints
- **Desktop** : > 768px - Carte complète
- **Tablet** : 480px - 768px - Carte réduite
- **Mobile** : < 480px - Carte compacte

### Adaptations
- **Filtres** : Passage en colonne unique
- **Statistiques** : Grille adaptative
- **Popups** : Taille réduite
- **Légende** : Position ajustée

## 🔒 Sécurité

### Authentification
- **Middleware** : Vérification des rôles
- **Autorisations** : Maire et Gestionnaire uniquement
- **Validation** : Sanitisation des paramètres

### Protection des données
- **CSRF** : Protection contre les attaques
- **Validation** : Vérification des entrées
- **Escape** : Protection XSS

## 🚀 Performance

### Optimisations
- **Lazy loading** : Chargement à la demande
- **Clustering** : Réduction du nombre de marqueurs
- **Cache** : Mise en cache des données
- **Compression** : Minification CSS/JS

### Monitoring
- **Console logs** : Debug et suivi
- **Error handling** : Gestion des erreurs
- **Loading states** : Indicateurs de chargement

## 🔄 Maintenance

### Mises à jour
- **Leaflet.js** : Version 1.9.4
- **Dépendances** : Mise à jour régulière
- **Sécurité** : Patches de sécurité

### Sauvegarde
- **Données** : Sauvegarde automatique
- **Configuration** : Versioning des paramètres
- **Logs** : Historique des accès

## 📞 Support

### Documentation
- **Code** : Commentaires détaillés
- **API** : Documentation Swagger
- **Utilisateur** : Guide d'utilisation

### Contact
- **Développeur** : Équipe SICOSA
- **Email** : support@sicosa.sn
- **Téléphone** : +221 XX XXX XX XX

---

**Version** : 1.0.0  
**Dernière mise à jour** : {{ date('d/m/Y') }}  
**Auteur** : Équipe SICOSA 