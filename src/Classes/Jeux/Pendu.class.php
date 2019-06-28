<?php
/*
    Cette classe permet de générer le jeu du pendu et d'y jouer grâce à ses méthodes :
        - __construct()
        - validerSaisiePendu()
        - propositionJoueur()
*/

// On charge automatiquement les fichiers nécessaires
spl_autoload_register(
    function ($classe)
    {
        require_once 'Classes/' . $classe . '.class.php';
    });


class Pendu
{
    /* 
        Fonction qui initialise toutes les variables necessaires à une partie.
        Ces variables sont stockées dans la SuperGlobal $_SESSION car elle ne sont valides qu'après connexion et jusqu'à la déconnexion.
        Ce stockage permet de pouvoir gérer le rechargement systématique de la page après soumission d'une lettre via le formulaire.
        Les variables créées sont :
         - "motPendu" (string) : contient un mot selectionné "aléatoirement" dans la constante LISTE_MOTS 
         - "tailleMot" (int) : contient la taille du "motPendu" (stockée dans une variable car utilisée plusieurs fois)
         - "motAffiche" (string) : contient une chaine de "_" du même nombre de lettre que le mot à trouver, pour affichage
         - "nbEssai" (int) : instanciée à 8, permet de compter le nombre d'essais avant echec et permet la gestion des scores en fin de partie
         - "lettreDejaProposees" (string) : chaine vide qui contiendra les lettres que propose l'utilisateur pour faciliter le jeu
         - "finDePartie" (bool) : instancier à "false", ne passera à "true" que lorsque la partie sera finie
         - "rejouer" (bool) : instancier à "false", ne passera à "true" que si clic sur "rejouer"
    */
    public function __construct()
    {
        if( !isset($_SESSION["motPendu"]) or $_SESSION["rejouer"] )
        {
            $bdPendu = BaseDeDonnees::constructeur();
            $unMotAleatoire = $bdPendu->recupUnMotAleatoire();
            $_SESSION["motPendu"] = rtrim(strtolower($unMotAleatoire));
            $_SESSION["tailleMot"] = strlen($_SESSION["motPendu"]);
            $_SESSION["motAffiche"] = "";
            for ($i = 0; $i < $_SESSION["tailleMot"] ; $i++)
            {
                $_SESSION["motAffiche"] .= "_"; 
            }
            $_SESSION["nbEssai"] = 8;
            $_SESSION["lettresDejaProposees"] = ""; 
            $_SESSION["finDePartie"] = false;
            $_SESSION["rejouer"] = false;
        }
    }


    /* 
        Méthode pemettant de valider la saisie du joueur.
        Cette méthode compare le numéro ASCII correspondant à la saisie (passée en miniscule) avec la table ASCCI.
        Si la saisie se trouve entre 97 et 122 (a-z) alors la fonction renvoie "true" sinon elle renvoie "false".
        ENTREE : La saisie de l'utilisateur en miniscule.
        SORTIE : Un booléen. 
    */
    private function validerSaisiePendu(string $lettreSaisie) : bool
    {
        if( (ord($lettreSaisie) >= 97) and (ord($lettreSaisie) <= 122) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }



    /*
        Fonction gérant une soumission de lettre envoyée par l'utilisateur.
        Cette fonction teste la saisie du joueurs et affiche les messages en correspondant aux diverses situations:
            - Est-ce bien une lettre ?
            - Cette lettre a-t-elle déjà été proposée ?
            - Cette lettre est-elle présente dans le mot à trouver ?
        Cette fonction gère aussi la partie :
            - Si la partie est en cours ou terminée:
                -> En cours : On exécute les vérifications sur la saisie du joueur.
                -> Terminée :  on augmente les scores puis on les enregistrent.
        ENTREE : aucune
        SORTIE : mise à jour de la superGlobal $_SESSION
    */
    public function propositionJoueur($page, $connexion) 
    {
        // On initialise un booléen à false pour la valider ou invalider la présence de la lettre dans le mot à trouver
        $_SESSION["lettreTrouvee"] = false; 
        // On vérifie que la partie ne soit pas finie
        if($_SESSION["finDePartie"] === false) 
        {
            // On récupère et passe en miniscule la lettre saisie par le joueurs
            $lettre = strtolower($_POST["lettre"]);
            // On valide la saisie avec la table ASCII (compris entre : a-z)
            if( $this->validerSaisiePendu($lettre) ) 
            {
                // On vérifie que la lettre n'ait pas été déjà proposée
                if( stripos($_SESSION["lettresDejaProposees"], $lettre) === false ) 
                {
                    // On ajoute la lettre à la liste des lettres déjà proposées
                    $_SESSION["lettresDejaProposees"] .= $lettre; 
                    
                    // On parcourt le mot à trouver lettre par lettre, en comparant avec la saisie du joueur
                    for($i = 0; $i < $_SESSION["tailleMot"]; $i++)
                    {
                        // Si une ou plusieurs lettres correspondent
                        if( $_SESSION["motPendu"][$i] === $lettre ) 
                        {
                            $_SESSION["motAffiche"][$i] = $lettre; // On affiche la lettre
                            $_SESSION["lettreTrouvee"] = true; // On valide la présence de la lettre
                        }
                    }
                    // Si la lettre ne correspondait pas
                    if( !$_SESSION["lettreTrouvee"] ) 
                    {
                        // On décrémente le nombre d'essai et on affiche un message de défaite
                        $_SESSION["nbEssai"] -= 1; 
                        $page->penduAfficheRate();
                    }
                    // Si la lettre était dans le mot on affiche un message de succès
                    else 
                    {
                        $page->penduAfficheTrouve();
                    }
                }
                // Si la lettre a déjà été proposée, on affiche un message sans faire perdre un tour
                else 
                {
                    $page->penduAfficheLettreDejaProposee();
                }
                // Dans tous les cas on affiche la liste des lettres déjà proposées
                $page->penduAfficheListeLettreDejaProposee();
            }
            // Si la saisie n'est pas valide (tous les autres caractères ne correspondant pas à une lettre)
            else 
            {
                $page->penduAfficheListeLettreDejaProposee();
                $page->penduAfficheErreurSaisie();
            }
            
            // On regarde si le mot a été trouvé
            if($_SESSION["motAffiche"] == $_SESSION["motPendu"]) 
            {
                // On affiche un message de victoire
                $page->penduAfficheVictoire(); 
                // On augmente le score de nombre de victoire: $_SESSION["utilisateurActuel"][2] = nb_partie_gagnee_pendu
                $_SESSION["utilisateurActuel"]["pendu_nb_gagne"] += 1; 
                // On enregistre les scores
                $connexion->requeteMajUtilisateurScorePendu($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["pendu_nb_gagne"], $_SESSION["utilisateurActuel"]["pendu_nb_perdu"]); 
                // On termine la partie.
                $_SESSION["finDePartie"] = true; 
            }
            elseif($_SESSION["nbEssai"] == 0)
            {
                $page->penduAfficheDefaite();
                $_SESSION["utilisateurActuel"]["pendu_nb_perdu"] += 1; 
                // On enregistre les scores
                $connexion->requeteMajUtilisateurScorePendu($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["pendu_nb_gagne"], $_SESSION["utilisateurActuel"]["pendu_nb_perdu"]); 
                // On termine la partie
                $_SESSION["finDePartie"] = true; 
            }
        }
    }
}