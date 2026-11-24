# 🏢 SICOFIL - Système d'Information Communal pour la Fiscalité Locale

## 📋 Description

**SICOFIL** est une plateforme SaaS nationale multi-tenant développée en Laravel pour la gestion fiscale locale des communes du Sénégal. Initialement développé comme SICOSA pour la commune de Sandiara, le système a évolué vers une plateforme nationale capable de gérer plus de 500 communes avec isolation complète des données.

### 🌍 Plateforme Nationale Multi-Communes
- ✅ Architecture multi-tenant avec isolation des données
- ✅ Gestion centralisée SICOFIL pour toutes les communes
- ✅ Abonnements SaaS et facturation automatique
- ✅ Portail public pour télépaiement (contribuables)
- ✅ Intégration Mobile Money (Wave, Orange Money, Free Money)
- ✅ Notifications SMS automatiques
- ✅ Laravel Horizon pour jobs asynchrones

## ✨ Fonctionnalités principales

### 👑 **Interface Maire**
- Tableau de bord complet avec statistiques
- Gestion des utilisateurs et rôles
- Configuration des zones fiscales
- Gestion des taxes et tarifs
- Rapports et analyses détaillés
- Suivi des paiements et recouvrements

### 👨‍💼 **Interface Gestionnaire**
- Supervision des collecteurs
- Gestion des zones et unités
- Suivi des paiements
- Alertes de retard de paiement
- Rapports de performance

### 👨‍💻 **Interface Collecteur (Optimisée Tablette)**
- Recensement d'unités commerciales
- Géolocalisation GPS intégrée
- Capture de photos
- Enregistrement des paiements
- Interface tactile responsive

## 🚀 Technologies utilisées

- **Backend** : Laravel 10.x
- **Frontend** : Bootstrap 5, HTML5, CSS3, JavaScript
- **Base de données** : MySQL/MariaDB
- **Géolocalisation** : Leaflet.js, OpenStreetMap
- **Cartographie** : Interactive maps avec marqueurs
- **Responsive Design** : Mobile-first, tablette, desktop

## 📱 Responsive Design

La plateforme est entièrement responsive et optimisée pour :
- 📱 **Mobile** (< 768px) : Interface tactile optimisée
- 📱 **Tablette** (768-1024px) : Interface hybride
- 💻 **Desktop** (> 1024px) : Interface complète

## 🔧 Installation

### Prérequis
- PHP 8.1+
- Composer
- MySQL/MariaDB
- XAMPP/WAMP/MAMP

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/sultan2096/Projet_sicosa.git
cd Projet_sicosa
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sicosa_db
DB_USERNAME=root
DB_PASSWORD=
```

5. **Créer la base de données**
```sql
CREATE DATABASE sicosa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. **Exécuter les migrations et seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Démarrer le serveur**
```bash
php artisan serve --host=localhost --port=8000
```

## 🔑 Identifiants de connexion

### 👑 **Maire**
- **Identifiant** : `maire2025`
- **Mot de passe** : `sicosa2025`
- **URL** : `http://localhost:8000/maire/login`

### 👨‍💼 **Gestionnaire**
- **Identifiant** : `gestionnaire01`
- **Mot de passe** : `gestionnaire2025`
- **URL** : `http://localhost:8000/gestionnaire/login`

### 👨‍💻 **Collecteurs**
- **Identifiant** : `collecteur01` à `collecteur05`
- **Mot de passe** : `collecteur2025`
- **URL** : `http://localhost:8000/collecteur/login`

## 🗺️ Fonctionnalités de géolocalisation

- **Capture GPS** : Géolocalisation précise des unités
- **Carte interactive** : Visualisation sur OpenStreetMap
- **Marqueurs draggables** : Ajustement manuel des positions
- **Photos géolocalisées** : Capture d'images avec coordonnées
- **Zones fiscales** : Délimitation géographique des secteurs

## 📊 Modules principaux

### 🏪 **Recensement**
- Formulaire intelligent avec filtres
- Géolocalisation automatique
- Capture de photos
- Validation en temps réel

### 💰 **Paiements**
- Enregistrement des transactions
- Calcul automatique des taxes
- Historique des paiements
- Alertes de retard

### 📈 **Rapports**
- Statistiques détaillées
- Graphiques interactifs
- Export PDF/Excel
- Analyses par zone

### 🗺️ **Cartographie**
- Carte interactive des unités
- Filtrage par zone/type
- Photos géolocalisées
- Navigation intuitive

## 🔒 Sécurité

- **Authentification** : Système de connexion sécurisé
- **Autorisation** : Gestion des rôles et permissions
- **Validation** : Contrôles de saisie stricts
- **Logs** : Traçabilité des actions
- **CSRF Protection** : Protection contre les attaques

## 📱 Optimisations mobiles

- **Interface tactile** : Boutons et champs optimisés
- **Géolocalisation** : GPS intégré pour les collecteurs
- **Performance** : Chargement rapide sur mobile
- **Accessibilité** : Navigation intuitive
- **Responsive** : Adaptation automatique à l'écran

## 🚀 Déploiement

### Production
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Variables d'environnement
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com
```

## 📚 Documentation

- **Documentation technique** : voir le dossier [`docs/`](docs/) (architecture, configuration PostgreSQL, carte thématique, géomarketing, guides utilisateur, résolution d'erreurs, responsive).
- Les fichiers `.md` à la racine (ARCHITECTURE.md, CONFIGURATION_POSTGRESQL.md, etc.) peuvent être déplacés dans `docs/` pour une arborescence plus propre.

## 📞 Support

Pour toute question ou support technique :
- **Email** : support@sicosa.sn
- **Téléphone** : +221 XX XXX XX XX
- **Documentation** : [Wiki du projet](https://github.com/sultan2096/Projet_sicosa/wiki)

## 📄 Licence

Ce projet est développé pour la commune de Sandiara. Tous droits réservés.

---

**Développé avec ❤️ pour la commune de Sandiara**
