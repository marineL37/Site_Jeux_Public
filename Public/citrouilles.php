<?php

/*
    Fichier permettant de gérer le jeu du Pendu du site Mini-jeux.
*/

// On charge les fichiers nécessaires
require_once '../src/Classes/Utilisateur.class.php';
require_once '../src/Classes/PageWeb.class.php';


// On démarre la session
$session = new Utilisateur();
$session->verrou(); // On vérrouille la page pour la rendre accessible qu'après connexion

// On crée l'objet issu de la classe PageWeb qui contient les méthodes utiles à l'affichage des différents messages du jeu du pendu
$page = new PageWeb("Le tape citrouille");

