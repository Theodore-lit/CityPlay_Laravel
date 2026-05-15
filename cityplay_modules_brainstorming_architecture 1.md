# CITYPLAY — Brainstorming des modules du projet

## Équipe
- Théodore
- Christelle
- Kamal

---

# Vision du projet

Cityplay est une plateforme de découverte de ville sous forme de jeu.

Le système permet à une mairie ou un organisateur :
- de créer une ville,
- d’ajouter des lieux,
- de créer des énigmes,
- d’inviter des joueurs,
- de suivre les parties,
- et de proposer une expérience immersive basée sur la géolocalisation.

---

# Architecture globale des modules

Le projet peut être découpé en 10 grands modules.

---

# 1. MODULE AUTHENTIFICATION & SÉCURITÉ

## Objectif
Gérer les accès et sécuriser les comptes.

## Fonctionnalités
- Inscription
- Connexion
- Déconnexion
- Réinitialisation mot de passe
- Double authentification
- Vérification email
- Gestion des sessions
- Gestion des rôles
- Expiration des liens d’invitation
- Suppression de compte
- Conservation des données selon la mairie

## Rôles possibles
- Super Admin
- Mairie
- Organisateur
- Joueur
- Challenger
- Coéquipier

---

# 2. MODULE GESTION DES VILLES

## Objectif
Créer et gérer les villes jouables.

## Fonctionnalités
- Création d’une ville
- Modification d’une ville
- Activation / désactivation
- Gestion de la période du jeu
- Définition des horaires de jeu
- Gestion des saisons / événements
- Image de couverture
- Description de la ville
- Gestion des zones interdites
- Gestion des zones hors jeu

## Exemple
- Ville disponible uniquement de 18h à 23h
- Mode spécial Halloween
- Jeu disponible uniquement pendant un festival

---

# 3. MODULE GESTION DES LIEUX

## Objectif
Gérer les points à découvrir.

## Fonctionnalités
- Création des lieux
- Classement des lieux
- Géolocalisation GPS
- Ajout de photos
- Description des lieux
- Niveau de popularité
- Temps moyen pour atteindre le lieu
- Catégories des lieux
- Validation GPS
- Rayon de validation GPS
- Statut du lieu

## Catégories possibles
- Historique
- Restaurant
- Monument
- Culture
- Mystère
- Nature

---

# 4. MODULE ÉNIGMES

## Objectif
Créer les mécanismes de jeu.

## Fonctionnalités
- Création d’énigmes
- Niveau de difficulté
- Gestion des indices
- Réponses possibles
- Validation des réponses
- Images des énigmes
- Audio ou vidéo plus tard
- Gestion du temps limite
- Gestion des récompenses
- Gestion des pénalités
- Génération aléatoire

## Modes de jeu

### Mode Devinette
Le joueur résout uniquement les énigmes.

### Mode Aventure
Le joueur suit une histoire avec progression.

## Types d’enchaînement

### Mode Aléatoire
Les énigmes sont choisies automatiquement.

### Mode Lié
Une énigme débloque la suivante.

## Système de récompense

### Difficulté difficile
- 1 cœur

### Difficulté moyenne
- 2 pièces

### Difficulté facile
- 1 pièce

## Boutique d’indices
- 3 cœurs = achat d’un indice

---

# 5. MODULE PARTIES & SESSIONS DE JEU

## Objectif
Gérer les parties en temps réel.

## Fonctionnalités
- Création de partie
- Pause de partie
- Reprise de partie
- Fin de partie
- Sauvegarde automatique
- Chronomètre
- Gestion du temps restant
- Historique des parties
- Reconnexion automatique
- Gestion des abandons
- Gestion des hors jeu
- Détection d’inactivité

## Gestion hors jeu

Cas possibles :
- joueur trop loin du périmètre,
- triche GPS,
- sortie de zone,
- temps dépassé,
- lieu inaccessible.

Actions possibles :
- avertissement,
- nouvelle énigme,
- repositionnement,
- pénalité.

---

# 6. MODULE ÉQUIPES & MULTIJOUEUR

## Objectif
Permettre les jeux en solo ou en équipe.

## Fonctionnalités
- Création d’équipe
- Invitation de membres
- Gestion des rôles dans l’équipe
- Challenger ou coéquipier
- Limite du nombre de membres
- Chat d’équipe plus tard
- Suivi des scores d’équipe
- Validation collaborative
- Synchronisation des joueurs

## Types de participation

### Solo
Le joueur joue seul.

### Équipe
Les joueurs collaborent.

### Challenger
Le joueur joue contre les autres.

---

# 7. MODULE GÉOLOCALISATION & CARTE

## Objectif
Faire fonctionner le jeu dans le monde réel.

## Fonctionnalités
- GPS temps réel
- Détection de proximité
- Carte interactive
- Affichage des lieux
- Calcul de distance
- Itinéraire
- Validation automatique de présence
- Gestion du rayon GPS
- Gestion des déplacements
- Anti-triche GPS

## Technologies possibles
- Leaflet
- OpenStreetMap
- Google Maps API

---

# 8. MODULE NOTIFICATIONS & COMMUNICATION

## Objectif
Communiquer avec les joueurs.

## Fonctionnalités
- Envoi de liens d’invitation
- Notifications push
- SMS
- WhatsApp
- Email
- Alertes temps restant
- Alertes nouvelle énigme
- Confirmation de victoire
- Messages système
- Message de fin de partie

---

# 9. MODULE ANALYTICS & STATISTIQUES

## Objectif
Permettre à la mairie d’analyser les parties.

## Fonctionnalités
- Nombre de joueurs
- Nombre de parties
- Temps moyen des parties
- Énigmes les plus difficiles
- Zones les plus visitées
- Taux d’abandon
- Score moyen
- Heatmap des déplacements
- Statistiques des équipes
- Historique des performances

---

# 10. MODULE ADMINISTRATION GÉNÉRALE

## Objectif
Piloter toute la plateforme.

## Fonctionnalités
- Dashboard global
- Gestion des utilisateurs
- Gestion des villes
- Gestion des parties
- Gestion des contenus
- Gestion des sanctions
- Gestion des sauvegardes
- Paramètres généraux
- Logs système
- Monitoring
- Permissions

---

# MÉCANIQUES IMPORTANTES DU JEU

## Progression dynamique

Si le joueur échoue :
- le niveau baisse automatiquement.

Si le joueur réussit vite :
- le niveau augmente.

---

# Économie du jeu

## Ressources
- Cœurs
- Pièces
- Badges
- Classement

## Dépenses possibles
- Acheter des indices
- Débloquer des énigmes
- Temps supplémentaire

---

# IDÉES FUTURES

## Mode IA
- Génération automatique d’énigmes
- Adaptation automatique de la difficulté

## Réalité augmentée
- Objets cachés dans la ville
- Personnages historiques

## Système de saisons
- Événements temporaires
- Énigmes exclusives

## Mode compétition mondiale
- Classement global
- Tournois entre villes

---

# STACK TECHNIQUE CONSEILLÉE

## Frontend
- Vue.js
- Tailwind CSS
- Leaflet

## Backend
- Laravel
- API REST

## Dashboard
- Filament

## Base de données
- MySQL

## Temps réel
- Laravel Reverb ou Socket.io

## Notifications
- Firebase Cloud Messaging

---

# PRIORITÉ MVP (VERSION 1)

## À développer d’abord

### Phase 1
- Authentification
- Création villes
- Création lieux
- Création énigmes
- Géolocalisation
- Validation GPS

### Phase 2
- Équipes
- Sessions de jeu
- Pause/reprise
- Système de points

### Phase 3
- Notifications
- Analytics
- Anti-triche
- Dashboard avancé

---

# RÉPARTITION EN 3 GRANDS PÔLES

Pour mieux organiser le travail de l’équipe et éviter la confusion pendant le développement, les 10 modules peuvent être regroupés en 3 grands pôles principaux.

---

# PÔLE 1 — ADMINISTRATION & GESTION DU JEU

## Objectif
Tout ce qui permet à la mairie ou aux administrateurs de créer, piloter et surveiller le jeu.

## Modules inclus

### 1. Module Administration Générale
- Dashboard
- Permissions
- Paramètres
- Monitoring
- Logs

### 2. Module Gestion des Villes
- Création des villes
- Gestion des périodes
- Zones hors jeu
- Activation/désactivation

### 3. Module Gestion des Lieux
- Création des lieux
- Classement
- GPS
- Photos
- Catégories

### 4. Module Énigmes
- Création des énigmes
- Difficultés
- Indices
- Récompenses
- Modes aventure/devinette
- Modes aléatoire/liés

### 5. Module Analytics & Statistiques
- Performances
- Heatmaps
- Taux d’abandon
- Zones populaires
- Statistiques des joueurs

## Responsable idéal
- Théodore

## Technologies principales
- Laravel
- Filament
- MySQL

---

# PÔLE 2 — EXPÉRIENCE JOUEUR & MULTIJOUEUR

## Objectif
Tout ce qui concerne les joueurs et leur expérience pendant la partie.

## Modules inclus

### 1. Module Authentification & Sécurité
- Inscription
- Connexion
- Double authentification
- Gestion profils
- Invitations

### 2. Module Équipes & Multijoueur
- Création d’équipe
- Invitations joueurs
- Challenger/coéquipier
- Synchronisation équipe

### 3. Module Parties & Sessions
- Création partie
- Pause/reprise
- Sauvegarde
- Gestion du temps
- Hors jeu
- Historique

### 4. Module Notifications & Communication
- Notifications push
- SMS
- WhatsApp
- Emails
- Alertes système

## Responsable idéal
- Christelle

## Technologies principales
- Vue.js
- Tailwind CSS
- Firebase

---

# PÔLE 3 — GÉOLOCALISATION & MOTEUR INTELLIGENT

## Objectif
Faire fonctionner le gameplay dans le monde réel avec les déplacements et la logique intelligente.

## Modules inclus

### 1. Module Géolocalisation & Carte
- GPS temps réel
- Carte interactive
- Calcul de distance
- Validation de présence
- Détection de proximité

### 2. Moteur Intelligent du Jeu
- Attribution des énigmes
- Calcul du temps
- Adaptation difficulté
- Gestion des récompenses
- Détection anti-triche
- Gestion des hors jeu

### 3. Système Économique
- Pièces
- Cœurs
- Achat d’indices
- Récompenses

## Responsable idéal
- Kamal

## Technologies principales
- Leaflet
- OpenStreetMap
- API GPS
- Algorithmes backend Laravel

---

# AVANTAGE DE CETTE ORGANISATION

## 1. Meilleure répartition des tâches
Chaque membre possède un domaine précis.

---

## 2. Développement parallèle
Les 3 pôles peuvent être développés simultanément.

---

## 3. Moins de conflits techniques
Chaque partie devient plus indépendante.

---

## 4. Plus facile à maintenir
Le projet reste organisé même lorsqu’il grossit.

---

## 5. Vision startup plus professionnelle
Cette séparation ressemble à une vraie architecture produit utilisée dans les startups.

---

# STRUCTURE LOGIQUE GLOBALE

MAIRIE
↓
CRÉE UNE VILLE
↓
AJOUTE DES LIEUX
↓
CRÉE DES ÉNIGMES
↓
INVITE DES JOUEURS
↓
LES JOUEURS CRÉENT UNE ÉQUIPE
↓
LE SYSTÈME GÉNÈRE UNE SESSION
↓
ENVOI DES ÉNIGMES
↓
VALIDATION GPS
↓
RÉCOMPENSES / INDICES
↓
FIN DE PARTIE
↓
STATISTIQUES & ANALYTICS

