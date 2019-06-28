<?php

/*
    Classe permettant de générer une page Web qui comporte différentes méthodes :
        - __construct()
        - __destruct()
        - afficheMenuNavigation()
        - fermetureBalisesBodyHtml()
        - accueilBienvenue()
        - connexionAfficheFormulaire()
        - saisieConnexionIncorrecte()
        - inscriptionAfficheFormulaire()
        - saisiePseudoUtilise()
        - saisieMdpIncorrecte()
        - saisieIncorrecte()
        - inscriptionOK()
        - citrouilleAfficheSection()
        - labyAfficheSection()
        - labyAfficheFormulaireChoix()
        - labyAfficheFormulaireRobot()
        - labyAfficheErreurSaisie()
        - labyAfficheCarte()
        - labyAfficheErreurMur()
        - labyAfficheVictoire()
        - labyAfficheRejouer()
        - labyAfficheLienSolution()
        - labyAffichePartieFinie()
        - penduAfficheSection()
        - penduAfficheJeu()
        - penduAfficheFormulaire()
        - penduAfficheRejouer() 
        - penduAfficheRate()
        - penduAfficheTrouve()
        - penduAfficheLettreDejaProposee()
        - penduAfficheListeLettreDejaProposee()
        - penduAfficheErreurSaisie()
        - penduAfficheDefaite()

 */

class PageWeb
{
    private $enTete;
    private $menuNav;
    private $body = "";
    private $enTetePendu = "";
    private $enTeteLaby = "";
    private $script;

/*
    Définition de la constante contenant un tableau de ce qui doit figurer dans le menu de navigation du site.
    Pour ajouter une page supplémentaire : mettre en clé le nom qui doit apparaitre à l'écran et 
    en valeur le nom de la page correspondante (le fichier doit se trouver dans le dossier "Public").
*/
    const MENU_NAVIGATION = array("Accueil" => "index.php", "Le Tape Citrouilles" => "citrouilles.php", 
    "Le Labyrinthe" => "labyrinthe.php", "Le Pendu" => "pendu.php");



/*
    Cette méthode génère le code HTML commun à toutes les pages : le DOCTYPE, le menu de navigation et le pied de page.
    ENTREE : le title à afficher
*/
    public function __construct(string $title = "Une page")
    {
        $this->enTete =
'<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="css/reset.css" />
        <link rel="stylesheet" href="css/style.css" />
        <title>' . $title . '</title>
    </head>
    <body>';
        $this->afficheMenuNavigation();
        $this->fermetureBalisesBodyHtml();
    }


/*
    Cette méthode teste $_SERVER["PHP_SELF"] pour afficher le contenu de la page correspondante.
    Chaque méthode appelée ajoute du texte au body
*/
    public function __destruct()
    {
        // PAGE : ACCUEIL.PHP
        if( basename($_SERVER["PHP_SELF"]) === "index.php" )
        {
            $this->accueilBienvenue(); // affiche message de bienvenu sur le site et images cliquables des jeux
        }

        // PAGE : CONNEXION.PHP
        elseif( basename($_SERVER["PHP_SELF"]) === "connexion.php" )
        {
            $this->connexionAfficheFormulaire(); // On affiche le formulaire de connexion
            $this->inscriptionAfficheFormulaire(); // et le formulaire d'inscription
        } 
        
        // PAGE : PENDU.PHP
        elseif( basename($_SERVER["PHP_SELF"]) === "pendu.php" )
        {
            // affiche le jeu du pendu
            $this->penduAfficheSection();
            $this->penduAfficheJeu();
            $this->penduAfficheFormulaire();
            $this->penduAfficheRejouer();
        } 
        
        // PAGE : LABYRINTHE.PHP
        elseif( basename($_SERVER["PHP_SELF"]) === "labyrinthe.php" )
        {
            $this->labyAfficheSection(); // On affiche l'en-tete du jeu et les scores de l'utilisateur
            // Si le joueur n'a pas choisit de labyrinthe
            if( !isset($_SESSION["labyNbLigne"]) and !isset($_SESSION["labyNbColonne"]) )
            {
                $this->labyAfficheFormulaireChoix(); // on affiche les choix possibles
            } 
            else // Sinon on affiche le jeu
            {
                $this->labyAfficheLienSolution();
                $this->labyAfficheFormulaireRobot();
                $this->labyAfficheRejouer();
            }
        }

        // PAGE : SOLUTION_LABYRINTHE.PHP
        elseif( basename($_SERVER["PHP_SELF"]) === "solution_labyrinthe.php" )
        {
            $this->labyAfficheSection(); // On affiche l'en-tete du jeu et les scores de l'utilisateur
        }

        // PAGE : CITROUILLES.PHP
        elseif( basename($_SERVER["PHP_SELF"]) === "citrouilles.php" )
        {
            $this->citrouilleAfficheSection(); // On affiche la section du jeu "tape citrouilles"
        }
        echo $this->enTete . PHP_EOL;
        echo $this->menuNav . PHP_EOL;
        echo $this->enTetePendu . PHP_EOL;
        echo $this->enTeteLaby . PHP_EOL;
        echo $this->body . PHP_EOL;
        echo $this->script . PHP_EOL;
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                          Méthodes utiles à toutes les pages du site                                                 //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* 
    Procédure permettant un affichage standard et dynamique du menu de navigation.
    Dans un premier temps, on ouvre la balise "header" et on affiche le premier menu de navigation qui affiche uniquement
     - si l'utilisateur n'est pas connecté : un message invitant à se connecter et le lien de connexion
     - si l'utilisateur est connecté : un message de bienvenu personnalisé et le lien de déconnexion
    Dans un second temps, on parcourt la constante "MENU_NAVIGATION", si le nom du fichier de la page parcourue correspond 
    à la page actuel on affiche son nom en gras et blanc, sinon on l'affiche normalement.
*/
    public function afficheMenuNavigation()
    {
        $this->menuNav =
'       <header>
            <nav class="menuNav_1">
                <ul>';
        if( isset($_SESSION["est_connecte"]))
        {
            $this->menuNav .= 
'                   <li>Bievenue ' . htmlentities($_SESSION["utilisateurActuel"]["pseudo"], ENT_QUOTES | ENT_HTML5, "utf-8" ) . '</li>
                    <li><a href="redirection/deconnexion.php">Déconnexion</a></li>';
        }
        else
        {
            $this->menuNav .= 
'                   <li>Afin de pouvoir sauvegarder vos scores, veuillez vous connecter.</li>
                    <li><a class="lien_inscription" href="./connexion.php">Connexion</a></li>';
        }
        $this->menuNav .=
'               </ul>
            </nav>
            <nav class="menuNav_2">
                <ul>';
        foreach( self::MENU_NAVIGATION as $key => $value ) 
        {
            if( basename($_SERVER["PHP_SELF"]) === $value ) 
            {
                $this->menuNav .= '                     <li style="color: white"><strong>' . $key . '</strong></li>' . PHP_EOL;
            } 
            else 
            {
                $this->menuNav .= '                     <li><a href="' . $value . '">' . $key . '</a></li>' . PHP_EOL;
            }
        }
        $this->menuNav .=
'               </ul>
            </nav>
        </header>
        <div class="container">'; // ouvre le "container" qui affiche le fond blanc
    }

/*
    Procédure permettant de générer les balises de fermeture du body et du HTML
*/
    public function fermetureBalisesBodyHtml()
    {
        $this->script =
'    </body>
</html>';
    }



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                              Méthode utile à la page d'accueil                                                      //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
    Méthode permettant de générer le texte de Bienvenu sur la page d'acceuil ainsi que les différentes images cliquables
    des jeux disponibles.
*/
    public function accueilBienvenue()
    {
        $this->body .=
'       <section>
            <h1>Bienvenue sur ce site de mini-jeux</h1>
            <p class="msg_accueil">A quoi joue-t-on ?</p>
            <div class="bloc_img">
                <a href="citrouilles.php"><img class="img_citrouille" src="images/citrouille.png" alt="Image du jeu du tape citrouilles." title="Le tape citrouille"</a>
                <a href="labyrinthe.php"><img class="img_laby" src="images/labyrinthe.png" alt="Image du jeu du labyrinthe." title="Le labyrinthe"</a>
                <a href="pendu.php"><img class="img_pendu" src="images/pendu.png" alt="Image du jeu du pendu." title="Le pendu"</a>
            </div>
        </section>
    </div>'; // ferme le "container" qui affiche le fond blanc
    }



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                     Procédures utiles à la page de connexion et d'inscription                                       //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    Méthode générant le formulaire de connexion. Contient un formulaire demandant à l'utilisateur :
    - son identifiant
    - son mot de passe
    - un bouton connexion
*/
public function connexionAfficheFormulaire()
{
    $this->body .=
'           <section class="formulaires">
                <form method="post" class="form_connexion">
                    <fieldset>
                        <legend><strong>Se connecter</strong></legend>
                        <p><label for="pseudoConnexion">Pseudo : </label>
                            <input name="pseudoConnexion" type="text" id="pseudoConnexion" autofocus/><br />
                            <label for="mdpConnexion">Mot de passe : </label>
                            <input type="password" name="mdpConnexion" id="mdp" /><br /></p>
                    </fieldset>
                    <p><input class="btn_connexion" type="submit" value="Connexion"/></p>
                </form>';
}

/*
    Procédure d'affichage du message "erreur saisie" lors de la connexion.
*/
public function saisieConnexionIncorrecte()
{
    $this->body .=
'       <p class="erreur_connexion">Problème lors de la saisie : soit le pseudo n\'existe pas, soit le mot de passe est incorrect.</p>';
}



/*
    Procédure générant le formulaire d'inscription d'un nouvel utilisateur qui lui propose de saisir :
    - son identifiant
    - son mot de passe
    - valider son mot de passe
    - un bouton valider
*/
    public function inscriptionAfficheFormulaire()
    {
        $this->body .=
    '       <form method="post" class="form_inscription">
                <fieldset>
                    <legend><strong>Créer un compte</strong></legend>
                    <p><label for="pseudoInscription">Pseudo : </label>
                        <input name="pseudoInscription" type="text" id="pseudoInscription" /><br />
                        <label for="mdp1">Mot de passe : </label>
                        <input type="password" name="mdp1" id="mdp1" /><br />
                        <label for="mdp2">Confirmation du mot de passe : </label>
                        <input type="password" name="mdp2" id="mdp2" /></p>
                    <p class="msg_mdp">Votre mot de passe doit faire au minimum 8 caractères et doit contenir au moins 1 miniscule, 1 majuscule et 1 chiffre.</p>
                </fieldset>
                <p><input class="btn_connexion" type="submit" value="Valider"/></p>
            </form>
        </section>
    </div>'; // ferme le "container" qui affiche le fond blanc
    }

/*
    Procédure d'affichage du message "saisie pseudo déjà utilisé".
*/
    public function saisiePseudoUtilise()
    {
        $this->body .=
'        <p class="erreur_inscription">Ce pseudo existe déjà, veuillez en choisir un autre.</p>';
    }

/*
    Procédure d'affichage du message "saisie mdp incorrect".
*/
    public function saisieMdpIncorrecte()
    {
        $this->body .=
'        <p class="erreur_inscription">Votre mot de passe doit faire au minimum 8 caractères et doit contenir au moins 1 miniscule, 1 majuscule et 1 chiffre.</p>';
    }

/*
    Procédure d'affichage du message "probleme lors de la saisie".
*/
    public function saisieIncorrecte()
    {
        $this->body .=
'        <p class="erreur_inscription">Problème lors de la saisie : Soit tous les champs n\'ont pas été rempli, soit il y a une différence sur la saisie des mots de passes.</p>';
    }

/*
    Procédure d'affichage du message "Votre compte a bien été créé".
*/
    public function inscriptionOK()
    {
        $this->body .=
    '        <p class="connexionOK">Votre compte a bien été créé.</p>';
    }




/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                              Méthode utile à la page du jeu du tape citrouille                                      //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
        Procédure d'affichage du jeu "Tape citrouilles".
        Affichage des scores et des div constituants le jeu
        Ajout à la fin du body du script js gérant le jeu.
    */
    public function citrouilleAfficheSection()
    {
        $this->body .=
    '           <h1>Frappez les citrouilles</h1>
                <p class="regles_tapeCitrouilles">Pour marquer des points, cliquez sur un maximum de citrouilles qui apparaissent</p>
                <div class="bloc_scores">
                    <p>Dernier Score : <span class="score_Citrouille" id="score_actuel">' . $_SESSION["utilisateurActuel"]["citrouilles_dernier_score"] . '</span><br />
                    Meilleur Score : <span class="score_Citrouille" id="score_max">' . $_SESSION["utilisateurActuel"]["citrouilles_meilleur_score"] . '</span></p>
                    <button class="btn_jouer" onClick="jouer()">Jouer</button>
                </div>
                <div class="plateau_jeu">
                    <div class="trou">
                    <div class="citrouille" onClick="clicCitrouille()"></div>
                    </div>
                    <div class="trou">
                    <div class="citrouille" onClick="clicCitrouille()"></div>
                    </div> 
                    <div class="trou">
                    <div class="citrouille" onClick="clicCitrouille()"></div>
                    </div>
                </div>
            </div>'; // ferme le "container" qui affiche le fond blanc
        // Le "$this->script" contient la fermeture du body, de cette manièe, le script js est ajouté juste avant la fermeture du body
        $this->script = '<script src="js/citrouilles.js"></script>' . $this->script;
    }



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                              Méthodes utiles à la page du jeu du labyrinthe                                         //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    Procédure d'affichage de l'en-tête du jeu du Labyrinthe.
    Affiche un message de bienvenue, et les scores enregistrés.
*/
    public function labyAfficheSection()
    {
        $this->enTeteLaby .=
'       <h1 class="titre_laby">Bienvenue dans le jeu du Labyrinthe</h1>';
            if( isset($_SESSION["labyNbLigne"]) and isset($_SESSION["labyNbColonne"]) and basename($_SERVER["PHP_SELF"]) === "labyrinthe.php" )
            {
                $this->enTeteLaby .=
'       <section class="regles_laby">
            <p>Pour déplacer votre robot vous devez appuyer sur les flèches de direction ou sur Z, Q, S, D.</p>
        </section>';
            }
            $this->enTeteLaby .=
'       <div class="score_laby">
            <p class="gagne">Nombre de partie(s) gagnée(s) : ' . $_SESSION["utilisateurActuel"]["laby_nb_gagne"] . '</p>
            <p class="perdu">Nombre de partie(s) perdue(s) : ' . $_SESSION["utilisateurActuel"]["laby_nb_perdu"] . '</p>
        </div>';
    }

/*
    Procédure d'affichage du formulaire du choix du labyrinthe, qui permet la saisie :
        - du nombre de ligne contenu entre 10 et 25
        - du nombre de colonne contenu entre 10 et 55
        - du choix de la difficulté : 1, 2 ou 3
*/
    public function labyAfficheFormulaireChoix()
    {
        $this->body .=
'            <form class="form_laby" method="post" >
                <p><strong>Votre robot est perdu dans un labyrinthe aidez-le à sortir de ce dédale</strong>.
                <p>Veuillez choisir la taille du labyrinthe :<br>
                    <label for="choix_laby_nbLigne">Nombre de lignes <span class="precision">(max 25)</span> : </label>
                    <input name="choix_laby_nbLigne" type="number" id="choix_laby" min="10" max="25" placeholder="10" autofocus/><br />
                    <label for="choix_laby_nbColonne">Nombre de colonnes <span class="precision">(max 55)</span>: </label>
                    <input name="choix_laby_nbColonne" type="number" id="choix_laby" min="10" max="55" placeholder="10" /><br />
                    <label for="choix_laby_difficulte">Difficulte <span class="precision">(1-facile, 2-moyen, 3-difficile)</span> : </label>
                    <input name="choix_laby_difficulte" type="number" id="choix_laby" min="1" max="3" value="1"/><br />
                </p>
                <input class="btn_validerLaby" type="submit" value="Valider"/>
            </form>
        </div>'; // ferme le "container" qui affiche le fond blanc
    }

/*
    Procédure de génération du formulaire de saisie du mouvement du robot dans le labyrinthe.
    Formulaire contenant uniquement un champ de saisie de texte ne tolérant qu'une seule saisie.
    Ce formulaire est invisible à l'utilisateur, car complété et envoyer par le fichier "labyrinthe.js"
    Le script est ajouter juste avant la fermeture du body.
*/
    public function labyAfficheFormulaireRobot()
    {
        $this->body .=
'            <form class="form_robot" name="form_robot" method="post" >
                <p><label for="mvt_robot">Dans quelle direction avancer ? </label>
                <input name="mvt_robot" type="text" id="mvt_robot" maxlength="1" size="5" autofocus/></p>
            </form>';
        $this->script = '<script src="js/labyrinthe.js"></script>' . $this->script;
    }
    
/*
    Procédure d'affichage du message "erreur saisie" du Labyrinthe.
*/
    public function labyAfficheErreurSaisie()
    {
        $this->body .=
'           <p class="laby_mauvaise_saisie">Saisie incorrecte, veuillez appuyer uniquement sur : z, d, s, q ou les flêches directionnelles.</p>';
    }

/*
    Procédure d'affichage de l'image du Labyrinthe.
    Entree : le lien de l'image générée par php (bibliothèque GD)
*/
    public function labyAfficheCarte(string $laby)
    {
        $this->body .=
'           <section class="jeu_laby">
                <div class="carte_laby">' . $laby . '</div>';
    }

/*
    Procédure d'affichage du message "erreur mur" du Labyrinthe.
*/
    public function labyAfficheErreurMur()
    {
        $this->body .=
'           <p class="laby_mauvaise_saisie">Vous ne pouvez pas aller dans cette direction, il y a un mur.</p>';
    }

/*
    Procédure d'affichage du message "Victoire" du labyrinthe.
*/
    public function labyAfficheVictoire()
    {
        $this->body .=
'           <p class="mot_gagne">Victoire ! Vous avez trouvé la sortie !</p>';
    }

/*
    Procédure d'affichage du bouton "rejouer" du labyrinthe.
*/
    public function labyAfficheRejouer()
    {
        $this->body .=
'           <p class="rejouer"><a href="redirection/rejouer_laby.php">Rejouer</a></p></div>'; // ferme le "container" qui affiche le fond blanc
    }

/*
    Procédure d'affichage du lien "afficher solution" du Labyrinthe.
*/
    public function labyAfficheLienSolution()
    {
        $this->enTeteLaby .=
'           <p class="laby_affiche_solution"><a href="solution_labyrinthe.php">Voir la solution</a> <span class="precision">(sera considéré comme une défaite)</span></p>';
    }
    
/*
    Procédure d'affichage du message "partie Finie" du Labyrinthe.
*/
    public function labyAffichePartieFinie()
    {
        $this->body .=
'           <p class="mot_gagne">La partie est finie, mais vous pouvez rejouer !</p>';
    }



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                              Méthodes utiles à la page du jeu du pendu                                              //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    Procédure d'affichage de l'en-tête du jeu du Pendu.
    Affiche un message de bienvenue, et les scores enregistrés.
*/
    public function penduAfficheSection()
    {
        $this->enTetePendu .=
'       <section class="jeu_pendu">
            <h1>Bienvenue dans le jeu du pendu</h1>
            <div class="score_pendu">
                <p class="gagne">Nombre de partie(s) gagnée(s) : ' . $_SESSION["utilisateurActuel"]["pendu_nb_gagne"] . '</p>
                <p class="perdu">Nombre de partie(s) perdue(s) : ' . $_SESSION["utilisateurActuel"]["pendu_nb_perdu"] . '</p>
            </div>';
    }

/*
    Procédure d'affichage du jeu du Pendu.
    Affiche le mot à trouver, le nombre d'essais restant et l'image du jeu du pendu en fonction du nombre d'essai.
*/
    public function penduAfficheJeu()
    {
        $this->body .=
'           <div class="affichage_pendu">
                <p class="nb_essai">Nombre d\'essai restant : <span class="attention_pendu">' . $_SESSION["nbEssai"] . '</span></p>
                <p class="mot_a_trouver">Mot à trouver <span class="precision">(' . $_SESSION["tailleMot"] . ' lettres)</span> : ' . $_SESSION["motAffiche"] . '</p>
                <p><img src="images/Pendu/' . $_SESSION["nbEssai"] . '.png" alt="Image de potence" title="Image du pendu"/></p>
            </div>';
    }

/*
    Procédure d'affichage du formulaire du Pendu.
    Formulaire contenant uniquement un champ de saisie de texte ne tolérant qu'une seule saisie.
*/
    public function penduAfficheFormulaire()
    {
        $this->body .=
'            <form class="form_pendu" method="post" >
                <p><label for="lettre">Votre proposition : </label>
                    <input name="lettre" type="text" id="lettre" placeholder="Exemple : a" maxlength="1" autofocus/><br /></p>
            </form>
        </section>';
    }

/*
    Procédure d'affichage du bouton "rejouer" du Pendu.
*/
    public function penduAfficheRejouer()
    {
        $this->body .=
'           <div class="rejouer_pendu"><p class="rejouer"><a href="redirection/rejouer_pendu.php">Rejouer</a></p></div></div>'; // ferme le "container" qui affiche le fond blanc
    }
    
/*
    Procédure d'affichage du message "raté" du Pendu.
*/
    public function penduAfficheRate()
    {
        $this->body .=
'           <p class="lettre_rate">Raté...</p>';
    }

/*
    Procédure d'affichage du message "trouvé" du Pendu.
*/
    public function penduAfficheTrouve()
    {
        $this->body .=
'           <p class="lettre_trouve">Trouvé !</p>';
    }

/*
    Procédure d'affichage du message "lettre déjà proposé" du Pendu.
*/
    public function penduAfficheLettreDejaProposee()
    {
        $this->body .=
'           <p class="pendu_mauvaise_saisie">Vous avez déjà proposé cette lettre, essayez en une nouvelle</p>';
    }

/*
    Procédure d'affichage de la liste des lettres déjà proposées du Pendu.
*/
    public function penduAfficheListeLettreDejaProposee()
    {
        $this->body .=
'           <p class="liste_lettre">Liste des lettres déjà proposées : <span class="attention_pendu">' . htmlentities($_SESSION["lettresDejaProposees"], ENT_QUOTES | ENT_HTML5, "utf-8" ) . '</span></p>';
    }
   
/*
    Procédure d'affichage de la liste du message "erreur saisie" du Pendu.
*/
    public function penduAfficheErreurSaisie()
    {
        $this->body .=
'           <p class="pendu_mauvaise_saisie">Veuillez saisir une lettre (aucun chiffre ni caractères spéciaux)</p>';
    }

/*
    Procédure d'affichage de la liste du message "Victoire" du Pendu.
*/
    public function penduAfficheVictoire()
    {
        $this->body .=
'           <p class="mot_gagne">Victoire ! Vous avez gagné !</p>';
    }

/*
    Procédure d'affichage de la liste du message "défaite" du Pendu.
*/
    public function penduAfficheDefaite()
    {
        $this->body .=
'       <div class="defaite_pendu">
            <p class="mot_perdu">Pendu ... vous avez perdu</p>
            <p class="mot_perdu">Le mot à trouver été : ' . strtoupper($_SESSION["motPendu"]) . '</p>
        </div>';
    }
}