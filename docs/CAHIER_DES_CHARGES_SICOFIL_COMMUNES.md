# Cahier des charges — SICOFIL / Systèmes par commune — Gestion des communes et interfaces

## 1. Contexte et objectifs

### 1.1 Nom du système selon la commune

- **SICOSA** = nom du système **pour la commune de Sandiara** uniquement.  
  **SICOSA** = *Système d’Information Commerciale de **Sandiara*** (fiscalité locale, unités commerciales, paiements, zones, etc.).

- **Pour une autre commune, le système porte un autre nom.**  
  Exemple : pour la **commune de Thiès**, le même type de système pourrait s’appeler **SICOTH** (*Système d’Information Commerciale de **Thiès***).  
  Autre exemple : pour une commune « Mbour », on pourrait avoir **SICOMB**, etc.

- En résumé : **chaque commune a son propre nom / acronyme** pour le système (SICOSA pour Sandiara, SICOTH pour Thiès, etc.). C’est le **même type** de système (information commerciale, fiscalité locale), mais le **nom affiché** dépend de la commune.

### 1.2 SICOFIL (plateforme multi-communes)

- **SICOFIL** : plateforme « multi-communes » qui permet à un administrateur national de gérer **toutes** les communes (Sandiara, Thiès, etc.) depuis une seule interface.  
  C’est le niveau au-dessus : on ne parle plus de SICOSA ou SICOTH, mais de la **gestion globale** des communes.

- **Objectif** : une même application technique sert à la fois  
  - les **communes** (chacune voit **son** nom : SICOSA, SICOTH, etc., et **ses** données uniquement),  
  - et l’**administrateur SICOFIL** (voit toutes les communes, interface SICOFIL).  
  Les communes ne voient pas l’interface SICOFIL ni les données des autres communes.

---

## 2. Rôles et périmètres

### 2.1 Admin SICOFIL (gestion multi-communes)

- **Qui** : un (ou quelques) compte(s) administrateur(s) plateforme.
- **Voit** :
  - Interface dédiée SICOFIL : tableau de bord multi-communes, liste des communes, carte globale, ajout de commune, etc.
  - Toutes les communes et leurs données agrégées (recettes, unités, utilisateurs, etc.).
- **Peut faire** :
  - Ajouter / modifier / désactiver une commune.
  - Voir les statistiques et rapports de toutes les communes.
  - Accéder à la carte multi-communes.
  - Créer des utilisateurs (Maire, Gestionnaire, Collecteur) **pour une commune donnée**, afin que cette commune puisse utiliser son interface propre.
- **Ne doit pas** : être confondu avec un rôle « commune » (Maire, Gestionnaire, Collecteur).

### 2.2 Maire (par commune)

- **Qui** : un ou plusieurs comptes par commune, rattachés à **une seule commune**.
- **Voit** :
  - Interface « Maire » de **sa** commune : le nom affiché est celui du système de cette commune (ex. **SICOSA** pour Sandiara, **SICOTH** pour Thiès) — tableau de bord, utilisateurs, zones, taxes, logs, etc.
  - **Uniquement les données de sa commune** (sa commune_id).
- **Ne voit pas** :
  - L’interface SICOFIL (Dashboard SICOFIL, Gestion multi-communes, liste des autres communes, etc.).
  - Les données (utilisateurs, unités, paiements, zones) des autres communes.

### 2.3 Gestionnaire (par commune)

- **Qui** : un ou plusieurs comptes par commune, rattachés à **une seule commune**.
- **Voit** :
  - Interface « Gestionnaire » de **sa** commune (nom du système = SICOSA, SICOTH, etc. selon la commune) : dashboard, collecteurs, zones, unités, taxes, paiements, carte, logs, etc.
  - **Uniquement les données de sa commune**.
- **Ne voit pas** :
  - L’interface SICOFIL.
  - Les données des autres communes.

### 2.4 Collecteur (par commune)

- **Qui** : comptes rattachés à une commune et éventuellement à une zone de cette commune.
- **Voit** :
  - Interface « Collecteur » de **sa** commune (nom du système = SICOSA, SICOTH, etc. selon la commune) : tableau de bord, paiements, sa zone, etc.
  - **Uniquement les données de sa commune** (et éventuellement limité à sa zone).
- **Ne voit pas** :
  - L’interface SICOFIL.
  - Les données des autres communes ou des autres zones (selon règles métier).

---

## 3. Règles fonctionnelles à respecter

### 3.1 Séparation des interfaces

- Les liens et menus « SICOFIL » (Dashboard SICOFIL, Gestion multi-communes, Carte multi-communes, Ajouter une commune, etc.) doivent être **affichés uniquement** aux utilisateurs ayant le rôle **Admin SICOFIL**.
- Les utilisateurs **Maire**, **Gestionnaire** et **Collecteur** ne doivent **jamais** voir ces entrées de menu ni pouvoir y accéder (même en tapant l’URL).

### 3.2 Isolement des données par commune

- Pour tout utilisateur **Maire**, **Gestionnaire** ou **Collecteur**, les écrans et les requêtes (utilisateurs, zones, unités commerciales, taxes, paiements, rapports, etc.) doivent **ne porter que sur sa commune** (commune_id de l’utilisateur connecté).
- Aucune donnée d’une autre commune ne doit être listée, exportée ou visible (tableaux, cartes, graphiques, rapports).

### 3.3 Protection des routes

- Les URLs du type **/multi-communes/** (tableau de bord, liste des communes, carte, ajout/édition de commune, etc.) doivent être **réservées au rôle Admin SICOFIL** (contrôle d’accès côté serveur).
- Si un Maire, Gestionnaire ou Collecteur tente d’accéder à ces URLs, l’application doit **refuser l’accès** (ex. erreur 403 ou redirection vers son tableau de bord).

### 3.4 Nouvelle commune ajoutée par Admin SICOFIL

- Lorsque l’Admin SICOFIL **ajoute une nouvelle commune** (nom, code, région, logo, etc.) :
  - La commune apparaît dans la liste des communes côté SICOFIL.
  - Pour que quelqu’un puisse **se connecter en tant que cette commune**, il faut qu’au moins **un utilisateur** (Maire ou Gestionnaire) soit **créé et affecté à cette commune**.
- Idée de flux recommandé :
  1. Admin SICOFIL crée la commune.
  2. Admin SICOFIL crée au moins un utilisateur (Maire ou Gestionnaire) pour cette commune (identifiant, mot de passe, rôle, commune = cette commune).
  3. Ce compte peut ensuite se connecter via la page d’accueil (ex. « Accéder par commune » → choix de la commune → Maire ou Gestionnaire) et ne voit que **l’interface SICOSA de son rôle** et **les données de sa commune**.

### 3.5 Portail d’accueil et « Accéder par commune »

- La page d’accueil peut proposer :
  - Un accès **par rôle** : Maire, Gestionnaire, Collecteur, Admin SICOFIL (chaque lien mène au bon formulaire de connexion).
  - Un accès **par commune** : liste des communes (ex. Sandiara, Nouvelle commune, etc.) ; cliquer sur une commune ouvre une page dédiée (logo, nom) avec les boutons Maire / Gestionnaire / Collecteur pour **cette** commune. Après connexion, l’utilisateur ne voit que l’interface de son rôle et les données de **cette** commune.
- Les communes ne doivent **pas** voir un menu ou un lien « SICOFIL » ou « Gestion multi-communes » une fois connectées.

---

## 4. Exigences techniques (à mettre en œuvre, sans imposer de code ici)

### 4.1 Contrôle d’accès (routes)

- Toutes les routes sous un préfixe « multi-communes » (ou équivalent Admin SICOFIL) doivent être protégées par un mécanisme qui vérifie que l’utilisateur connecté a le rôle **Admin SICOFIL**. Sinon : refus d’accès ou redirection.

### 4.2 Menus et navigation

- La construction du menu (navbar, sidebar) doit dépendre du **rôle** de l’utilisateur :
  - **Admin SICOFIL** : afficher uniquement les liens SICOFIL (tableau de bord SICOFIL, gestion multi-communes, carte globale, etc.).
  - **Maire, Gestionnaire, Collecteur** : afficher uniquement les liens de l’interface SICOSA correspondant à leur rôle (dashboard, utilisateurs/collecteurs, zones, unités, taxes, paiements, carte, logs, etc.) et **ne jamais** afficher les liens SICOFIL.

### 4.3 Filtrage des données (scope commune)

- Pour les rôles Maire, Gestionnaire, Collecteur, toutes les requêtes qui chargent des données « métier » (utilisateurs, zones, unités commerciales, taxes, paiements, rapports, etc.) doivent **filtrer** par la **commune_id** de l’utilisateur connecté (ou par zone appartenant à cette commune pour le Collecteur, si applicable).
- Idéalement, ce filtrage est centralisé (ex. scope global sur les modèles ou dans une couche commune) pour éviter d’afficher par erreur des données d’une autre commune.

### 4.4 Création d’utilisateurs pour une commune

- L’Admin SICOFIL doit pouvoir, depuis l’interface SICOFIL (par exemple depuis la fiche d’une commune), **créer un utilisateur** (Maire, Gestionnaire ou Collecteur) en précisant :
  - La **commune** concernée (celle pour laquelle on crée le compte).
  - Le **rôle** (Maire, Gestionnaire, Collecteur).
  - Les informations du compte (identifiant, mot de passe, nom, etc.).
  - Pour un Collecteur : éventuellement la zone (de cette commune).
- Ainsi, chaque commune peut avoir ses propres comptes et n’accéder qu’à son interface et ses données.

---

## 5. Récapitulatif des idées clés

| Qui                    | Nom du système / interface visible   | Données visibles        |
|------------------------|--------------------------------------|--------------------------|
| Admin SICOFIL          | **SICOFIL** (multi-communes, carte, etc.) | Toutes les communes  |
| Maire (Sandiara)       | **SICOSA** Maire                     | Uniquement Sandiara      |
| Maire (Thiès)          | **SICOTH** Maire (ex.)               | Uniquement Thiès        |
| Gestionnaire (commune A) | Interface de la commune (SICOSA, SICOTH, etc.) Gestionnaire | Uniquement commune A |
| Collecteur (commune A) | Interface de la commune (SICOSA, SICOTH, etc.) Collecteur    | Uniquement commune A |

- **SICOSA** = système pour **Sandiara** ; **SICOTH** = exemple pour **Thiès** ; chaque autre commune peut avoir son propre nom (SICOMB, etc.).
- **Les communes ne voient pas l’interface SICOFIL.**
- **Les communes ne voient pas les autres communes.**
- **Chaque commune voit uniquement son interface (sous le nom de son système : SICOSA, SICOTH, etc.) et ses propres données.**
- **Quand l’Admin SICOFIL ajoute une commune, il doit pouvoir créer des utilisateurs pour cette commune afin qu’elle puisse se connecter et utiliser son interface (sous le nom prévu pour cette commune).**

---

## 6. Évolutions possibles (hors périmètre strict)

- Gestion des logos par commune et lien « Accéder par commune » avec logo.
- Messages ou encarts d’aide sur la page d’accueil pour expliquer la différence entre « Connexion par rôle » et « Accéder par commune ».
- Historisation des actions Admin SICOFIL (qui a ajouté quelle commune, quel utilisateur, etc.).

---

*Document : cahier des charges fonctionnel SICOFIL / communes — idées et exigences, sans spécification de code.*
