<?php

/*
    Fichier permettant de gérer la page d'inscription du site Mini-jeux.
*/

// On charge les fichiers nécessaires
require_once '../src/Classes/Utilisateur.class.php';
require_once '../src/Classes/PageWeb.class.php';
require_once '../src/Classes/BaseDeDonnees.class.php';


// On démarre la session
$session = new Utilisateur();

// On affiche la page
$page = new PageWeb("Connexion");

// On se connecte à la base de données
$baseDonnees = BaseDeDonnees::constructeur();

// On se connecte à la base de données et on récupère la liste des utilisateurs
$connexion = BaseDeDonnees::constructeur();
$listeDesUtilisateurs = $connexion->requeteRecupListeUtilisateurs();

// On créé l'objet gestion utilisateur pour traiter le formulaire de connexion
$session->traiteFormulaireConnexion($listeDesUtilisateurs, $page, $connexion);

// On récupère la liste des utilisateurs pour la parcourir
$utilisateurs = $baseDonnees->requeteRecupListeUtilisateurs();

// On traite le formulaire d'inscrition via l'objet de controle des saisies
$nouvelUtilisateur = $session->traiteFormulaireInscription($utilisateurs, $page);

// Si l'inscription est conforme, on l'enregistre dans la BD
if( $nouvelUtilisateur )
{
    $baseDonnees->requeteAjouterUnUtilisateur($nouvelUtilisateur["pseudo"], $nouvelUtilisateur["mdp"]);
    $page->inscriptionOK();
}



