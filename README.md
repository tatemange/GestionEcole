# Eco-Note - Gestion Scolaire

Application web de gestion de notes et bulletins scolaires.

## Installation

1.  **Pré-requis** : Serveur LAMP/WAMP/XAMPP (PHP 7.4+, MySQL).
2.  **Base de données** :
    *   Créer une base de données nommée `eco_note`.
    *   Importer le fichier `database.sql`.
    *   *Note* : Le script crée automatiquement un compte administrateur.

3.  **Configuration** :
    *   Vérifier les paramètres dans `config/db.php`.

## Connexion (Compte par défaut)

Pour la première connexion, utilisez le compte administrateur par défaut :

*   **Email** : `admin@eco.school`
*   **Mot de passe** : `admin123`

> **IMPORTANT** : Une fois connecté, veuillez créer de nouveaux comptes pour vos professeurs et changer le mot de passe de cet administrateur ou créer un nouvel administrateur personnel.

## Fonctionnalités

*   **Gestion des Élèves** : Inscription, Classes.
*   **Gestion des Matières** : Coefficients, Association aux classes.
*   **Notes** : Saisie par classe/matière/trimestre.
*   **Bulletins** : Génération automatique avec calcul de moyenne, rang et mention.
*   **Utilisateurs** : Gestion des droits (Admin/Professeur).
