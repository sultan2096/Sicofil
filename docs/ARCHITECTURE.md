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
├── docs/                                   # Documentation du projet
└── routes/
    └── web.php                             # Définition des routes
```

## Base de données

### Tables principales

1. **users** – Utilisateurs (maire, admin, collecteur), authentification, zone pour collecteurs
2. **zones_fiscales** – Zones de collecte, géométrie (PostGIS)
3. **taxes** – Types de taxes, périodicité, nature
4. **unites_commerciales** – Recensement, géolocalisation, zone, taxe
5. **paiements** – Collectes, modes de paiement, traçabilité
6. **logs** – Journal des actions sensibles

## Rôles et permissions

- **Maire** : Accès complet, gestion utilisateurs, logs, rapports
- **Gestionnaire** : Collecteurs, zones, unités, paiements, alertes
- **Collecteur** : Zone assignée, paiements, unités de sa zone

## Technologies

- **Backend** : Laravel 10.x (PHP 8.1+)
- **Base de données** : PostgreSQL / PostGIS
- **Frontend** : Blade, Bootstrap 5, TailwindCSS, Leaflet.js

## Installation

1. `composer install`
2. Configurer `.env` (PostgreSQL)
3. `php artisan migrate --seed`
4. `php artisan serve`
