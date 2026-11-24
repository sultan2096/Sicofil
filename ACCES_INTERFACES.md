# SICOSA - Guide d'Accès aux Interfaces

## 🚀 Démarrage Rapide

### 1. Démarrer le serveur Laravel
```bash
php artisan serve
```
Le serveur sera disponible sur : **http://127.0.0.1:8000**

---

## 🌐 Accès aux Interfaces

### Interface Principale Laravel
- **URL :** http://127.0.0.1:8000
- **Description :** Page d'accueil avec sélection de profil
- **Fonctionnalité :** Choix entre Maire, Gestionnaire, Collecteur

### Connexions Directes

#### 🏛️ Interface Maire
- **Connexion directe :** http://127.0.0.1:8000/maire/login
- **Identifiants de test :**
  - Utilisateur : `maire2025`
  - Mot de passe : `sicosa2025`

#### ⚙️ Interface Gestionnaire
- **Connexion directe :** http://127.0.0.1:8000/gestionnaire/login
- **Identifiants de test :**
  - Utilisateur : `gestionnaire01`
  - Mot de passe : `gestionnaire2025`

#### 📱 Interface Collecteur
- **Connexion directe :** http://127.0.0.1:8000/collecteur/login
- **Identifiants de test :**
  - Utilisateur : `collecteur01` (ou collecteur02, collecteur03, etc.)
  - Mot de passe : `collecteur2025`

---

## 🧪 Pages de Test

### Test de Debug des Comptes
- **URL :** http://127.0.0.1:8000/debug-comptes
- **Fonctionnalité :** Affichage de tous les comptes utilisateurs

---

## 📋 Fonctionnalités par Interface

### 🏛️ Maire
- ✅ Gestion complète des utilisateurs
- ✅ Supervision de toutes les activités
- ✅ Rapports et statistiques globales
- ✅ Configuration système
- ✅ Accès à tous les modules

### ⚙️ Gestionnaire
- ✅ Gestion des collecteurs
- ✅ Administration des zones fiscales
- ✅ Gestion des unités commerciales
- ✅ Suivi des paiements
- ✅ Paramétrage des taxes

### 📱 Collecteur
- ✅ Collecte des paiements sur le terrain
- ✅ Recensement des unités commerciales
- ✅ Géolocalisation GPS
- ✅ Interface mobile optimisée
- ✅ Statistiques personnelles

---

## 🔧 Accès Rapide - Workflow de Test

### Pour tester le recensement d'une unité commerciale :

1. **Démarrer le serveur :**
   ```bash
   php artisan serve
   ```

2. **Accéder à l'interface collecteur :**
   - Aller sur : http://127.0.0.1:8000/collecteur/login

3. **Se connecter :**
   - Utilisateur : `collecteur01`
   - Mot de passe : `collecteur2025`

4. **Accéder au recensement :**
   - Menu "Unités commerciales" → "Ajouter"
   - Ou directement : http://127.0.0.1:8000/unites/create

5. **Utiliser la géolocalisation :**
   - Autoriser l'accès GPS quand demandé
   - Ou utiliser la position manuelle (Sandiara par défaut)

---

## 🌍 URLs Complètes de Test

### Connexions directes
```
Maire :        http://127.0.0.1:8000/maire/login
Gestionnaire : http://127.0.0.1:8000/gestionnaire/login
Collecteur :   http://127.0.0.1:8000/collecteur/login
```

### Pages de connexion
```
Maire :        http://127.0.0.1:8000/maire/login
Gestionnaire : http://127.0.0.1:8000/gestionnaire/login
Collecteur :   http://127.0.0.1:8000/collecteur/login
```

### Tests
```
Debug comptes : http://127.0.0.1:8000/debug-comptes
```

---

## ⚠️ Notes Importantes

1. **Serveur requis :** Le serveur Laravel doit être démarré (`php artisan serve`)
2. **Port par défaut :** 8000 (peut changer selon votre configuration)
3. **Géolocalisation :** Fonctionne mieux sur HTTPS en production
4. **Navigateur :** Recommandé : Chrome, Firefox, Edge (dernières versions)
5. **Mobile :** Interface collecteur optimisée pour mobile

---

## 🆘 Dépannage

### Le serveur ne démarre pas
```bash
# Vérifier les dépendances
composer install

# Vérifier la configuration
php artisan config:clear
php artisan cache:clear
```

### Erreur de connexion
- Vérifier que le serveur est démarré
- Utiliser http://127.0.0.1:8000 (pas localhost)
- Vérifier les identifiants de test

### Géolocalisation ne fonctionne pas
- Autoriser l'accès GPS dans le navigateur
- Utiliser la saisie manuelle en cas d'échec
- Position par défaut : Sandiara (14.43540, -16.79153)

---

## 📞 Support

Pour toute question ou problème :
1. Vérifier ce guide
2. Consulter la console du navigateur (F12)
3. Vérifier les logs Laravel dans `storage/logs/` 