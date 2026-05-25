# Documentation Technique CITYPLAY

Ce document explique les étapes de développement réalisées pour mettre en place la plateforme de jeu urbain CITYPLAY, avec un focus particulier sur le système de cartographie et de géolocalisation.

---

## 1. Architecture des Données (Backend)

La première étape a consisté à traduire le brainstorming en une structure de base de données robuste sous Laravel.

- **Migrations** : Création des tables `cities`, `locations`, `enigmas`, `teams`, `game_sessions`, et `enigma_responses`.
- **Modèles & Relations** : 
    - Un utilisateur peut être `mairie` ou `joueur`.
    - Une `City` possède plusieurs `Locations`.
    - Chaque `Location` contient des `Enigmas`.
    - Les sessions de jeu (`GameSession`) suivent la progression, les points (pièces 🪙) et la santé (cœurs ❤️).
- **Seeders Béninois** : Configuration de données réelles pour **Cotonou**, **Ouidah** et **Porto-Novo** avec des coordonnées GPS précises (ex: Place de l'Amazone, Porte du Non-Retour).

---

## 2. Design et Identité Visuelle

L'interface a été conçue pour un rendu "Gaming Professionnel" en utilisant **Tailwind CSS**.

- **Palette de couleurs** : Utilisation d'un Bleu électrique et d'un Vert vibrant sur un fond sombre (`gaming-dark`).
- **Layout Adaptatif** : Mise en place d'un système Mobile-First. Sur mobile, la navigation se fait via une barre basse (Bottom Nav) pour une ergonomie optimale avec le pouce.

---

## 3. Système de Cartographie et GPS (Le Cœur du Projet)

C'est la partie la plus complexe, utilisant **Leaflet.js** et l'API **Geolocation** du navigateur.

### A. Intégration de la Carte
Nous utilisons le composant [MapComponent.vue](file:///c:/Users/HP/Documents/laravel/CityPlay/resources/js/Components/MapComponent.vue).
1. **Initialisation** : La carte est chargée avec un fond "Dark Mode" (CartoDB Dark Matter) pour correspondre au thème.
2. **Markers de Lieux** : Chaque lieu est représenté par un cercle bleu avec un rayon de validation (ex: 50m ou 100m).
3. **Marker Joueur** : Un point blanc avec une animation "pulse" bleue indique la position réelle du joueur.

### B. Géolocalisation en Temps Réel
Dans [Game.vue](file:///c:/Users/HP/Documents/laravel/CityPlay/resources/js/Pages/Player/Game.vue), nous utilisons `navigator.geolocation.watchPosition()`.
- **Suivi continu** : L'application met à jour les coordonnées du joueur dès qu'il se déplace.
- **Calcul de Distance (Haversine)** : Une fonction calcule mathématiquement la distance entre le joueur et le lieu le plus proche.
    ```javascript
    // Exemple du calcul utilisé
    const d = calculateDistance(userLat, userLng, locLat, locLng);
    ```

### C. Mécanique de Validation
- **Zone de Proximité** : Si la distance calculée est inférieure au rayon du lieu (ex: 50m), la variable `isNearLocation` passe à `true`.
- **Feedback Visuel** : Sur mobile, le bouton de validation s'anime (bounce) et devient vert pour indiquer au joueur qu'il peut débloquer l'énigme.

---

## 4. Expérience Utilisateur Mobile

L'application a été optimisée pour être utilisée "sur le terrain" :
- **Mode Plein Écran** : Le layout occupe 100% de la hauteur du téléphone pour éviter les défilements inutiles.
- **Navigation par Onglets** : 
    - **Carte** : Pour se diriger.
    - **Énigmes** : Pour répondre une fois arrivé.
    - **Sac** : Pour voir son inventaire et ses points.
- **Alertes Dynamiques** : Affichage de la distance restante en temps réel en haut de l'écran.

---

## 5. Comment tester ?

1. Connectez-vous avec un compte joueur (ex: `jean@player.com` / `password`).
2. Choisissez une ville béninoise (Cotonou, Ouidah ou Porto-Novo).
3. Sur la carte, vous verrez votre position (si vous autorisez le GPS).
4. Pour simuler une arrivée sur un lieu (si vous n'êtes pas physiquement au Bénin), vous pouvez modifier temporairement les coordonnées dans le seeder ou utiliser les outils de développement du navigateur pour "simuler" une position GPS.

---
*Documentation générée par l'Assistant CityPlay - Mai 2026*
