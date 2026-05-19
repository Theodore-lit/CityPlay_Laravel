# Journal de Progression - CityPlay

Ce document sert de guide pour suivre les travaux effectués sur le projet.

## 1. Système de Quiz et Devinettes (Niveaux de Difficulté)
L'objectif est de permettre aux joueurs de répondre à des quiz par ville, avec une progression par niveau.

### Fonctionnalités implémentées :
- **Sélection de Ville** : Le joueur choisit d'abord sa ville.
- **Niveaux de Progression** : 
    - **Facile** : Ouvert par défaut.
    - **Moyen** : Bloqué (nécessite de réussir tous les quiz du niveau Facile).
    - **Difficile** : Bloqué (nécessite de réussir tous les quiz du niveau Moyen).
- **Interface QuizLobby** : Création d'une interface avec onglets pour les niveaux, affichage des scores et badges de réussite.
- **Logique de Jeu (Quiz)** : 
    - Implémentation du système de **5 vies** au démarrage.
    - Gain de **100 XP** pour chaque bonne réponse trouvée.
    - **Barre de Navigation Dynamique** : L'XP et les vies s'actualisent en temps réel dans la barre de navigation pendant le quiz.
    - Arrêt automatique du décompteur à la fin de la 5ème question.
- **Contrôleur Dynamique** : Le `PlayerController` calcule désormais en temps réel quels niveaux sont débloqués en fonction des résultats passés du joueur.
- **Base de Données Corrigée** : Ajout de la colonne `difficulty` dans la table `quizzes` pour permettre la catégorisation (Facile, Moyen, Difficile).

---
*Dernière mise à jour : 18 Mai 2026*
