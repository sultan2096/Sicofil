# Architecture du Projet SICOSA

## Vue d'ensemble

SICOSA (Système d'Information Commerciale de Sandiara pour la Fiscalité Locale) est une application web développée avec Laravel 10 et PostgreSQL pour gérer la fiscalité locale des unités commerciales.

## Structure des dossiers

```
sicosa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php     # Gestion de l'authentification
│   │   │   ├── DashboardController.php     # Tableaux de bord par rôle
│   │   │   ├── UserController.php          # Gestion des utilisateurs
│   │   │   ├── PaiementController.php      # Gestion des paiements
│   │   │   ├── UniteCommercialeController.php # Gestion des unités
│   │   │   ├── ZoneFiscaleController.php   # Gestion des zones
│   │   │   ├── TaxeController.php          # Gestion des taxes
│   │   │   ├── LogController.php           # Consultation des logs
│   │   │   └── MapController.php           # Carte interactive
│   │   └── Middleware/
│   │       └── CheckRole.php               # Vérification des rôles
│   └── Models/
│       ├── User.php                        # Modèle utilisateur
│       ├── ZoneFiscale.php                 # Modèle zone fiscale
│       ├── Taxe.php                        # Modèle taxe
│       ├── UniteCommerciale.php            # Modèle unité commerciale
│       ├── Paiement.php                    # Modèle paiement
│       └── Log.php                         # Modèle log
├── database/
│   ├── migrations/                         # Migrations de la base de données
│   └── seeders/                            # Seeders pour les données initiales
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php               # Layout principal
│       ├── auth/
│       │   └── login.blade.php             # Page de connexion
│       ├── maire/
│       │   └── dashboard.blade.php         # Dashboard du maire
│       ├── admin/
│       │   └── dashboard.blade.php         # Dashboard de l'admin
│       ├── collecteur/
│       │   └── dashboard.blade.php         # Dashboard du collecteur
│       ├── paiements/
│       │   └── create.blade.php            # Formulaire de paiement
│       └── map/
│           └── index.blade.php             # Carte interactive
└── routes/
    └── web.php                             # Définition des routes
```

## Base de données

### Tables principales

1. **users**
   - Utilisateurs du système (maire, admin, collecteur)
   - Authentification par identifiant/mot de passe
   - Association avec une zone fiscale pour les collecteurs

2. **zones_fiscales**
   - Délimitation géographique des zones de collecte
   - Stockage de la géométrie (polygones) en JSON
   - Relation avec les collecteurs et unités commerciales

3. **taxes**
   - Configuration des différents types de taxes
   - Périodicité : journalière, mensuelle, annuelle, non périodique
   - Nature : produits domaniaux, taxes municipales, produits d'exploitation

4. **unites_commerciales**
   - Recensement des commerces et points d'intérêt
   - Géolocalisation (latitude/longitude)
   - Catégories : activité commerciale, point d'intérêt
   - Association avec zone fiscale et taxe applicable

5. **paiements**
   - Enregistrement des collectes de taxes
   - Modes de paiement : cash, Wave, Orange Money
   - Traçabilité : collecteur, date, montant, référence

6. **logs**
   - Journal de toutes les actions sensibles
   - Traçabilité complète des opérations
   - Restrictions d'accès selon les rôles

## Rôles et permissions

### Maire (maire2025)
- Accès complet à toutes les fonctionnalités
- Gestion de tous les utilisateurs (y compris les admins)
- Visualisation de tous les logs
- Tous les droits d'administration

### Administrateur (admin01)
- Gestion des collecteurs uniquement
- Administration des zones, taxes et unités commerciales
- Accès aux rapports et statistiques
- Logs visibles sauf ceux du maire

### Collecteur (collecteur01-05)
- Accès limité à sa zone assignée
- Enregistrement des paiements
- Consultation des unités de sa zone
- Export de ses propres collectes

## Fonctionnalités clés

### 1. Authentification
- Connexion par identifiant (pas d'email)
- Redirection automatique selon le rôle
- Logs de connexion/déconnexion

### 2. Gestion des zones fiscales
- Création et délimitation des zones
- Visualisation sur carte interactive
- Attribution des collecteurs par zone

### 3. Gestion des unités commerciales
- Recensement avec géolocalisation
- Classification par type et nature
- Association avec les taxes applicables

### 4. Collecte des paiements
- Interface mobile-friendly pour terrain
- Support multi-modes de paiement
- Calcul automatique selon la taxe

### 5. Cartographie interactive
- Visualisation des zones et unités sur Leaflet
- Filtrage par zone, catégorie, recherche
- Popups informatifs sur chaque marqueur

### 6. Rapports et exports
- Export CSV des données
- Statistiques par zone et période
- Tableaux de bord personnalisés

## Technologies utilisées

- **Backend** : Laravel 10.x (PHP 8.1+)
- **Base de données** : PostgreSQL 12+
- **Frontend** : Blade, Bootstrap 5, Font Awesome
- **Cartographie** : Leaflet.js
- **Authentification** : Laravel native (sans Breeze/Jetstream)

## Sécurité

- Middleware `CheckRole` pour contrôle d'accès
- Protection CSRF sur tous les formulaires
- Logs automatiques des actions sensibles
- Hiérarchie stricte des permissions
- Validation côté serveur de toutes les entrées

## Installation

1. Cloner le projet
2. Installer les dépendances : `composer install`
3. Configurer `.env` avec PostgreSQL
4. Créer la base de données : `sicosa_db`
5. Lancer les migrations : `php artisan migrate --seed`
6. Démarrer le serveur : `php artisan serve`

## Prochaines étapes

### Vues à créer
- Listes et formulaires CRUD pour chaque module
- Pages de détail pour chaque entité
- Tableaux de statistiques avancées

### Fonctionnalités à implémenter
- API REST pour application mobile
- Notifications en temps réel
- Graphiques et visualisations avancées
- Système de reçus/factures
- Sauvegarde automatique des données

### Optimisations
- Cache pour les requêtes fréquentes
- Pagination côté serveur
- Compression des assets
- Tests unitaires et d'intégration

## Maintenance

- Logs rotatifs dans `storage/logs`
- Commande de nettoyage des anciens logs
- Backup régulier de la base de données
- Monitoring des performances 