<?php
/*
    Cette page permet d'afficher le labyrinthe complet, fait perdre un point et met à jour la base de données
*/

// On charge les fichiers nécessaires
require_once '../src/Classes/Utilisateur.class.php';
require_once '../src/Classes/PageWeb.class.php';
require_once '../src/Classes/BaseDeDonnees.class.php';
require_once '../src/Classes/Jeux/Labyrinthe.class.php';

// On démarre la session
$session = new Utilisateur();
$session->verrou(); // On vérrouille la page pour la rendre accessible qu'après connexion

// On se connecte à la base de donnée pour mettre à jour les scores
$connexion = BaseDeDonnees::constructeur();

// On crée la page à afficher
$page = new PageWeb("Solution du labyrinthe");

// On fait perdre un point au joueur
$_SESSION["utilisateurActuel"]["laby_nb_perdu"] += 1;

// On met à jour la base de donnée
$connexion->requeteMajUtilisateurScoreLaby($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["laby_nb_gagne"], $_SESSION["utilisateurActuel"]["laby_nb_perdu"]); 

// On affiche la solution
$page->labyAfficheCarte($_SESSION['carteSolution']);

// On affiche le bouton rejouer
$page->labyAfficheRejouer();

// On termine la partie
$_SESSION["finDePartieLaby"] = false;
