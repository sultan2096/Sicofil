# Laravel Horizon — Activer le statut « Active »

## Windows : Horizon ne peut pas tourner

Sous **Windows**, Laravel Horizon échoue avec :  
**`Call to undefined function pcntl_async_signals()`**

L’extension PHP **pcntl** (Process Control) n’existe pas sous Windows. Horizon en a besoin pour gérer les workers. Donc :

- **Sur Windows** : n’utilisez pas `php artisan horizon`. Utilisez **`php artisan queue:work`** pour traiter les jobs (avec `QUEUE_CONNECTION=database` dans `.env`). Double-cliquez sur **`start_queue_work.bat`** pour lancer le worker.
- Le **tableau de bord Horizon** (page web `/horizon`) reste consultable ; le statut restera **Inactive** tant que les workers ne sont pas gérés par Horizon (Linux / WSL / Docker).

## Pourquoi le statut est « Inactive » (sous Linux) ?

Horizon affiche **Inactive** quand ses workers ne tournent pas. Deux conditions sont nécessaires :

1. **Redis** doit être installé et démarré (Horizon utilise uniquement Redis pour les files).
2. **QUEUE_CONNECTION** doit être `redis` (pas `database` ni `sync`).
3. La commande **`php artisan horizon`** doit être lancée (sur **Linux / WSL / Docker**, pas sur Windows natif).

## Étapes pour avoir le statut « Active »

### 1. Installer et démarrer Redis (Windows)

- **Option A — Redis pour Windows (non officiel) :**  
  Télécharger par exemple : https://github.com/tporadowski/redis/releases  
  Installer puis lancer le service Redis (port 6379 par défaut).

- **Option B — WSL (Windows Subsystem for Linux) :**  
  Dans WSL : `sudo apt install redis-server` puis `redis-server`.

- **Option C — Docker :**  
  `docker run -d -p 6379:6379 redis:alpine`

Vérifier que Redis répond :  
`redis-cli ping` → doit répondre `PONG`.

### 2. Configurer l’application

Dans le fichier **`.env`** à la racine du projet :

```env
QUEUE_CONNECTION=redis
```

(Si vous utilisiez `CACHE_DRIVER=file` ou `SESSION_DRIVER=file` par défaut, vous pouvez laisser comme ça ; seules les files d’attente utilisent Redis pour Horizon.)

### 3. Démarrer Horizon

**Sous Windows (XAMPP) :**

- **Option 1 (recommandé) :** Double-cliquer sur **`start_horizon.bat`** à la racine du projet (il se place dans le bon dossier et lance Horizon).
- **Option 2 :** Dans PowerShell ou CMD, **aller d’abord dans le dossier du projet**, puis lancer :
  ```powershell
  cd c:\xampp\htdocs\sicosa
  c:\xampp\php\php.exe artisan horizon
  ```
  (Sans le `cd`, la commande échoue avec « Could not open input file: artisan ».)

**Sous Linux / macOS ou si `php` est dans le PATH :**

```bash
cd c:\xampp\htdocs\sicosa   # ou le chemin de votre projet
php artisan horizon
```

Garder la fenêtre ouverte. Le tableau de bord Horizon doit alors afficher **Status: Active** et **Total Processes** > 0.

### 4. Arrêter Horizon

Dans le terminal où Horizon tourne : **Ctrl+C**.

En production, on utilise en général **Supervisor** (ou un service système) pour garder Horizon actif en permanence.

## Si Redis n’est pas installé ou ne tourne pas

L’erreur **« Aucune connexion n’a pu être établie… [tcp://127.0.0.1:6379] »** signifie que Redis n’est pas démarré (ou pas installé).

**Solution sans Redis :**

1. Dans **`.env`**, laissez ou remettez :
   ```env
   QUEUE_CONNECTION=database
   ```
2. Les jobs sont alors enregistrés en base. Pour les exécuter, lancez dans le dossier du projet :
   ```bat
   c:\xampp\php\php.exe artisan queue:work
   ```
3. Le tableau de bord **Horizon** restera **Inactive** (il ne fonctionne qu’avec Redis). L’application SICOFIL fonctionne normalement avec `database` + `queue:work`.

**Résumé :** **Active** dans Horizon = Redis démarré + `QUEUE_CONNECTION=redis` + `php artisan horizon`. Sans Redis, utilisez `QUEUE_CONNECTION=database` et `php artisan queue:work`.
