# 🚀 SICOSA - Guide de Démarrage Local

## ✅ Installation Terminée !

Votre application SICOSA est maintenant configurée pour fonctionner localement avec XAMPP.

## 🔧 Démarrage Quotidien

### 1. Démarrer XAMPP
- Ouvrez le panneau de contrôle XAMPP
- Démarrez **Apache** et **MySQL**

### 2. Démarrer SICOSA
```bash
# Méthode 1 : Script automatique
.\start_sicosa.bat

# Méthode 2 : Commande manuelle
php artisan serve
```

## 🌐 Accès à l'Application

### URL Principal
http://127.0.0.1:8000

### Interfaces Directes
- 🏛️ **Maire** : http://127.0.0.1:8000/maire.html
- ⚙️ **Gestionnaire** : http://127.0.0.1:8000/admin.html
- 📱 **Collecteur** : http://127.0.0.1:8000/collecteur.html

### Connexions
- 🏛️ **Maire** : http://127.0.0.1:8000/maire/login
- ⚙️ **Gestionnaire** : http://127.0.0.1:8000/admin/login
- 📱 **Collecteur** : http://127.0.0.1:8000/collecteur/login

## 👥 Comptes de Test

| Rôle | Identifiant | Mot de passe |
|------|-------------|--------------|
| Maire | `maire2025` | `sicosa2025` |
| Gestionnaire | `gestionnaire01` | `gestionnaire2025` |
| Collecteur | `collecteur01` | `collecteur2025` |

*Note : Vous pouvez aussi utiliser collecteur02, collecteur03, etc.*

## 🧪 Pages de Test

- **Test Recensement** : http://127.0.0.1:8000/test_recensement.html
- **Test Géolocalisation** : http://127.0.0.1:8000/test_sicosa.html

## ⚙️ Fonctionnalités par Rôle

### 🏛️ Maire
- ✅ Vue d'ensemble des statistiques
- ✅ Rapports financiers
- ✅ Supervision générale
- ✅ Consultation seule (pas de modification)

### ⚙️ Gestionnaire
- ✅ Gestion des collecteurs
- ✅ Configuration des taxes
- ✅ Gestion des zones fiscales
- ✅ Rapports détaillés
- ✅ Administration complète

### 📱 Collecteur
- ✅ Recensement d'unités commerciales
- ✅ Collecte de paiements
- ✅ Géolocalisation GPS
- ✅ Interface mobile optimisée

## 🔄 Maintenance

### Réinitialiser la Base de Données
```bash
.\reset_database.bat
```

### Optimiser les Performances
```bash
.\optimize_performance.bat
```

### Arrêter l'Application
- Fermez le terminal où `php artisan serve` fonctionne
- Ou utilisez `Ctrl+C` dans le terminal

## 📞 Support

### Logs d'Erreur
Les logs sont dans : `storage/logs/laravel.log`

### Configuration
Fichier de configuration : `.env`

### Base de Données
- **Nom** : `sicosa_db`
- **Accès** : http://localhost/phpmyadmin

## 🎯 Workflow de Test Recommandé

1. **Démarrer** avec l'interface **Collecteur**
2. **Tester** le recensement d'une unité commerciale
3. **Basculer** vers l'interface **Admin** pour voir les données
4. **Vérifier** les rapports dans l'interface **Maire**

---

**✨ SICOSA est maintenant prêt pour l'utilisation locale !** 