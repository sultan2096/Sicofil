# 🧪 Test Rapide - Géomarketing Avancé

## ✅ Vérifications à effectuer

### 1. **Accès à l'interface**
- [ ] Connectez-vous en tant que **Maire**
- [ ] Cliquez sur **"Géomarketing"** dans la barre de navigation
- [ ] L'interface se charge correctement

### 2. **Carte de base**
- [ ] La carte s'affiche centrée sur Sandiara
- [ ] Les limites de la commune sont visibles (contour rouge)
- [ ] Le zoom fonctionne (+ et -)
- [ ] Cliquez sur les limites pour voir les infos de Sandiara

### 3. **Couches géographiques**

#### 🌡️ Population
- [ ] Cochez la case **"Population"**
- [ ] Une heatmap rouge/jaune apparaît
- [ ] Les zones les plus chaudes sont le centre-ville et Louly Benteigné

#### 🛣️ Routes
- [ ] Cochez la case **"Routes"**
- [ ] Les routes principales apparaissent :
  - Route Nationale 1 (rouge, épaisse)
  - Route de Thiès (orange)
  - Route de Mbour (orange)
  - Routes secondaires (gris)
- [ ] Cliquez sur une route pour voir son nom

#### 🏢 Bâti
- [ ] Cochez la case **"Bâti"**
- [ ] Les zones bâties apparaissent :
  - Centre Commercial (bleu)
  - Marché Central (bleu)
  - Zone Administrative (vert)
  - Quartier Résidentiel (vert)
  - Zone Industrielle (orange)
- [ ] Cliquez sur une zone pour voir les détails

#### 🔥 Heatmap population
- [ ] Cochez la case **"Heatmap population"**
- [ ] Une heatmap de densité d'activité apparaît
- [ ] Les zones les plus intenses sont le centre-ville

#### 🎯 Carte multi-critères
- [ ] Cochez la case **"Carte multi-critères"**
- [ ] Des cercles colorés apparaissent :
  - **Vert** : Zones d'excellence (Centre-Ville, Louly Benteigné)
  - **Orange** : Zones modérées (Route de Thiès)
  - **Rouge** : Zones à développer (Zone Agricole)
- [ ] Cliquez sur un cercle pour voir les scores détaillés

### 4. **Analyse des opportunités**

#### 📊 Filtres
- [ ] Sélectionnez **"Zone Centre-Ville"** dans le filtre Zone
- [ ] Cliquez sur **"Analyser les opportunités"**

#### 📈 Résultats
- [ ] Les statistiques s'affichent à droite :
  - Zones analysées : 5
  - Score moyen : ~60
  - Zones d'excellence : 2
  - Unités totales : 118

#### 📋 Analyse détaillée
- [ ] En bas de page, les cartes des zones apparaissent
- [ ] Chaque zone montre :
  - Score global (0-100)
  - Densité commerciale
  - Accessibilité routière
  - Potentiel bâti
  - Potentiel population
  - Recommandation

### 5. **Propositions d'investissement**

#### 💡 Affichage automatique
- [ ] Après analyse, les propositions apparaissent automatiquement
- [ ] 5 propositions sont affichées :
  1. **Supermarché** - Zone Centre-Ville (ROI 87.5%)
  2. **Boutique générale** - Zone Louly Benteigné (ROI 220%)
  3. **Station-service** - Zone Route de Thiès (ROI 20%)
  4. **Restaurant** - Zone Centre-Ville (ROI 125%)
  5. **Atelier mécanique** - Zone Rurale Sud (ROI 71.4%)

#### 📊 Détails des propositions
- [ ] Chaque proposition montre :
  - Zone et type d'investissement
  - Score d'opportunité
  - Revenus estimés
  - Coût d'investissement
  - ROI (Retour sur Investissement)
  - Délai de réalisation
  - Évaluation des risques

### 6. **Fonctionnalités avancées**

#### 🔄 Combinaison des couches
- [ ] Activez plusieurs couches simultanément
- [ ] Vérifiez que toutes s'affichent correctement
- [ ] Désactivez-les une par une

#### 🎯 Filtrage
- [ ] Testez différents filtres :
  - Zone spécifique
  - Population min/max
  - Relancez l'analyse
- [ ] Vérifiez que les résultats changent

## 🎯 Résultats attendus

### ✅ Si tout fonctionne :
- **Carte interactive** : Centrée sur Sandiara avec limites visibles
- **5 couches géographiques** : Toutes fonctionnelles avec données de test
- **Analyse complète** : 5 zones analysées avec scores détaillés
- **5 propositions** : Investissements recommandés avec ROI calculé
- **Interface responsive** : Fonctionne sur desktop et mobile

### ❌ Si problème :
- **Erreur JavaScript** : Vérifiez la console du navigateur (F12)
- **Carte ne charge pas** : Vérifiez la connexion internet
- **Données manquantes** : Les données de test sont intégrées, pas d'API nécessaire

## 🚀 Test rapide en 2 minutes

1. **Accédez** à http://127.0.0.1:8000/maire/geomarketing/advanced
2. **Cochez** "Routes" et "Bâti" pour voir les couches
3. **Cliquez** sur "Analyser les opportunités"
4. **Vérifiez** que les statistiques et propositions apparaissent
5. **Testez** la carte multi-critères

**🎉 Si ces 5 étapes fonctionnent, le système est opérationnel !**

---

**💡 Conseil** : Utilisez ce guide pour former les utilisateurs et vérifier le bon fonctionnement du système.



