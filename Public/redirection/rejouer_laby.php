<?php

/*
    Cette page permet de réinitialiser les $_SESSION[] utiles au jeu du labyrinthe.
*/

// On charge le fichier nécessaire
require_once '../../src/Classes/Utilisateur.class.php';

// On démmare la session pour mettre à jour la $_SESSION[] et on redirige vers la page du labyrinthe.
$session = new Utilisateur();
// On repasse à "null" toutes les valeurs de la $_SESSION concernant le labyrinthe
$_SESSION["labyNbLigne"] = null;
$_SESSION["labyNbColonne"] = null;
$_SESSION['listeCasesVisibles'] = [];
$_SESSION["carte"] = null;
$_SESSION['carteSolution'] = null;
$_SESSION["finDePartieLaby"] = false;

// On redirige vers le jeu du labyrinthe
header("Location: ../labyrinthe.php");
exit;