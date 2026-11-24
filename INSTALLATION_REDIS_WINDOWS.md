# 🚀 Installation de Redis sur Windows (XAMPP)

## Option 1: Memurai (Recommandé pour Windows)

Memurai est un portage officiel de Redis pour Windows.

### Installation:

1. **Télécharger Memurai:**
   - Site: https://www.memurai.com/get-memurai
   - Version gratuite disponible

2. **Installer Memurai:**
   - Exécuter l'installeur
   - Suivre l'assistant d'installation
   - Memurai démarre automatiquement comme service Windows

3. **Vérifier l'installation:**
   ```bash
   # Ouvrir PowerShell
   telnet localhost 6379
   # Ou
   redis-cli ping
   ```

4. **Activer Redis dans SICOFIL:**
   
   Éditez `.env`:
   ```env
   REDIS_CLIENT=predis
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

5. **Redémarrer l'application:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

---

## Option 2: Redis dans Docker

Si vous avez Docker installé:

### Installation:

1. **Démarrer Redis:**
   ```bash
   docker run -d -p 6379:6379 --name redis redis:alpine
   ```

2. **Vérifier:**
   ```bash
   docker ps
   docker exec -it redis redis-cli ping
   ```

3. **Activer dans `.env`** (voir Option 1, étape 4)

---

## Option 3: Extension PHP Redis (Avancé)

⚠️ Plus complexe, nécessite de compiler l'extension PHP.

### Installation:

1. **Télécharger l'extension:**
   - Site: https://windows.php.net/downloads/pecl/releases/redis/
   - Choisir la version correspondant à votre PHP (8.2)

2. **Installer l'extension:**
   - Copier `php_redis.dll` dans `C:\xampp\php\ext\`
   - Éditer `C:\xampp\php\php.ini`
   - Ajouter: `extension=redis`
   - Redémarrer Apache

3. **Vérifier:**
   ```bash
   php -m | grep redis
   ```

4. **Modifier `.env`:**
   ```env
   REDIS_CLIENT=phpredis
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

---

## Comparaison des Options

| Option | Avantages | Inconvénients |
|--------|-----------|---------------|
| **Memurai** | ✅ Facile à installer<br>✅ Service Windows<br>✅ Compatible Redis | ⚠️ Gratuit limité |
| **Docker** | ✅ Isolé<br>✅ Facile à gérer<br>✅ Redis officiel | ⚠️ Nécessite Docker |
| **Extension PHP** | ✅ Performance maximale | ❌ Installation complexe<br>❌ Maintenance difficile |
| **Predis (actuel)** | ✅ Aucune installation<br>✅ Fonctionne immédiatement | ⚠️ Performance moindre |

---

## Configuration Actuelle (Sans Redis)

Votre application fonctionne actuellement avec:
- **Cache:** File (storage/framework/cache/)
- **Sessions:** File (storage/framework/sessions/)
- **Queue:** Database (table `jobs`)

**Performance:** Suffisante pour < 100 utilisateurs simultanés.

---

## Quand Installer Redis ?

Installez Redis si:
- ✅ Vous avez **plus de 50 utilisateurs simultanés**
- ✅ Vous voulez des **jobs asynchrones performants** (webhooks, SMS)
- ✅ Vous préparez la **mise en production**
- ✅ Vous utilisez **Laravel Horizon** (monitoring queues)

Pas besoin de Redis si:
- ✅ Vous êtes en **phase de développement**
- ✅ Vous testez localement avec **peu d'utilisateurs**
- ✅ Vous préférez la **simplicité**

---

## Test de Performance

### Avec File Cache (actuel):
```bash
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### Avec Redis (après installation):
```bash
php artisan tinker
>>> Cache::store('redis')->put('test', 'value', 60);
>>> Cache::store('redis')->get('test');
```

---

## Support

Pour toute question:
- **Documentation SICOFIL:** `SICOFIL_IMPLEMENTATION.md`
- **Démarrage rapide:** `DEMARRAGE_RAPIDE.md`

---

**Recommandation:** Pour l'instant, continuez sans Redis. Installez-le avant la mise en production.
