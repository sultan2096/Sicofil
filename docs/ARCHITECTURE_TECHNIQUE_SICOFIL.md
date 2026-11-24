# Architecture Technique de SICOFIL
## Système d'Information Commerciale pour la Fiscalité Locale

---

## Schéma global cible (architecture de déploiement)

```
                    HTTPS / TLS 1.3
                            │
    ┌───────────────────────┴───────────────────────┐
    │   SERVEUR PHYSIQUE A – PRODUCTION              │
    │                                                 │
    │   API CENTRALE SICOFIL                          │
    │   • Laravel 11                                 │
    │   • Auth OAuth2 / RBAC                          │
    │   • Logique fiscale & paiements                 │
    │   • Multi-tenant (10 → N communes)              │
    │   • Webhooks Mobile Money                       │
    │   • APIs SIG & Reporting                        │
    │            │                                    │
    │   BASE DE DONNÉES FISCALE                       │
    │   • PostgreSQL 16 + PostGIS                    │
    │   • UC, Taxes, Zones fiscales SIG, Paiements    │
    │                                                 │
    │   STOCKAGE OBJETS (MinIO)                       │
    │   • Photos UC (preuves), Reçus PDF, Backups     │
    │                                                 │
    │   SIG SERVEUR (GeoServer)                       │
    │   • WMS / WFS, Vector Tiles                     │
    │   • Intégration QGIS & Web                      │
    └───────────────────────┬───────────────────────┘
                            │
              Réplication temps réel / planifiée
                            │
    ┌───────────────────────┴───────────────────────┐
    │   SERVEUR PHYSIQUE B – RÉPLICA                  │
    │   • Réplication PostgreSQL (Streaming)         │
    │   • Réplication MinIO                          │
    │   • Sauvegardes chiffrées AES-256               │
    │   • PRA &lt; 4 h                                 │
    └───────────────────────────────────────────────┘
```

---

## État actuel vs cible

| Domaine | État actuel (projet SICOSA/SICOFIL) | Cible (document SICOFIL) |
|--------|--------------------------------------|---------------------------|
| **Stack** | ✅ PHP, Laravel 10.x, PostgreSQL + PostGIS, HTML5/CSS3, Bootstrap 5, Leaflet, OpenStreetMap, html2canvas, jsPDF, Composer | Aligné |
| **Interfaces** | ✅ Collecteur, Gestionnaire, Maire (objectifs et modules conformes au tableau) | Aligné |
| **Multi-communes** | ✅ Table `communes`, `commune_id` sur users/zones/UC/paiements/taxes/logs ; rôle **Admin SICOFIL** ; interface **Gestion multi-communes** (tableau de bord unique, ajout/édition communes, vue agrégée). Scoping Maire/Gestionnaire/Collecteur par commune à finaliser. | Multi-tenant 10 → 500 communes, isolation données |
| **Auth** | ✅ Connexion identifiant, RBAC, logs d’audit | + OAuth2 (Passport), MFA (OTP/SMS) |
| **SIG** | ✅ PostGIS, Leaflet, zones, UC, géolocalisation recensement & paiement | + GeoServer (WMS/WFS), Vector Tiles, QGIS |
| **Infrastructure** | Serveur web (Apache/Nginx), pas de Docker/Redis/MinIO/GeoServer en place | Serveur A (Laravel, DB, Redis, GeoServer), MinIO, Serveur B (réplication, backups, PRA &lt; 4 h) |
| **Paiements** | ✅ Enregistrement, partiels, GPS, modes Cash/Wave/OM (UI) | + Webhooks Mobile Money, reçus PDF, notifications SMS/WhatsApp |
| **Temps réel** | ✅ API tracking collecteurs, page suivi | + WebSockets (Laravel Reverb + Redis), dashboard temps réel |
| **Industrialisation** | Non | Docker, CI/CD, monitoring (Grafana), SLA |

**Résumé :** Le cœur métier (recensement, taxes, paiements, cartographie, rapports, rôles) et la base multi-communes (communes + Admin SICOFIL) sont en place. Les prochaines évolutions (OAuth2, MFA, GeoServer, MinIO, Docker, WebSockets, Webhooks Mobile Money, CI/CD, portail contribuable, etc.) sont à réaliser selon le plan en 4 sprints (6 mois) décrit plus bas.

---

## Stack Technologique Actuelle

### Backend (serveur)
| Composant | Technologie |
|-----------|-------------|
| Langage | PHP 8.1+ |
| Framework | Laravel 10.x |
| Base de données | PostgreSQL 12+ avec extension PostGIS |
| Géolocalisation | PostGIS (données géospatiales) |

### Frontend (interface utilisateur)
| Composant | Technologie |
|-----------|-------------|
| Structure | HTML5 |
| Styles | CSS3 (Bootstrap 5) |
| Interactivité | JavaScript, cartographie |

### Bibliothèques
- **Leaflet.js** — cartes interactives
- **OpenStreetMap** — tuiles cartographiques
- **html2canvas** — export d'images
- **jsPDF** — export PDF

### Infrastructure
- Serveur web : Apache / Nginx
- Gestion des dépendances : Composer (PHP)

---

## Niveaux de réalisation — Tableau des Fonctionnalités Actuelles

### Interface Collecteur (Mobile & Terrain)
**Objectif :** Recensement et collecte des taxes sur le terrain

| Modules & Fonctionnalités | Outils SIG & Traçabilité | Indicateurs & Analyse |
|---------------------------|--------------------------|------------------------|
| Formulaire de recensement des UC | Géolocalisation obligatoire recensement & paiement | Tableau de bord : UC zone, FCFA jour/mois, UC non payées |
| Ajout paiements, sélection UC & taxe, calcul auto | Carte interactive validation position | — |
| Gestion avances & paiements partiels | — | — |
| Modes : Cash / Wave / Orange Money | — | — |
| Historique paiements, « Mes Unités » (zone assignée) | — | — |

### Interface Gestionnaire (Administration & Opérations)
**Objectif :** Supervision des collecteurs & gestion des références

| Modules & Fonctionnalités | Outils SIG & Traçabilité | Indicateurs & Analyse |
|---------------------------|--------------------------|------------------------|
| Gestion Taxes, Zones, Collecteurs, UC & Paiements | Carte zones & UC, filtres avancés | Dashboard : UC actives, recensements, paiements du mois |
| Activités récentes : Paiements (7j), Recensements (30j) | Tracking temps réel collecteurs | Collecteurs actifs, répartition paiements par zone |
| Administration données stratégiques | Journal logs (actions, localisation, horodatage) | — |

### Interface Maire (Décisionnel & Stratégique)
**Objectif :** Pilotage de la fiscalité et aide à la décision

| Modules & Fonctionnalités | Outils SIG & Traçabilité | Indicateurs & Analyse |
|---------------------------|--------------------------|------------------------|
| Consultation zones, taxes, UC & utilisateurs | Cartographie complète (zones, UC) | Recettes jour / mois |
| 11 types de rapports décisionnels | Tracking collecteurs, logs | UC en règle / retard / impayées |
| — | — | Graphiques : performance collecteurs, évolution recettes, répartition |

---

## Tableau des Fonctionnalités pour 10 communes et Niveau de Réalisation (N-R)

| Module | Fonctionnalité | N-R | Statut | Notes |
|--------|----------------|-----|--------|-------|
| **Authentification & Sécurité** | | | | |
| | Connexion par identifiant | 100% | Réalisé | Auth Laravel native |
| | Gestion des rôles (Admin / Maire / Gestionnaire / Collecteur / Contribuable) | 50% | Réalisé | RBAC par middleware |
| | Logs d'audit | 100% | Réalisé | |
| | OAuth2 (Laravel Passport) | 0% | À faire | Tokens API, multi-communes |
| | MFA (OTP/SMS) | 0% | À faire | Sécurité institutionnelle |
| **Gestion des Utilisateurs** | | | | |
| | CRUD Utilisateurs (Maire) | 100% | Réalisé | UserController |
| | Attribution zones aux collecteurs | 100% | Réalisé | User ↔ ZoneFiscale |
| | Gestion multi-communes | ~40% | Partiel | Table communes, commune_id, rôle Admin SICOFIL, interface Gestion multi-communes (dashboard, CRUD communes). Scoping complet et paramétrage par commune à finaliser. |
| **Zones Fiscales** | | | | |
| | Création zones fiscales | 50% | Partiel | PostGIS + Leaflet (Sandiara) |
| | Visualisation cartographique | 100% | Réalisé | Carte interactive |
| | Import Shapefiles | 0% | À faire | SRID 4326 |
| | Attribution multi-collecteurs | 50% | Partiel | À finaliser |
| | Zones multi-communes | 0% | À faire | SIG intercommunal |
| **Recensement UC** | | | | |
| | Formulaire recensement | 100% | Réalisé | UniteCommercialeController |
| | Géolocalisation GPS | 100% | Réalisé | Leaflet + GPS navigateur |
| | Capture photos | 60% | Partiel | MinIO à externaliser |
| | Classification (Type/Nature) | 90% | Partiel | Dictionnaire technique |
| **Gestion des Taxes** | | | | |
| | CRUD Taxes | 100% | Réalisé | TaxeController |
| | Grilles tarifaires | 100% | Réalisé | Calcul automatique |
| | Taxes dynamiques par commune | 50% | Partiel | Paramétrage multi-communes |
| | Historique des tarifs | 30% | Partiel | Versioning incomplet |
| **Paiements** | | | | |
| | Enregistrement paiements | 100% | Réalisé | PaiementController |
| | Paiements partiels | 100% | Réalisé | Finalisation progressive |
| | Géolocalisation paiements | 100% | Réalisé | GPS obligatoire |
| | Modes (Cash/Wave/OM) | 50% | Partiel | UI prête, webhooks manquants |
| | Webhooks Mobile Money | 0% | À faire | Validation opérateur |
| | Télépaiement sécurisé | 0% | À faire | |
| | Reçus numériques | 30% | Partiel | PDF basique |
| | Notifications SMS/WhatsApp | 0% | À faire | Passerelle |
| **Cartographie & SIG** | | | | |
| | Carte interactive Leaflet | 100% | Réalisé | MapController |
| | Cartes thématiques | 100% | Réalisé | Filtres statut |
| | Visualisation zones | 100% | Réalisé | Polygones PostGIS |
| | Marqueurs UC | 100% | Réalisé | Clustering |
| | Export carte (PNG/PDF) | 100% | Réalisé | html2canvas + jsPDF |
| | GeoServer (WMS/WFS) | 0% | À faire | SIG intercommunal |
| | Vector Tiles | 0% | À faire | Performance 10 communes |
| | Intégration QGIS | 30% | Partiel | Administration SIG |
| **Tracking & Temps Réel** | | | | |
| | Tracking GPS collecteurs | 50% | Partiel | API existante, WebSockets manquants |
| | WebSockets (Laravel Reverb) | 0% | À faire | Suivi temps réel |
| | Dashboard temps réel Maire | 0% | À faire | Supervision terrain |
| | Historique itinéraires | 30% | Partiel | Visualisation à améliorer |
| **Rapports & Statistiques** | | | | |
| | Dashboard Maire / Gestionnaire | 100% | Réalisé | Vue décisionnelle / superviseur |
| | Rapports financiers | 100% | Réalisé | 11 rapports officiels |
| | Export PDF/Excel | 100% | Réalisé | DomPDF + Laravel-Excel |
| | Graphiques statistiques | 100% | Réalisé | Chart.js |
| | Analytics avancées | 70% | Partiel | Analyse spatiale |
| **Analyse Spatiale** | | | | |
| | Analyse zones | 100% | Réalisé | Multi-critères |
| | Heatmap densité | 100% | Réalisé | Population / UC |
| | Analyse multi-critères | 50% | Partiel | Routes / Bâti / Population |
| **Administration SICOFIL – Exécutif** | | | | |
| | Nombre de communes clientes | 0% | À faire | Instances à activer |
| | Activité globale collecteurs (intercommunal) | 0% | À faire | |
| | Volume global transactions | 0% | À faire | Jour / Mois / Année |
| | Recettes globales (FCFA) | 0% | À faire | Base redevance SaaS |
| **Administration SICOFIL – Multi-Tenant & Technique** | | | | |
| | Création / paramétrage communes | 0% | À faire | Instance logique |
| | Isolation des données (commune_id) | 0% | À faire | |
| | Déploiement centralisé | 0% | À faire | Docker / CI-CD |
| | Backups & restauration | 0% | À faire | Sauvegardes |
| | Monitoring performance & sécurité | 0% | À faire | SLA |
| **Administration SICOFIL – SaaS & Support** | | | | |
| | Gestion abonnements | 0% | À faire | Statut commune client |
| | Facturation SaaS hybride | 0% | À faire | Fixe + % (annuelle) |
| | Reporting commercial global | 0% | À faire | MRR, croissance |
| | Ticketing support | 0% | À faire | Support clients |
| | Journal d'audit central SICOFIL | 0% | À faire | Traçabilité |
| **Portail Contribuable** | | | | |
| | Accès compte contribuable | 0% | À faire | Téléphone / UC |
| | Consultation taxes dues | 0% | À faire | Par période |
| | Paiement en ligne | 0% | À faire | Wave / OM |
| | Historique paiements | 0% | À faire | Vue chronologique |
| | Téléchargement factures PDF | 0% | À faire | |
| | Contact mairie | 0% | À faire | Réclamations |
| | Notifications fiscales | 0% | À faire | Rappels |
| **Infrastructure** | | | | |
| | Laravel Backend | 100% | Réalisé | Laravel 10/11 |
| | PostgreSQL + PostGIS | 100% | Réalisé | Base spatiale |
| | Docker/Containerisation | 0% | À faire | Déploiement |
| | Redis Cache | 0% | À faire | Performance |
| | Laravel Horizon | 0% | À faire | Files & webhooks |
| | MinIO (Stockage objets) | 0% | À faire | Photos/pièces |
| | Monitoring (Grafana) | 0% | À faire | Surveillance |
| | CI/CD (GitHub Actions) | 0% | À faire | Industrialisation |

**Niveau global actuel : ~65 %**  
Le reste (35 %) correspond à l’industrialisation SICOFIL (multi-tenant, SaaS, sécurité avancée), au portail contribuable, à l’infrastructure (Docker, CI/CD, monitoring) et à l’administration centrale intercommunale. La phase test à Sandiara prouve que le cœur du système fonctionne déjà dans une commune ; SICOFIL consiste à en faire une plateforme nationale, sécurisée, multi-communes et économiquement viable.

---

## Matériels, Capacité & Infrastructure (10 Communes)

| Composant | Quantité | Spécifications & Justification |
|-----------|----------|--------------------------------|
| Tablettes de collecte | 5–10 | Android, GPS, 4G |
| Stockage objets | Inclus | MinIO 2×5 To |
| Serveur physique A (Production) | 1 | 24 vCPU, 128 Go RAM, 6 To NVMe, UPS |
| Serveur physique B (Réplica) | 1 | Identique + réplication MinIO |
| Postes Windows Administration & SIG | 2 | 64 Go RAM, SSD 2 To, GPU dédié (SIG/Carto) |
| Réseau inter-serveurs | — | Carte 10 Gbps Ethernet (RJ45) |

---

## Sécurité & Conformité

| Domaine / Risque | Mécanismes (Laravel / Flutter) | Protection & Impact Métier |
|------------------|--------------------------------|----------------------------|
| Accès / Usurpation | OAuth2, MFA, Device Fingerprinting, sessions | Accès réservé aux agents autorisés |
| Fraude encaissements | Géolocalisation obligatoire, horodatage serveur, webhooks Wave/OM | Transaction validée par opérateur, lieu réel |
| Intrusion / cyberattaques | WAF (Cloudflare), TLS 1.3, Fail2Ban (Docker) | Protection back-office, SLA |
| Corruption données | Logs d’audit, historisation, versioning données fiscales | Transparence, preuve juridique |
| Perte de données | Backups chiffrés AES-256, réplication A→B, PRA/DRP | Reprise < 4 h |
| RGPD / CDP Sénégal | Chiffrement au repos, masquage, RBAC | Conformité, protection contribuables |
| API & abus | Rate Limiting, JWT, IP Whitelisting admin | Limitation scraping et accès critiques |
| Conformité & gouvernance | Journal d’audit, séparation des rôles, archivage légal | Crédibilité, audits étatiques |

---

## Architecture des APIs & Performance

| Type d'API | Approche SICOFIL | Rôle métier |
|------------|------------------|-------------|
| API Financière | Mobile Money (Wave, OM, Free) + Webhooks, Laravel Horizon | Validation automatique, zéro fraude, confirmation instantanée |
| API Cartographique & SIG | OGC WMS/WFS, Vector Tiles (GeoServer) | Cadastre économique dynamique, QGIS, performance 10 communes |
| API Temps réel | WebSockets (Laravel Reverb + Redis) | Tracking GPS, alertes, dashboard Maire en direct |
| API Notifications | Passerelle SMS & WhatsApp | Reçu numérique, rappels fiscaux, transparence |
| API Données fiscales | Taxes, tarifs & règles dynamiques par commune | Autonomie locale, cadre intercommunal |
| API Reporting | PDF & Excel (DomPDF / Laravel-Excel) | 11 rapports officiels, signature, conformité, audits |
| API Portail contribuable | Consultation & paiement en ligne sécurisé | Paiement sans déplacement, recettes |
| API Admin SICOFIL (SaaS) | Multi-tenant & monitoring centralisé | Supervision communes, déploiement centralisé |

---

## Schéma global de l’architecture

```
[ Clients / Collecteurs / Mairies ]
            |
     HTTPS / TLS 1.3
            |
[ SERVEUR PHYSIQUE A – PRODUCTION ]
  • API Laravel
  • PostgreSQL + PostGIS
  • Redis / GeoServer / MinIO
  • WAF / TLS
            |
  Réplication temps réel / planifiée
            |
[ SERVEUR PHYSIQUE B – RÉPLICA ]
  • Même stack + réplication MinIO
```

---

## Méthodologie de Développement — Plan Intensif (6 mois)

| Phase / Sprint | Focus | Tâches critiques | % | Livrable |
|----------------|--------|------------------|---|----------|
| **Sprint 1 (M1–M2)** | Infrastructure & API Core | Docker (API, DB, Redis, GeoServer), multi-tenant (10 communes), OAuth2 (Passport), modèle fiscal intercommunal | 60% | Noyau Backend + API multi-communes |
| **Sprint 2 (M3–M4)** | SIG & Application terrain | PostGIS ↔ GeoServer (WMS/WFS), Mobile Web recensement UC + GPS + photos, sync terrain ↔ serveur, UC/zones multi-communes | 80% | Interface mobile recensement + SIG fiscal intercommunal |
| **Sprint 3 (M5)** | Paiement, supervision & décisionnel | Mobile Money (Wave, OM, Free) Webhooks, Dashboard Maire/Gestionnaire, tracking GPS temps réel (Reverb + Redis), reçus PDF | 90% | SICOFIL pilotable, paiement certifié et traçable |
| **Sprint 4 (M6)** | Industrialisation & tests de charge | Prod + Réplica, CI/CD, sauvegardes, monitoring (Grafana), stress tests multi-communes, optimisation | 100% | Plateforme prête déploiement national, SLA validés |

---

## Dictionnaire des Données — Concepts Fondamentaux

| Entité | Définition | Notes / Règles |
|--------|------------|----------------|
| **Unités commerciales** | Entité économique recensée (activité ou point d’intérêt commercial) assujettie à une taxe locale | Rattachée à une zone fiscale et une commune. Suivi multi-communal, analyse par zone et type de taxe. |
| **Activité commerciale** | Entité générant des revenus, taxation périodique | Boutiques, restaurants, salons, ateliers, pharmacies, stations, épiceries, cybercafés, boulangeries, etc. |
| **Point d’intérêt commercial** | Entité non directement commerciale mais génératrice de revenus | Panneaux publicitaires, enseignes, locations terrains |
| **Zone fiscale** | Découpage administratif pour collecte et planification | Contient plusieurs UC. Assignée à un ou plusieurs collecteurs. |
| **Taxe locale** | Contribution fiscale applicable aux UC | Par commune ou uniforme. Calcul automatique des montants. |
| **Paiement fiscal** | Enregistrement d’une transaction par un collecteur | Traçabilité multi-communes, avances, paiements partiels. |
| **Collecteur fiscal** | Agent recensement et collecte dans sa zone | Accès limité à sa zone/commune. Voit uniquement ses UC et paiements. |
| **Gestionnaire** | Superviseur collecteurs et données fiscales | Toutes zones/communes assignées. Ne gère pas les autres gestionnaires. |
| **Maire / Conseil municipal** | Autorité décisionnelle, accès complet | Supervision, rapports, géomarketing, conformité. |
| **Journal des actions / Logs** | Enregistrement des actions et événements | Audit, sécurité, traçabilité. |
| **Rapport fiscal** | Synthèse recettes, activités, paiements par commune/zone/collecteur | Généré automatiquement ou export PDF/Excel. |

---

*Document de référence SICOFIL — Système d’Information Commerciale pour la Fiscalité Locale. Projet actuel (Sandiara) : SICOSA.*
