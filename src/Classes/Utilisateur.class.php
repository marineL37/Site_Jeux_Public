<?php
 /*
    Cette classe sert à gérer les comptes utilisateurs. Cette classe contient les méthodes :
        - __construct()
        - verrou()
        - creationMdpValide()
        - traiteFormulaireInscription()
        - traiteFormulaireConnexion()
*/

class Utilisateur
{
    /*
        Méthode permettant de démarer la session.
    */
    public function __construct()
    {
        session_start();
    }


    /* 
        Méthode permmetant de démarrer une page privé qui renvoie vers la page d'accueil pour connexion, 
        si l'utilisateur n'est pas connecté.
    */
    public function verrou()
    {        
        // Si $_SESSION[est_connecte] n'existe pas
        if ( !isset($_SESSION["est_connecte"]) ) 
        {
            header("Location: connexion.php"); // On renvoie à la page de connexion qui est la page d'accueil.
            exit;
        }
    }


    /*
        Cette méthode permet de controler la saisie du mot de passe au moment de la création du compte.
        On impose 8 caractères minimum et avec une expression régulière 1 minuscule, 1 majuscule et 1 chiffre.
        ENTREE : la saisie de l'utilisateur
        SORTIE : true si la saisie est correcte, false sinon
    */
    private function creationMdpValide(string $mdp) : bool
    {
        $saisieValide = false;
        if( strlen($mdp) >= 8 and preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])#', $mdp) )
        {
            $saisieValide = true;
        }
        return $saisieValide;
    }


    /*
        Méthode permmetant d'enregistrer un nouvel utilisateur
        Elle vérifie la saisie du pseudo et du mot de passe :
        - D'abord on vérifie que les champs existent et ont été complété.
        - Puis on valide la saisie avec le filtre par défault (car pseudo et mdp sont des chaines de caractères).
        - Puis on vérifie l'égalité entre les 2 mots de passes saisis.
        - Puis on vérifie que le mot de passe fais 8 caractères minimum et contient au moins 1 miniscule, 1 majuscule 
            et 1 chiffre avec une expression régulière.
        - Puis on vérifie que le pseudo est disponible.
        - Si tout est correct : La session débute et affiche un message invitant l'utilisateur à retourner sur la page d'accueil pour connexion.
        ENTREE : la liste des untilisateurs, l'objet $controleSaisie, l'objet $page.
        SORTIE : soit le $nouvelUtilisateur = ["pseudo", "mdp"] soit false.
    */
    public function traiteFormulaireInscription($listeDesUtilisateur, $page)
    {
        // Est-ce que les champs "pseudo", "mdp1" et mdp2" de POST existent ?
        if( filter_has_var(INPUT_POST,"pseudoInscription") and filter_has_var(INPUT_POST,"mdp1") and filter_has_var(INPUT_POST,"mdp2") )
        {
            $pseudo = filter_input(INPUT_POST, "pseudoInscription", FILTER_DEFAULT);
            $mdp1 = filter_input(INPUT_POST, "mdp1", FILTER_DEFAULT); 
            $mdp2 = filter_input(INPUT_POST, "mdp2", FILTER_DEFAULT); 
            // On vérifie si les 3 filtres n'ont pas rencontrés de problème, et que mdp1 = mdp2
            if( $pseudo and $mdp1 and $mdp2 and ($mdp1 === $mdp2) )
            {
                if( $this->creationMdpValide($mdp1) )
                {
                     // On crée un booléen à "true" pour tester la disponibilité du pseudo choisi 
                    $creationCompte = true;
                    // On calcule l'empreinte du mot de passe, pour plus de sécurité
                    $mdpChiffre = password_hash($mdp1, PASSWORD_DEFAULT); 
                    // On parcourt la liste des utilisateurs existant
                    foreach($listeDesUtilisateur as $cle => $users) 
                    {
                        // On vérifie que le pseudo soit disponible
                        if( $listeDesUtilisateur[$cle]["pseudo"] === $pseudo )
                        {
                            // Si le pseudo existe déjà, on affiche un message et on annule la création du compte
                            $page->saisiePseudoUtilise();
                            $creationCompte = false;
                        }
                    }
                    // Si creationCompte vaut true alors on créé le nouvel utilisateur et on le renvoie pour enregistrement 
                    // en base de données
                    if ( $creationCompte )
                    {
                        $nouvelUtilisateur["pseudo"] = $pseudo;
                        $nouvelUtilisateur["mdp"] = $mdpChiffre;
                        return $nouvelUtilisateur;
                    }
                }
                // Si le mot de passe est incorrecte, on affiche un message et renvoie false
                else
                {
                    $page->saisieMdpIncorrecte();
                    return false; // $creationCompte vaut false
                }
            }
            // Si l'un des filtres à rencontré un problème, on affiche un message et renvoie false
            else
            {
                $page->saisieIncorrecte();
                return false; // $creationCompte vaut false
            }
        }
    }


    /*
        Méthode permmetant de controler la saisie du pseudo et du mot de passe pour connexion.
        - D'abord on vérifie que les champs existent.
        - Puis on valide la saisie avec le filtre par défault (car pseudo et mdp sont des chaines de caractères).
        Enfin on regarde si la saisie correspond au mot de passe attendu, et on remet le hash à jour si nécessaire.
        Si le pseudo et le mot de passe sont correct : La session débute et renvoi vers la page d'accueil.
        ENTREE : auncune
        SORTIE : création des variables "est_connecte" et "utilisateurActuel" dans la superGlobal $_SESSION
    */
    public function traiteFormulaireConnexion($listeDesUtilisateurs, $page, $connexion)
    {
        // Est que les champs "pseudo" et "mdp" de POST existent ? Si oui, on utilise un filtre par défault
        if( filter_has_var(INPUT_POST,"pseudoConnexion") and filter_has_var(INPUT_POST,"mdpConnexion") )
        {
            $pseudo = filter_input(INPUT_POST, "pseudoConnexion", FILTER_DEFAULT);
            $mdp = filter_input(INPUT_POST, "mdpConnexion", FILTER_DEFAULT);
            // On vérifie si les 2 filtres n'ont pas rencontrés de problème
            if( $pseudo and $mdp )
            {
                $connexionOK = false; // On crée un booléen à "false" pour valider si le pseudo existe déjà
                foreach ($listeDesUtilisateurs as $cle => $users) // On parcourt la liste des utilisateurs existant
                {
                    // On vérifie si le pseudo saisi correspond à l'un des pseudo déjà enregistré
                    if( $listeDesUtilisateurs[$cle]["pseudo"] === $pseudo )
                    {
                        $connexionOK = true; // On autorise la connexion
                        $utilisateurActuel = $listeDesUtilisateurs[$cle]; // On stocke les données de l'utilisateur dans une variable
                    }
                }
                // On vérifie si la connexion est autorisée pour le pseudo saisi par l'utilisateur
                if( $connexionOK )
                {
                    // On vérifie si le mdp saisi correspond au mdp chiffré enregistré dans la base
                    if( password_verify($mdp, $utilisateurActuel["mdp"]) ) 
                    {
                        // Si le hachage peut être mis à jour
                        if( password_needs_rehash($utilisateurActuel["mdp"], PASSWORD_DEFAULT) ) 
                        {
                            // On crée un nouveau hachage afin de mettre à jour l'ancien
                            $newHash = password_hash($mdp, PASSWORD_DEFAULT);
                            $connexion->requeteMajMdp($pseudo, $newHash);
                        }
                        // On détruit le $utilisateurActuel pour ne pas conserver le mdp qui ne resservira plus avant prochaine connexion
                        unset($utilisateurActuel);
                        // On récupère de la base de donnée uniquement les données utiles.
                        $utilisateurActuel = $connexion->requeteRecupUnUtilisateur($pseudo);
                        // on convertie en entier les scores
                        $utilisateurActuel["pendu_nb_gagne"] = intval($utilisateurActuel["pendu_nb_gagne"]); 
                        $utilisateurActuel["pendu_nb_perdu"] = intval($utilisateurActuel["pendu_nb_perdu"]);
                        $utilisateurActuel["laby_nb_gagne"] = intval($utilisateurActuel["laby_nb_gagne"]);
                        $utilisateurActuel["laby_nb_perdu"] = intval($utilisateurActuel["laby_nb_perdu"]);
                        $utilisateurActuel["citrouilles_dernier_score"] = intval($utilisateurActuel["citrouilles_dernier_score"]);
                        $utilisateurActuel["citrouilles_meilleur_score"] = intval($utilisateurActuel["citrouilles_meilleur_score"]);
                        
                        $_SESSION["est_connecte"] = true; // On accepte la connexion
                        // On stocke les données de l'utilisateur dans une superGlobal pour permettre un affichage dynamique sur les différentes pages
                        $_SESSION["utilisateurActuel"] = $utilisateurActuel;
                        // On redirige vers l'accueil pour choisir le jeu
                        header("Location: index.php");
                        exit;
                    }
                    // Si le mdp est incorrecte on affiche un message
                    else
                    {
                        $page->saisieConnexionIncorrecte();
                    }
                }
                // Si le pseudo n'existe pas on affiche un message
                else
                {
                    $page->saisieConnexionIncorrecte();
                }
            }
        }
    }
}
