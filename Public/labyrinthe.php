<?php
/*
    Cette page gère et affiche le jeu du labyrinthe
*/

// On charge les fichiers nécessaires
require_once '../src/Classes/Utilisateur.class.php';
require_once '../src/Classes/PageWeb.class.php';
require_once '../src/Classes/BaseDeDonnees.class.php';
require_once '../src/Classes/Jeux/Labyrinthe.class.php';
require_once '../src/Classes/Jeux/UneCase.class.php';
require_once '../src/Classes/Jeux/Robot.class.php';




// On démarre la session
$session = new Utilisateur();
$session->verrou(); // On vérrouille la page pour la rendre accessible qu'après connexion

// On se connecte à la base de donnée pour mettre à jour les scores
$connexion = BaseDeDonnees::constructeur();

// On crée la page à afficher
$page = new PageWeb("Labyrinthe");

// S'il le joueur saisie les dimensions et la difficulté du labyrinthe
if( filter_has_var(INPUT_POST, "choix_laby_nbLigne") 
and filter_has_var(INPUT_POST, "choix_laby_nbColonne") 
and filter_has_var(INPUT_POST, "choix_laby_difficulte") )
{
    // On gère le cas par défaut, si rien n'est saisi on envoie les valeurs minimum
    if( $_POST["choix_laby_nbLigne"] === "" or $_POST["choix_laby_nbColonne"] === "" )
    {
        $_SESSION["labyNbLigne"] = 10;
        $_SESSION["labyNbColonne"] = 10;        
    }

    else
    {
        // On stocke dans la $_SESSION le nombre de ligne et de colonne saisies
        $_SESSION["labyNbLigne"] =  intval($_POST["choix_laby_nbLigne"]);
        $_SESSION["labyNbColonne"] = intval($_POST["choix_laby_nbColonne"]);
    }

    // On créé l'objet laby qui construit la carte aux dimensions choisies
    $laby = new Labyrinthe();
    // On génère alétoirement le labyrinthe en passant en parametre la difficulte choisie par l'utilisateur
    $laby->labyAleatoire(intval($_POST["choix_laby_difficulte"]));
    // On stocke la carte sous forme d'un tableau de tableau de caractères [[OOOOOOOOOO], [0  0       0], [0  0       0] ...]
    $_SESSION['carte'] = $laby->getLabyTableau();
    // On stocke la solution dans la session
    $_SESSION['carteSolution'] = $laby->solution();    
}

// Si le joueur à choisi un labyrinthe
if( isset($_SESSION["labyNbLigne"]) and isset($_SESSION["labyNbColonne"]) )
{
    // On créé l'objet laby qui construit le labyrinthe
    $laby = new Labyrinthe();
    // On déplace le robot en fonction du déplacement choisi par l'utilisateur (voir labyrinthe.js)
    $laby->deplacerRobot($page, $connexion);
    
    // Si la partie est finie, on récupère l'intégralité du labyrinthe
    if( $_SESSION["finDePartieLaby"] === true )
    {
        $laby->obtenirLabyComplet();
    }

    // Dans tous les cas on affiche le labyrinthe
    $page->labyAfficheCarte($laby->conversionLabyObjetVersImage());
}


