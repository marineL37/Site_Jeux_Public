<?php

/*
    Fichier permettant de gérer la page d'accueil du site Mini-jeux.
*/

// On charge les fichiers nécessaires
require_once '../src/Classes/Utilisateur.class.php';
require_once '../src/Classes/PageWeb.class.php';
require_once '../src/Classes/BaseDeDonnees.class.php';

// On démarre la session
$session = new Utilisateur();

// On affiche la page
$page = new PageWeb("Accueil");
