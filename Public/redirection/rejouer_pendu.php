<?php

/*
    Fichier permettant de gérer la page d'accueil du site Mini-jeux.
*/

// On charge le fichier nécessaire
require_once '../../src/Classes/Utilisateur.class.php';


// On démmare la session pour mettre à jour la $_SESSION["rejouer"] et on redirige vers la page du pendu.
$session = new Utilisateur();
$_SESSION["rejouer"] = true;

header("Location: ../pendu.php");
exit;
