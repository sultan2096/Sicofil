# Déploiement SICOFIL (GitHub Actions)

## Secrets requis

Dans **Settings → Secrets and variables → Actions** :

| Secret | Description |
|--------|-------------|
| `DEPLOY_HOST` | Adresse du serveur (IP ou hostname) |
| `DEPLOY_USER` | Utilisateur SSH (ex: `deploy`, `root`) |
| `SSH_PRIVATE_KEY` | Clé privée SSH (contenu complet) |
| `DEPLOY_PATH` | *(optionnel)* Chemin sur le serveur (défaut: `/var/www/sicofil`) |

## Déclenchement

- **Manuel** : Actions → Deploy SICOFIL → Run workflow
- **Automatique** : à chaque push sur `main` (sauf modification de `.md`, `docs/`)

## Prérequis serveur

- PHP 8.2+, extensions Laravel
- PostgreSQL, Redis
- Composer
- Clé SSH du dépôt autorisée sur le serveur
