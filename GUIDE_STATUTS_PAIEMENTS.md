# 📊 Guide des Statuts de Paiement - SICOSA

## 🎯 **Les 3 Statuts de Paiement**

### **1. 📋 PAIEMENTS EN ATTENTE**
**Statut :** `en_attente`

**Signification :**
- Le paiement a été enregistré mais n'est pas encore complet
- Il reste un montant à payer (`montant_restant > 0`)
- Le paiement est en cours de finalisation

**Caractéristiques :**
- ✅ Montant total défini
- ✅ Montant avance payé
- ⏳ Montant restant > 0
- 🔄 En attente de finalisation

**Actions possibles :**
- ✅ Finaliser le paiement (collecteurs/gestionnaires)
- ✅ Annuler le paiement (maires)
- ❌ Ne peut pas être comptabilisé

---

### **2. ✅ PAIEMENTS VALIDES**
**Statut :** `valide`

**Signification :**
- Le paiement est complet et finalisé
- Le montant total a été payé (`montant_restant = 0`)
- Le paiement peut être comptabilisé

**Caractéristiques :**
- ✅ Montant total défini
- ✅ Montant avance = montant total
- ✅ Montant restant = 0
- ✅ Paiement complet

**Actions possibles :**
- ✅ Comptabilisé dans les rapports
- ✅ Génération de reçu
- ✅ Historique définitif
- ❌ Ne peut plus être modifié

---

### **3. ⚠️ PAIEMENTS VALIDES PARTIELS**
**Statut :** `valide` mais avec `montant_restant > 0`

**Signification :**
- **PROBLÈME DANS LE SYSTÈME** - Ce statut ne devrait pas exister
- Un paiement ne peut pas être "valide" s'il reste un montant à payer
- Cela indique une incohérence dans les données

**Problème identifié :**
- ❌ Statut = "valide" mais montant restant > 0
- ❌ Logique contradictoire
- ❌ Peut causer des erreurs de comptabilité

---

## 🔧 **Logique Correcte du Système**

### **Création d'un Paiement :**
```php
// Si montant_restant = 0 (paiement complet)
'statut' => 'valide'

// Si montant_restant > 0 (paiement partiel)  
'statut' => 'en_attente'
```

### **Finalisation d'un Paiement :**
```php
// Après ajout d'un paiement supplémentaire
if ($nouveauMontantRestant <= 0) {
    'statut' => 'valide'  // Paiement complet
} else {
    'statut' => 'en_attente'  // Paiement encore partiel
}
```

## 🧪 **Test des Statuts**

### **Scénario 1 : Paiement Complet**
- Montant total : 10,000 FCFA
- Montant avance : 10,000 FCFA
- Montant restant : 0 FCFA
- **Statut attendu :** `valide`

### **Scénario 2 : Paiement Partiel**
- Montant total : 10,000 FCFA
- Montant avance : 3,000 FCFA
- Montant restant : 7,000 FCFA
- **Statut attendu :** `en_attente`

### **Scénario 3 : Finalisation**
- Paiement partiel de 3,000 FCFA sur 10,000 FCFA
- Ajout de 7,000 FCFA supplémentaires
- **Statut final :** `valide`

## 🔍 **Vérification des Données**

### **Script de Diagnostic :**
```sql
-- Paiements avec statut incohérent
SELECT id, montant, montant_avance, montant_restant, statut 
FROM paiements 
WHERE statut = 'valide' AND montant_restant > 0;

-- Paiements en attente corrects
SELECT id, montant, montant_avance, montant_restant, statut 
FROM paiements 
WHERE statut = 'en_attente' AND montant_restant > 0;

-- Paiements valides corrects
SELECT id, montant, montant_avance, montant_restant, statut 
FROM paiements 
WHERE statut = 'valide' AND montant_restant = 0;
```

## 🛠️ **Correction des Données Incohérentes**

Si vous voyez des "Paiements Valides Partiels", il faut les corriger :

```sql
-- Corriger les paiements avec statut incohérent
UPDATE paiements 
SET statut = 'en_attente' 
WHERE statut = 'valide' AND montant_restant > 0;
```

## 📋 **Résumé**

| Statut | Montant Restant | Signification | Actions |
|--------|----------------|---------------|---------|
| `en_attente` | > 0 | Paiement partiel | Finaliser ou Annuler |
| `valide` | = 0 | Paiement complet | Comptabiliser |
| `annule` | - | Paiement annulé | Aucune |

---

**Les "Paiements Valides Partiels" sont une anomalie qui doit être corrigée ! 🚨**
