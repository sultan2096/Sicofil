# 🔧 Configuration PostgreSQL pour SICOSA

## 📋 Problème Identifié

L'erreur `fe_sendauth: no password supplied` indique que PostgreSQL nécessite un mot de passe ou que la configuration d'authentification n'est pas correcte.

## 🛠️ Solutions

### **Solution 1 : Configurer PostgreSQL avec mot de passe**

1. **Ouvrez pgAdmin** ou connectez-vous à PostgreSQL en ligne de commande
2. **Exécutez ces commandes** :

```sql
-- Se connecter en tant que superutilisateur
psql -U postgres

-- Créer la base de données si elle n'existe pas
CREATE DATABASE sicosa_db;

-- Créer un utilisateur spécifique pour SICOSA
CREATE USER sicosa_user WITH PASSWORD 'sicosa2025';

-- Donner les permissions
GRANT ALL PRIVILEGES ON DATABASE sicosa_db TO sicosa_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO sicosa_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO sicosa_user;

-- Ou utiliser l'utilisateur postgres existant
ALTER USER postgres PASSWORD 'postgres';
```

### **Solution 2 : Configuration sans mot de passe (Trust)**

1. **Trouvez le fichier** `pg_hba.conf` (généralement dans le dossier de données PostgreSQL)
2. **Modifiez la ligne** pour l'authentification locale :

```
# Changer cette ligne :
local   all             all                                     md5
# En :
local   all             all                                     trust
```

3. **Redémarrez PostgreSQL** après modification

### **Solution 3 : Utiliser un utilisateur existant**

Si vous avez déjà un utilisateur PostgreSQL configuré, modifiez les scripts avec vos paramètres :

```php
$host = 'localhost';
$dbname = 'sicosa_db';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';
$port = '5432';
```

## 🔍 Vérification

### **Tester la connexion PostgreSQL :**

```bash
# Test de connexion
psql -h localhost -p 5432 -U postgres -d sicosa_db

# Ou avec mot de passe
psql -h localhost -p 5432 -U postgres -d sicosa_db -W
```

### **Vérifier que PostgreSQL est démarré :**

```bash
# Windows (Services)
services.msc
# Cherchez "postgresql" et vérifiez qu'il est démarré

# Ou en ligne de commande
net start postgresql
```

## 📁 Fichiers de Configuration PostgreSQL

### **pg_hba.conf** (Authentification)
```
# TYPE  DATABASE        USER            ADDRESS                 METHOD
local   all             all                                     trust
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
```

### **postgresql.conf** (Configuration principale)
```
listen_addresses = 'localhost'
port = 5432
```

## 🚀 Script de Configuration Automatique

Créons un script qui configure automatiquement PostgreSQL :

```sql
-- Script de configuration PostgreSQL pour SICOSA
-- À exécuter en tant que superutilisateur

-- Créer la base de données
CREATE DATABASE sicosa_db;

-- Créer l'utilisateur
CREATE USER sicosa_user WITH PASSWORD 'sicosa2025';

-- Donner toutes les permissions
GRANT ALL PRIVILEGES ON DATABASE sicosa_db TO sicosa_user;

-- Se connecter à la base de données
\c sicosa_db;

-- Donner les permissions sur le schéma public
GRANT ALL PRIVILEGES ON SCHEMA public TO sicosa_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO sicosa_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO sicosa_user;

-- Permissions par défaut pour les futures tables
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO sicosa_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO sicosa_user;
```

## 🔧 Script PHP avec Configuration Détectée

Le script `migrate_detect.php` que j'ai créé teste automatiquement plusieurs configurations :

1. **postgres sans mot de passe**
2. **postgres avec mot de passe "postgres"**
3. **postgres avec mot de passe "root"**
4. **utilisateur "sicosa" avec mot de passe "sicosa"**
5. **port alternatif 5433**

## 📞 Support

Si vous continuez à avoir des problèmes :

1. **Vérifiez** que PostgreSQL est installé et démarré
2. **Utilisez pgAdmin** pour configurer graphiquement
3. **Consultez** les logs PostgreSQL pour plus de détails
4. **Vérifiez** les paramètres de pare-feu

---

**Une fois PostgreSQL configuré, la migration se déroulera automatiquement ! 🎯**
