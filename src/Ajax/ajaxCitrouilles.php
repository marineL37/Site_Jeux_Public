<?php
// On charge automatiquement les fichiers nécessaires
spl_autoload_register(
    function ($classe)
    {
        require_once '../Classes/' . $classe . '.class.php';
    });



// On démarre la session
$session = new Utilisateur();
$session->verrou(); // On vérrouille la page pour la rendre accessible qu'après connexion

// On se connecte à la base de donnée
$connexion = BaseDeDonnees::constructeur();

// Si $_POST["score"] existe (il a donc été envoyé par la requete ajax)
if( isset($_POST["score"]) )
{
    // Dans tous les cas on met à jour le dernier score de l'utilisateur dans la super globale $_SESSION
    $_SESSION["utilisateurActuel"]["citrouilles_dernier_score"] = intval($_POST["score"]);
    
    // Si le dernier score est supérieure au meilleur score on met à jour le meilleur score dans la super globale $_SESSION
    if( intval($_POST["score"]) > $_SESSION["utilisateurActuel"]["citrouilles_meilleur_score"] )
    {
        $_SESSION["utilisateurActuel"]["citrouilles_meilleur_score"] = intval($_POST["score"]);
    }
    // Puis on met à jour dans la base de donnée
    $connexion->requeteMajUtilisateurScoreCitrouille($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["citrouilles_dernier_score"], $_SESSION["utilisateurActuel"]["citrouilles_meilleur_score"]); 
    
    // La réponse retour de la requete ajax renvoie les scores pour affichage sans rechargement de la page
    $envoiJS = array("citrouilles_dernier_score" => $_SESSION["utilisateurActuel"]["citrouilles_dernier_score"], "citrouilles_meilleur_score" => $_SESSION["utilisateurActuel"]["citrouilles_meilleur_score"]);
    echo json_encode($envoiJS);
}