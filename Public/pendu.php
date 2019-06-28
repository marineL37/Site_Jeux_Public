<?php

/*
    Fichier permettant de gérer le jeu du Pendu du site Mini-jeux.
*/

// On charge les fichiers nécessaires
require_once '../src/Classes/Utilisateur.class.php';
require_once '../src/Classes/PageWeb.class.php';
require_once '../src/Classes/BaseDeDonnees.class.php';
require_once '../src/Classes/Jeux/Pendu.class.php';



// On démarre la session
$session = new Utilisateur();
$session->verrou(); // On vérrouille la page pour la rendre accessible qu'après connexion

// On se connecte à la base de donnée
$connexion = BaseDeDonnees::constructeur();

// On crée l'objet issu de la classe PageWeb qui contient les méthodes utiles à l'affichage des 
// différents messages du jeu du pendu
$page = new PageWeb("Le Pendu");

// On crée l'objet jeu du pendu qui contient les méthodes de traitement du jeu
$jeuPendu = new Pendu();

// Si le joueur fait une saisie on gère le jeu, affiche les messages et si la partie est finie 
// on met à jour les scores dans base de donnée
if( filter_has_var(INPUT_POST, "lettre") )
{
    $jeuPendu->propositionJoueur($page, $connexion);
}







