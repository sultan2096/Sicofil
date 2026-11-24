# DOCUMENT D'ARCHITECTURE TECHNIQUE (DAT)
## SICOFIL / SICOSA — Plateforme de Gestion Fiscale Locale

**Version :** 1.0  |  **Date :** Juin 2026  |  **Statut :** Pré-production

---

## TABLE DES MATIÈRES

1. [Schéma de contexte](#1-schéma-de-contexte)
2. [Architecture d'infrastructure](#2-architecture-dinfrastructure)
3. [Composants applicatifs](#3-composants-applicatifs)
4. [Flux de données](#4-flux-de-données)
5. [Séquence authentification](#5-séquence-authentification)
6. [Séquence paiement](#6-séquence-paiement)
7. [Architecture en couches](#7-architecture-en-couches)
8. [Schéma réseau & Pare-feu](#8-schéma-réseau--pare-feu)
9. [Matrice technologies](#9-matrice-technologies)
10. [Gouvernance RBAC](#10-gouvernance-rbac)
11. [Matrice sécurité](#11-matrice-sécurité)
12. [Sauvegarde & PRA](#12-sauvegarde--pra)
13. [Roadmap](#13-roadmap)
14. [Glossaire](#14-glossaire)

---

## 1. SCHÉMA DE CONTEXTE

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           UTILISATEURS EXTERNES                              │
├─────────────┬─────────────┬─────────────┬─────────────┬───────────────────┤
│ CONTRIBUABLE│ COLLECTEUR  │ GESTIONNAIRE│    MAIRE    │  ADMIN SICOFIL    │
│(Portail Web)│ (Mobile/Web)│  (Dashboard)│(Dashboard  │  (SaaS Ops)       │
│             │             │             │  stratégique)│                   │
└──────┬──────┴──────┬──────┴──────┬──────┴──────┬──────┴─────────┬─────────┘
       │             │             │             │                │
       └─────────────┴─────────────┴─────────────┘                │
                         │                                       │
                         ▼                                       │
       ┌─────────────────────────────────────────┐                 │
       │     SICOFIL — APPLICATION MONOLITHE    │◄────────────────┘
       │         Laravel 10 / PHP 8.1+            │   (Scope multi-communes)
       │         Blade + Vanilla JS               │
       │         Leaflet + Chart.js               │
       └────────────────────┬────────────────────┘
                            │
           ┌────────────────┼────────────────┐
           │                │                │
           ▼                ▼                ▼
    ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
    │ PostgreSQL   │ │    Redis     │ │  MinIO / S3  │
    │ + PostGIS    │ │  (Cache/WS)  │ │  (Fichiers)  │
    └──────────────┘ └──────────────┘ └──────────────┘
           │                │                │
           └────────────────┼────────────────┘
                            │
                            ▼
       ┌─────────────────────────────────────────┐
       │         APIS EXTERNES                    │
       │  Wave API │ Orange Money │ SMS Gateway   │
       └─────────────────────────────────────────┘
```

---

## 2. ARCHITECTURE D'INFRASTRUCTURE

### 2.1. Déploiement cible — Cluster 3 tiers

```
                                    INTERNET
                                       │
                                       ▼
                              ┌─────────────────┐
                              │   CloudFlare    │
                              │  CDN + WAF +    │
                              │  DDoS Protect   │
                              └────────┬────────┘
                                       │
                                       ▼
                              ┌─────────────────┐
                              │   Nginx (LB)    │
                              │  SSL Termination│
                              │  Reverse Proxy  │
                              └────────┬────────┘
                                       │
                      ┌────────────────┼────────────────┐
                      │                │                │
                      ▼                ▼                ▼
               ┌──────────┐    ┌──────────┐    ┌──────────┐
               │  App 01  │    │  App 02  │    │  App 03  │
               │PHP-FPM   │    │PHP-FPM   │    │PHP-FPM   │
               │Horizon   │    │Horizon   │    │Horizon   │
               │Reverb WS │    │Reverb WS │    │Reverb WS │
               └────┬─────┘    └────┬─────┘    └────┬─────┘
                    │               │               │
                    └───────────────┼───────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    │               │               │
                    ▼               ▼               ▼
            ┌────────────┐  ┌────────────┐  ┌────────────┐
            │  PostgreSQL│  │   Redis    │  │   MinIO    │
            │  Primary   │  │  Cluster   │  │  (S3 API)  │
            │  + PostGIS │  │  3 nodes   │  │            │
            └─────┬──────┘  └────────────┘  └────────────┘
                  │
                  │ Streaming Replication
                  ▼
            ┌────────────┐
            │ PostgreSQL │
            │  Replica   │
            └────────────┘
```

### 2.2. Spécifications matérielles cibles

| Tier | Rôle | vCPU | RAM | Disk | Network |
|------|------|------|-----|------|---------|
| `app-01/02/03` | PHP-FPM + Horizon + Reverb | 2 | 4 Go | 50 Go SSD | 1 Gbps |
| `db-primary` | PostgreSQL 16 + PostGIS | 2 | 4 Go | 100 Go SSD | 1 Gbps |
| `db-replica` | Replicat streaming | 2 | 4 Go | 100 Go SSD | 1 Gbps |
| `redis-*` | Cache + Sessions + Queues + PubSub | 1 | 1 Go | 20 Go SSD | 1 Gbps |
| `minio` | Stockage objet (photos, PDF, SHP) | 1 | 2 Go | 200 Go+ | 1 Gbps |
| `nginx-lb` | Load balancer + SSL | 1 | 1 Go | 20 Go SSD | 1 Gbps |

---

## 3. COMPOSANTS APPLICATIFS

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         SICOFIL — COMPOSANTS INTERNES                       │
├─────────────────────────────────────────────────────────────────────────────┤
│  PRESENTATION LAYER                                                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐   │
│  │ Blade Views │ │  Leaflet.js │ │  Chart.js   │ │  Bootstrap 5 +      │   │
│  │ (Templates) │ │   (Carte)   │ │ (Graphiques)│ │  Tailwind CDN       │   │
│  └──────┬──────┘ └──────┬──────┘ └──────┬──────┘ └──────────┬──────────┘   │
│         └───────────────┴───────────────┴───────────────────┘                │
├─────────────────────────────────────────────────────────────────────────────┤
│  APPLICATION LAYER (Laravel 10)                                             │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────────┐   │
│  │  Controllers │ │   Models     │ │  Middleware  │ │   Jobs (Queue)   │   │
│  │  15+ modules │ │  Eloquent    │ │  RBAC + Sec  │ │  PDF / SMS /     │   │
│  │  Auth / Map  │ │  + Scopes    │ │  + Audit     │ │  Webhooks /      │   │
│  │  Paiement    │ │              │ │              │ │  Notifications   │   │
│  └──────┬───────┘ └──────┬───────┘ └──────┬───────┘ └────────┬─────────┘   │
├─────────┼────────────────┼────────────────┼──────────────────┼─────────────┤
│         │                │                │                  │             │
│  ┌──────┴────────────────┴────────────────┴──────────────────┴──────────┐   │
│  │                         SERVICES                                     │   │
│  │  FraudDetectionService │ NotificationService │ PaymentReconciliation│   │
│  └────────────────────────┴─────────────────────┴──────────────────────┘   │
├─────────────────────────────────────────────────────────────────────────────┤
│  DATA LAYER                                                                 │
│  ┌────────────────┐ ┌────────────────┐ ┌────────────────┐ ┌─────────────┐ │
│  │   PostgreSQL   │ │     Redis      │ │   File System  │ │   MinIO/S3  │ │
│  │  + PostGIS     │ │  (Predis)      │ │   (Fallback)   │ │  (Object)   │ │
│  │  20+ tables    │ │  Cache/Queue   │ │                │ │             │ │
│  └────────────────┘ └────────────────┘ └────────────────┘ └─────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Modules applicatifs

| Module | Controller | Models | Jobs |
|--------|-----------|--------|------|
| **Authentification** | `Auth\LoginController` | `User`, `Contribuable` | `SendSmsNotification` |
| **Recensement UC** | `UniteCommercialeController` | `UniteCommerciale` | `GenerateReceiptPdf` |
| **Paiements** | `PaiementController` | `Paiement`, `Taxe` | `ProcessWaveWebhook`, `ProcessOrangeMoneyWebhook` |
| **Cartographie** | `MapController` | `ZoneFiscale`, `Commune` | — |
| **Rapports** | `RapportController` | `Paiement`, `UniteCommerciale` | `GenerateReceiptPdf` |
| **Gestion Commune** | `CommuneController` | `Commune`, `User` | — |
| **Support** | `SupportTicketController` | `SupportTicket` | — |
| **Monitoring** | `MonitoringController` | `SystemLog` | — |

---

## 4. FLUX DE DONNÉES

### 4.1. Matrice des flux applicatifs

| Source → Cible | Protocole | Port | Fréquence | Données | Chiffrement |
|----------------|-----------|------|-----------|---------|-------------|
| Client → Nginx | HTTPS | 443 | Continu | HTTP requests | TLS 1.3 |
| Nginx → PHP-FPM | FastCGI | 9000 | Continu | HTTP forward | Loopback |
| PHP → PostgreSQL | TCP/IP | 5432 | Requête | SQL + PostGIS | SSL (prod) |
| PHP → Redis | TCP/IP | 6379 | Continu | Sessions, cache, queues | TLS (prod) |
| PHP → MinIO/S3 | HTTPS | 9000 | On-demand | Photos, PDF, SHP | TLS 1.3 |
| PHP → Wave API | HTTPS | 443 | On-demand | JSON (paiement) | OAuth 2.0 + TLS |
| PHP → Orange Money | HTTPS | 443 | On-demand | JSON (paiement) | API Key + TLS |
| PHP → SMS Gateway | HTTPS | 443 | On-demand | JSON (SMS) | API Key + TLS |
| PG Primary → Replica | Streaming | 5432 | Continu | WAL | SSL |
| Redis → Replica | TCP | 6379 | Continu | RDB/AOF | — |

### 4.2. Flux — Paiement complet

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ CONTRIBUABLE│     │   SICOFIL   │     │   WAVE API  │     │  POSTGRESQL │
│  (Navigateur)│     │  (Backend)   │     │  (Externe)  │     │  (PostGIS)  │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │                   │
       │ ① POST /contribuable/paiement       │                   │
       │  {unite_id, taxe_id, montant}       │                   │
       │────────────────────────────────────>│                   │
       │                   │ ② INSERT paiement (pending)         │
       │                   │──────────────────────────────────────>│
       │                   │ ③ Génère référence unique           │
       │                   │ ④ POST wave-api.com/v1/checkout     │
       │                   │  {reference, amount, callback_url} │
       │                   │────────────────────────────────────>│
       │                   │<────────────────────────────────────│
       │                   │  {checkout_url, transaction_id}     │
       │ ⑤ Redirect checkout_url             │                   │
       │<────────────────────────────────────│                   │
       │  [Utilisateur valide sur Wave]      │                   │
       │                   │ ⑥ POST /webhooks/wave               │
       │                   │  {transaction_id, status, reference}│
       │                   │<────────────────────────────────────│
       │                   │ ⑦ Vérification HMAC signature       │
       │                   │ ⑧ UPDATE paiement → statut=valide   │
       │                   │──────────────────────────────────────>│
       │                   │ ⑨ Dispatch jobs (PDF + SMS)         │
       │ ⑩ Confirmation + Reçu                 │                   │
       │<────────────────────────────────────│                   │
```

---

## 5. SÉQUENCE AUTHENTIFICATION

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  CLIENT  │    │   Nginx  │    │  Laravel │    │  Redis   │    │ PostgreSQL│
│ (Browser) │    │  (LB)    │    │ (Auth)   │    │(Session) │    │  (User)   │
└────┬─────┘    └────┬─────┘    └────┬─────┘    └────┬─────┘    └────┬─────┘
     │               │               │               │               │
     │ ① POST /login │               │               │               │
     │  {id, pwd, role}              │               │               │
     │──────────────>│               │               │               │
     │               │──────────────>│               │               │
     │               │               │ ② RateLimit check (Redis)     │
     │               │               │──────────────>│               │
     │               │               │<──────────────│               │
     │               │               │ ③ SELECT user WHERE identifiant=?
     │               │               │──────────────────────────────>│
     │               │               │<──────────────────────────────│
     │               │               │ ④ Hash::check(password, hash)│
     │               │               │ ⑤ Log security event         │
     │               │               │──────────────────────────────>│
     │               │               │ ⑥ CREATE session (Redis)    │
     │               │               │──────────────>│               │
     │               │               │<──────────────│               │
     │               │<──────────────│               │               │
     │ ⑦ Redirect /{role}/dashboard │               │               │
     │<──────────────│               │               │               │
     │ ⑧ GET /dashboard              │               │               │
     │──────────────>│               │               │               │
     │               │──────────────>│               │               │
     │               │               │ ⑨ CheckRole + SetCommuneCtx │
     │               │               │ ⑩ SELECT scope data          │
     │               │               │──────────────────────────────>│
     │               │               │<──────────────────────────────│
     │               │<──────────────│               │               │
     │ ⑪ HTML + Stats│               │               │               │
     │<──────────────│               │               │               │
```

---

## 6. SÉQUENCE PAIEMENT MOBILE MONEY

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│CONTRIBUABLE│   │ SICOFIL  │    │   WAVE   │    │  QUEUE   │    │ POSTGRES │
│  (Phone)   │   │ (Backend)│    │   API    │    │ (Redis)  │    │          │
└────┬─────┘    └────┬─────┘    └────┬─────┘    └────┬─────┘    └────┬─────┘
     │               │               │               │               │
     │ ① Sélection UC + Taxe + Mode  │               │               │
     │────────────────>│               │               │               │
     │               │ ② Validation + Calcul montant │               │
     │               │ ③ INSERT paiement (pending)   │               │
     │               │─────────────────────────────────────────────────>│
     │               │<─────────────────────────────────────────────────│
     │               │ ④ POST wave-api.com/v1/checkout │               │
     │               │────────────────>│               │               │
     │               │<────────────────│               │               │
     │               │  {checkout_url} │               │               │
     │ ⑤ Redirect checkout_url       │               │               │
     │<────────────────│               │               │               │
     │ [Validation OTP Wave]         │               │               │
     │               │ ⑥ POST /webhooks/wave            │               │
     │               │  {transaction_id, status, reference}│
     │               │<────────────────│               │               │
     │               │ ⑦ Vérification HMAC signature    │               │
     │               │ ⑧ UPDATE → statut=valide         │               │
     │               │─────────────────────────────────────────────────>│
     │               │ ⑨ Dispatch jobs (PDF + SMS)      │               │
     │               │───────────────────┐               │               │
     │ ⑩ Confirmation + Reçu         │               │               │
     │<────────────────│               │               │               │
     │               │               │               │ ⑪ Worker:      │
     │               │               │               │  Generate PDF  │
     │               │               │               │  Upload S3     │
     │               │               │               │  Envoi SMS     │
```

---

## 7. ARCHITECTURE EN COUCHES

```
┌─────────────────────────────────────────────────────────────────┐
│  COUCHE 5 : PRÉSENTATION (Client)                               │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │
│  │  Blade  │ │VanillaJS│ │ Leaflet │ │ Chart.js│ │Bootstrap│  │
│  │ Templates││ (AJAX)  │ │  (Map)  │ │(Rapports)│ │  (UI)   │  │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘  │
├─────────────────────────────────────────────────────────────────┤
│  COUCHE 4 : CONTRÔLEUR (HTTP Request Handling)                │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │
│  │  Auth   │ │UniteCom-│ │Paiement │ │  Map    │ │Rapport  │  │
│  │Controller│ │merciale │ │Controller│ │Controller│ │Controller│  │
│  │ (Multi) │ │Controller││ (Multi) │ │ (Multi) │ │ (Multi) │  │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘  │
├─────────────────────────────────────────────────────────────────┤
│  COUCHE 3 : MÉTIER / SERVICES                                  │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐  │
│  │FraudDetection    │ │Notification     │ │PaymentReconciliation││
│  │Service           │ │Service          │ │Service          │  │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘  │
├─────────────────────────────────────────────────────────────────┤
│  COUCHE 2 : MODÈLE / DONNÉES (Eloquent ORM)                   │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │
│  │  User   │ │  UC     │ │Paiement │ │ Zone    │ │ Commune │  │
│  │ (RBAC)  │ │ (PostGIS)│ │ (Audit) │ │Fiscale  │ │         │  │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘  │
├─────────────────────────────────────────────────────────────────┤
│  COUCHE 1 : INFRASTRUCTURE (Persistence)                       │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌───────────┐│
│  │ PostgreSQL  │ │    Redis    │ │ FileSystem  │ │ MinIO/S3  ││
│  │ + PostGIS   │ │(Cache/Queue)│ │ (Fallback)  │ │ (Object)  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └───────────┘│
└─────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────┐
│  COUCHE 0 : APIS EXTERNES (Intégrations)                        │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌───────────┐  │
│  │  Wave API   │ │  Orange M.  │ │  Free M.    │ │  SMS GW   │  │
│  │ (REST JSON) │ │ (REST JSON) │ │ (REST JSON) │ │(REST/SMPP)│  │
│  └─────────────┘ └─────────────┘ └─────────────┘ └───────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 8. SCHÉMA RÉSEAU & PARE-FEU

### 8.1. Topologie réseau

```
┌─────────────────────────────────────────────────────────────────┐
│                    DMZ (CloudFlare + WAF)                        │
│  104.21.x.x (Anycast)                                           │
└────────────────────────────┬────────────────────────────────────┘
                             │ 443
┌────────────────────────────▼────────────────────────────────────┐
│                    BASTION / VPN                                   │
│  ssh:22 (accès restreint IP whitelist)                           │
└────────────────────────────┬────────────────────────────────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
┌─────────────▼────┐ ┌───────▼────────┐ ┌──▼───────────────┐
│  Nginx LB        │ │  App Cluster   │ │  Data Cluster    │
│  10.0.1.10       │ │  10.0.2.0/24   │ │  10.0.3.0/24     │
│  SSL Termination │ │  PHP-FPM       │ │  PostgreSQL      │
│  Rate Limiting   │ │  Horizon       │ │  Redis           │
│  Reverse Proxy   │ │  Reverb        │ │  MinIO           │
└──────────────────┘ └────────────────┘ └──────────────────┘
```

### 8.2. Règles pare-feu

| Direction | Protocole | Port | Source → Destination | Action |
|-----------|-----------|------|----------------------|--------|
| IN | TCP | 443 | Internet → Nginx LB | **ALLOW** |
| IN | TCP | 80 | Internet → Nginx LB | **DENY** (redirection 443) |
| IN | TCP | 22 | VPN/Bastion → All hosts | **ALLOW** |
| IN | TCP | 5432 | App servers → DB Primary | **ALLOW** |
| IN | TCP | 5432 | DB Replica → DB Primary | **ALLOW** |
| IN | TCP | 6379 | App servers → Redis | **ALLOW** |
| IN | TCP | 9000 | App servers → MinIO | **ALLOW** |
| OUT | TCP | 443 | App servers → Wave/OM/SMS | **ALLOW** |
| OUT | TCP | 53 | All hosts → DNS | **ALLOW** |
| ALL | ANY | ANY | Any → Any (non listé) | **DENY** |

---

## 9. MATRICE DES TECHNOLOGIES

| Couche | Composant | Technologie | Version | Licence | Alternative |
|--------|-----------|-------------|---------|---------|-------------|
| OS | Serveur | Ubuntu Server LTS | 22.04/24.04 | GPL | Debian, RHEL |
| Web | Load Balancer | Nginx | 1.24+ | BSD | Traefik, HAProxy |
| App | Runtime | PHP-FPM | 8.1+ | PHP License | 8.2+ (recommandé) |
| App | Framework | Laravel | 10.48+ | MIT | Symfony |
| App | UI Engine | Blade | Native | MIT | Livewire |
| App | CSS Framework | Bootstrap | 5.3 | MIT | Tailwind (partiel) |
| App | Maps | Leaflet.js | 1.9+ | BSD | MapLibre, OpenLayers |
| App | Charts | Chart.js | 4.x | MIT | D3.js |
| App | PDF | DomPDF | 3.1 | LGPL | Snappy |
| App | Excel | Laravel Excel | 3.1 | MIT | — |
| App | Auth API | Laravel Sanctum | 3.2 | MIT | Passport (inclus) |
| App | WebSockets | Laravel Reverb | 1.0 | MIT | Pusher, Soketi |
| App | Queue Manager | Laravel Horizon | 5.21 | MIT | — |
| Data | SGBD | PostgreSQL | 16+ | PostgreSQL | MySQL 8 (dev actuel) |
| Data | Spatial | PostGIS | 3.4+ | GPL | — (obligatoire) |
| Data | Cache/Queue | Redis | 7.2+ | BSD | Memcached, RabbitMQ |
| Data | Storage | MinIO | 2024+ | AGPL | AWS S3, Wasabi |
| Data | Client Redis | Predis | 2.2 | MIT | phpredis (C ext) |
| Infra | Container | Docker | 24.0+ | Apache | Podman |
| Infra | Orchestration | Docker Compose | 2.20+ | Apache | Kubernetes |
| Sec | WAF | CloudFlare | Pro/Biz | Commercial | AWS WAF, ModSecurity |
| Sec | Certificats | Let's Encrypt | — | Gratuit | ZeroSSL, Sectigo |
| CI/CD | Pipeline | GitHub Actions | — | Gratuit/Payant | GitLab CI |
| Monitoring | Dashboard | Laravel Horizon | Native | MIT | Prometheus + Grafana |
| Monitoring | Logs | — | — | — | ELK / Loki (à mettre en place) |

---

## 10. GOUVERNANCE RBAC

### 10.1. Matrice rôles × permissions

| Permission | Admin SICOFIL | Maire | Gestionnaire | Collecteur | Contribuable |
|------------|:-------------:|:-----:|:------------:|:----------:|:------------:|
| Créer commune | ✅ | ❌ | ❌ | ❌ | ❌ |
| Créer utilisateurs (tous rôles) | ✅ | ✅ | ❌ (sauf maire) | ❌ | ❌ |
| CRUD UC | ✅ | ✅ | ✅ | ✅ (zone seule) | ❌ |
| Recensement terrain | ❌ | ❌ | ❌ | ✅ | ❌ |
| Gestion taxes | ✅ | ✅ | ✅ | ❌ | ❌ |
| Gestion zones fiscales | ✅ | ✅ | ✅ | ❌ | ❌ |
| Enregistrer paiement | ❌ | ❌ | ❌ | ✅ | ✅ (en ligne) |
| Annuler paiement | ❌ | ❌ | ✅ | ❌ | ❌ |
| Voir paiements (tous) | ✅ | ✅ | ✅ | ✅ (siens) | ✅ (siens) |
| Dashboard stratégique | ✅ | ✅ | ❌ | ❌ | ❌ |
| Cartographie complète | ✅ | ✅ | ✅ | ✅ (zone) | ❌ |
| Rapports officiels (11) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Export PDF/Excel | ✅ | ✅ | ✅ | ❌ | ❌ |
| Monitoring système | ✅ | ❌ | ❌ | ❌ | ❌ |
| Toggle recensement/màj | ✅ | ❌ | ❌ | ❌ | ❌ |
| Tracking GPS collecteurs | ✅ | ✅ | ❌ | ❌ | ❌ |
| Portail public (consultation) | ❌ | ❌ | ❌ | ❌ | ✅ |
| Paiement en ligne | ❌ | ❌ | ❌ | ❌ | ✅ |

### 10.2. Scopage des données

```
┌─────────────────────────────────────────────────────────────┐
│                     ADMIN SICOFIL                            │
│              Scope: Toutes les communes                     │
│         session('commune_scope_id') = filtre opt.             │
└─────────────────────────────────────────────────────────────┘
                              │
          ┌───────────────────┼───────────────────┐
          │                   │                   │
          ▼                   ▼                   ▼
   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
   │   MAIRE     │    │ GESTIONNAIRE│    │  COLLECTEUR │
   │ Scope:      │    │ Scope:      │    │ Scope:      │
   │ user->commune│   │ user->commune│   │ user->zone  │
   └─────────────┘    └─────────────┘    └─────────────┘
                                               │
                                               ▼
                                        ┌─────────────┐
                                        │ CONTRIBUABLE│
                                        │ Scope:      │
                                        │ phone->UC   │
                                        └─────────────┘
```

---

## 11. MATRICE SÉCURITÉ

| # | Contrôle | Implémenté | Statut | Fichier / Middleware |
|---|----------|:----------:|:------:|---------------------|
| 1 | CSP (Content Security Policy) | ✅ | ⚠️ Commenté | `AddSecurityHeaders.php` — ligne 24 `Kernel.php` |
| 2 | X-Frame-Options (Clickjacking) | ✅ | ⚠️ Commenté | `AddSecurityHeaders.php` |
| 3 | HSTS (HTTPS forcing) | ✅ | ⚠️ Commenté | `AddSecurityHeaders.php` |
| 4 | X-Content-Type-Options | ✅ | ⚠️ Commenté | `AddSecurityHeaders.php` |
| 5 | Referrer-Policy | ✅ | ⚠️ Commenté | `AddSecurityHeaders.php` |
| 6 | Permissions-Policy | ✅ | ⚠️ Commenté | `AddSecurityHeaders.php` |
| 7 | Rate limiting login | ✅ | ✅ Actif | `LoginController::checkRateLimit()` |
| 8 | Rate limiting API | ✅ | ✅ Actif | `RouteServiceProvider::configureRateLimiting()` |
| 9 | CSRF protection | ✅ | ✅ Actif | `VerifyCsrfToken` middleware |
| 10 | Bcrypt password hashing | ✅ | ✅ Actif | Laravel natif |
| 11 | Encrypted cookies | ✅ | ✅ Actif | `EncryptCookies` middleware |
| 12 | RBAC 5 rôles | ✅ | ✅ Actif | `CheckRole` middleware |
| 13 | Scopage par commune | ✅ | ✅ Actif | `SetCommuneContext` middleware |
| 14 | Détection brute-force | ✅ | ✅ Actif | `SecurityMiddleware::checkRateLimit()` |
| 15 | Détection XSS/SQLi patterns | ✅ | ✅ Actif | `SecurityMiddleware::checkMaliciousPatterns()` |
| 16 | Détection session hijacking | ✅ | ✅ Actif | `SecurityMiddleware::checkSessionSecurity()` |
| 17 | Détection DDoS/flood | ✅ | ✅ Actif | `SecurityMiddleware::checkFlood()` |
| 18 | Logs de sécurité centralisés | ✅ | ✅ Actif | `SystemLog::createLog()` |
| 19 | OTP Contribuable (6 digits, 10 min) | ✅ | ✅ Actif | `Contribuable::generateOtp()` |
| 20 | MFA Maire/Gestionnaire | ❌ | ❌ Non implémenté | À prioriser |
| 21 | WAF (pare-feu applicatif) | ❌ | ❌ Non implémenté | CloudFlare recommandé |
| 22 | Chiffrement AES-256 données sensibles | ❌ | ❌ Non implémenté | NINEA, CIN, téléphone |
| 23 | Signature des logs (intégrité) | ❌ | ❌ Non implémenté | SHA-256 ou HMAC |

---

## 12. SAUVEGARDE, PRA & PCA

### 12.1. Schéma de sauvegarde

```
┌─────────────────────────────────────────────────────────────────┐
│                    PLAN DE SAUVEGARDE                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐        ┌─────────────┐        ┌─────────────┐  │
│  │ PostgreSQL  │        │   MinIO     │        │   Redis     │  │
│  │  Primary    │        │  (Fichiers) │        │  (Cache)    │  │
│  └──────┬──────┘        └──────┬──────┘        └──────┬──────┘  │
│         │                        │                        │        │
│         ▼                        ▼                        ▼        │
│  ┌─────────────┐        ┌─────────────┐        ┌─────────────┐  │
│  │ pg_dump     │        │ mc mirror   │        │ BGSAVE      │  │
│  │ quotidien   │        │ quotidien   │        │ quotidien   │  │
│  │ 02:00 UTC   │        │ 02:00 UTC   │        │ 02:00 UTC   │  │
│  │ gzip + AES  │        │ incrémental │        │ RDB + AOF   │  │
│  └──────┬──────┘        └──────┬──────┘        └──────┬──────┘  │
│         │                        │                        │        │
│         └────────────────────────┼────────────────────────┘        │
│                                  ▼                                │
│                           ┌─────────────┐                        │
│                           │ Stockage    │                        │
│                           │ Froid       │                        │
│                           │ (S3 Glacier │                        │
│                           │  ou équivalent)│                       │
│                           │ Rétention:  │                        │
│                           │ 30 jours hot│                        │
│                           │ 1 an cold   │                        │
│                           └─────────────┘                        │
└─────────────────────────────────────────────────────────────────┘
```

### 12.2. Tableau PRA / PCA

| Événement | RTO | RPO | Procédure | Responsable |
|-----------|-----|-----|-----------|-------------|
| Crash serveur applicatif | 15 min | 0 | Redémarrage conteneur / bascule LB | SysAdmin |
| Corruption base primaire | 30 min | 5 min | Bascule sur réplica + promotion | DBA |
| Panne réplica | 1h | 0 | Reconstruction réplica depuis primaire | DBA |
| Panne Redis | 10 min | 0 | Redémarrage cluster / reconstruction | SysAdmin |
| Panne MinIO | 30 min | 0 | Restauration depuis backup froid | SysAdmin |
| Attaque ransomware | 2h | 24h | Isolation + restauration backup chiffré | RSSI |
| Incendie / sinistre site | 4h | 1h | Bascule site secondaire (si multi-AZ) | RSSI |

---

## 13. ROADMAP

### 13.1. Phases de déploiement

```
Phase 1 ──[==========]── Pré-production (Sprint 1-2)
            │
            ├── Migration MySQL → PostgreSQL + PostGIS
            ├── Réactivation AddSecurityHeaders middleware
            ├── Configuration Redis + Horizon (Linux)
            ├── Intégration webhooks Wave / Orange Money
            └── Mise en place MinIO / S3 pour fichiers

Phase 2 ──[==========]── Durcissement (Sprint 3)
            │
            ├── MFA OTP pour Maire & Gestionnaire
            ├── Rate limiting global + WAF
            ├── Chiffrement AES-256 données sensibles
            ├── Tests de charge (k6 / JMeter)
            └── Replicat PostgreSQL (A→B)

Phase 3 ──[==========]── Production & Scale (Sprint 4)
            │
            ├── Déploiement VPS/cloud avec Docker
            ├── CI/CD complète (GitHub Actions → VPS)
            ├── Monitoring Grafana + AlertManager
            ├── Backups automatisés + PRA
            └── Test de pénétration externe
```

### 13.2. Dépendances critiques

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ PostgreSQL   │────▶│   MinIO      │────▶│   Redis      │
│ + PostGIS    │     │   (S3)       │     │   (Cache)    │
└──────────────┘     └──────────────┘     └──────────────┘
       │                    │                    │
       ▼                    ▼                    ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Laravel    │◀────│   Horizon    │◀────│   Reverb     │
│   App        │     │   (Workers)  │     │   (WS)       │
└──────────────┘     └──────────────┘     └──────────────┘
       │
       ▼
┌──────────────┐     ┌──────────────┐
│   Wave API   │     │ Orange Money │
│   (REST)     │     │   (REST)     │
└──────────────┘     └──────────────┘
```

---

## 14. GLOSSAIRE

| Terme | Définition |
|-------|------------|
| **UC** | Unité Commerciale — entité fiscale recensée |
| **SHP** | Shapefile — format géographique ESRI |
| **PostGIS** | Extension spatiale PostgreSQL |
| **CSP** | Content Security Policy |
| **HSTS** | HTTP Strict Transport Security |
| **MFA** | Multi-Factor Authentication |
| **OTP** | One-Time Password |
| **WAF** | Web Application Firewall |
| **PRA** | Plan de Reprise d'Activité |
| **PCA** | Plan de Continuité d'Activité |
| **RTO** | Recovery Time Objective |
| **RPO** | Recovery Point Objective |
| **RBAC** | Role-Based Access Control |
| **RSSI** | Responsable de la Sécurité des Systèmes d'Information |

---

**Fin du document**
