<?php
/*
    Class gérant la connexion à la base de donnée (contenant les comptes utilisateurs et leurs scores).
*/

class BaseDeDonnees
{
    private static $singleton = null;
    private $maBDR;
    /* 
        Définition du __construct en private pour limiter à 1 la connexion à la base de données.
        Cette méthode connecte à la base de données.
    */
    private function __construct()
    {
        $adresse = "mysql:host=localhost;dbname=bd_site_jeux;charset=UTF8";
        try
        {
            $this->maBDR = new PDO($adresse, "pendu", "di3F9Elpebw4YjS1");
        } 
        catch (PDOExeption $uneErreur) 
        {
            echo $uneErreur;
            exit;
        }
    }


    /* 
        Définition d'une méthode static permettant de construire la connexion à la BD 1 seule fois. 
    */
    public static function constructeur(): BaseDeDonnees
    {
        if (self::$singleton === null) {
            self::$singleton = new BaseDeDonnees();
        }
        return self::$singleton;
    }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                            Méthodes utiles à la gestion des utilisateurs                                            //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /* 
        Méthode permettant de récupérer le tableau contenant tous les utilisateurs.
        SORTIE : la liste de tous pseudo et mdp des utilisateurs.
    */
    public function requeteRecupListeUtilisateurs() : array //!\ A REVOIR : Securité médiocre /!\
    {
        $requete = "SELECT pseudo, mdp FROM Utilisateurs";
        // On essaie d'executer la requete préparée
        try 
        {
            $requetePreparee =  $this->maBDR->prepare($requete); 
        }
        // Si erreur, on affiche l'exception attrapée
        catch ( PDOExeption $Exception )
        {
            echo $Exception->getMessage();
            exit;
        }

        // On affiche les erreurs s'il y en a eu à la préparation ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        // fetch(PDO::FETCH_ASSOC) Permet de retourner le tableau des utilisateurs
        $resultat = $requetePreparee->fetchAll(PDO::FETCH_ASSOC);
        return $resultat;
    }


    /* 
        Méthode permettant de récupérer le tableau d'UN utilisateur. 
        ENTREE : le pseudo à récupérer en base.
        SORTIE : la liste des données d'un utilisateur (excepté son mdp).
    */
    public function requeteRecupUnUtilisateur(string $pseudo) : array
    {
        $requete = "SELECT pseudo, pendu_nb_gagne, pendu_nb_perdu, laby_nb_gagne, laby_nb_perdu, citrouilles_dernier_score, citrouilles_meilleur_score FROM Utilisateurs WHERE pseudo=?";
        try // On essaie d'executer la requete préparée
        {
            $requetePreparee =  $this->maBDR->prepare($requete);
        }
        // Si erreur, on affiche l'exception attrapée
        catch( PDOExeption $Exception ) 
        {
            echo $Exception->getMessage();
            exit;
        }

        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de la variable ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(1, $pseudo, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }

        $resultat = $requetePreparee->fetch(PDO::FETCH_ASSOC);
        return $resultat;
    }


    /*
        Méthode permettant d'ajouter un utilisateur à la base de donnée.
        ENTREE : pseudo et mdp (hashé).
    */
    public function requeteAjouterUnUtilisateur(string $pseudo, string $mdp)
    {
        
        $requete = "INSERT INTO `Utilisateurs` (`id`, `pseudo`, `mdp`) VALUES (NULL, ?, ?)";
        try // On essaie d'executer la requete préparée
        {
            $requetePreparee =  $this->maBDR->prepare($requete);
        }
        catch ( PDOExeption $Exception ) // Si erreur, on affiche l'exception attrapée
        {
            echo $Exception->getMessage();
            exit;
        }

        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de variables ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(1, $pseudo, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(2, $mdp, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        
    }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                Méthodes pour mettre à jour les scores obtenus sur les différents jeux pour un joueur                                //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
        Méthode permettant de mettre à jour les scores du jeux du Pendu d'un utilisateur.
        ENTREE : pseudo, le nombre de partie gagnées au pendu, le nombre de partie perdues au pendu.
    */
    public function requeteMajUtilisateurScorePendu(string $pseudo, int $p_nb_gagne, int $p_nb_perdu)
    {
        $requete = "UPDATE `Utilisateurs` SET `pendu_nb_gagne` = ?, `pendu_nb_perdu` = ? WHERE `Utilisateurs`.`pseudo` = ?;";
        
        try // On essaie d'executer la requete préparée
        {
            $requetePreparee =  $this->maBDR->prepare($requete); 
        }
        catch ( PDOExeption $Exception ) // Si erreur, on affiche l'exception attrapée
        {
            echo $Exception->getMessage();
            exit;
        }

        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de variables ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(1, $p_nb_gagne, PDO::PARAM_INT) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(2, $p_nb_perdu, PDO::PARAM_INT) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(3, $pseudo, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
    }

    /*
        Méthode permettant de mettre à jour les scores du jeux du Labyrinthe d'un utilisateur.
        ENTREE : pseudo, le nombre de partie gagnées au jeu du labyrinthe, le nombre de partie perdues au jeu du labyrinthe.  
    */
    public function requeteMajUtilisateurScoreLaby(string $pseudo, int $l_nb_gagne, int $l_nb_perdu)
    {
        $requete = "UPDATE `Utilisateurs` SET `laby_nb_gagne` = ?, `laby_nb_perdu` = ? WHERE `Utilisateurs`.`pseudo` = ?;";
        
        try // On essaie d'executer la requete préparée
        {
            $requetePreparee =  $this->maBDR->prepare($requete); 
        }
        catch ( PDOExeption $Exception ) // Si erreur, on affiche l'exception attrapée
        {
            echo $Exception->getMessage();
            exit;
        }
        
        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de variables ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(1, $l_nb_gagne, PDO::PARAM_INT) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(2, $l_nb_perdu, PDO::PARAM_INT) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(3, $pseudo, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
    }


    /*
        Méthode permettant de mettre à jour les scores du jeu du "Tape Citrouilles" d'un utilisateur.
        ENTREE : pseudo, le dernier score obtenu au "Tape Citrouilles" et le score max obtenu au "Tape Citrouilles".  
    */
    public function requeteMajUtilisateurScoreCitrouille(string $pseudo, int $dernier_score, int $score_max)
    {
        $requete = "UPDATE `Utilisateurs` SET `citrouilles_dernier_score` = ?, `citrouilles_meilleur_score` = ? WHERE `Utilisateurs`.`pseudo` = ?;";
        
        try // On essaie d'executer la requete préparée
        {
            $requetePreparee =  $this->maBDR->prepare($requete);
        }
        catch ( PDOExeption $Exception ) // Si erreur, on affiche l'exception attrapée
        {
            echo $Exception->getMessage();
            exit;
        }

        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de variables ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(1, $dernier_score, PDO::PARAM_INT) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(2, $score_max, PDO::PARAM_INT) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(3, $pseudo, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
    }


    /*
        Méthode permettant d'enregistrer la mise à jour du hashage de mot de passe.
        ENTREE : pseudo, nouveau hashage.
    */
    public function requeteMajMdp(string $pseudo, string $newHash) 
    {
        $requete = "UPDATE `Utilisateurs` SET `mdp` = ? WHERE `Utilisateurs`.`pseudo` = ?;";

        try // On essaie d'executer la requete préparée
        {
            $requetePreparee =  $this->maBDR->prepare($requete);
        }
        catch ( PDOExeption $Exception ) // Si erreur, on affiche l'exception attrapée
        {
            echo $Exception->getMessage();
            exit;
        }

        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de variables ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(1, $newHash, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->bindValue(2, $pseudo, PDO::PARAM_STR) === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
    }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                              Méthodes pour récupérer un mot alétoirement pour le jeu du pendu                                       //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
        Méthode permettant de récupèrer aléatoirement un mot dans la base de données du pendu.
        ENTREE : aucune
        SORTIE : un mot selectionné aléatoirement dans la base
    */
    public function recupUnMotAleatoire()
    {
        $requete = "SELECT `mot` FROM `MotsPendu` ORDER BY RAND() LIMIT 1";
        try // On essaie d'executer la requete préparée
        {
            $requetePreparee = $this->maBDR->prepare($requete);
        }
        catch ( PDOExeption $Exception ) // Si erreur, on affiche l'exception attrapée
        {
            echo $Exception->getMessage();
            exit;
        }
        // On affiche les erreurs s'il y en a eu : à la préparation, à l'ajout de variables ou à l'execution
        if( $requetePreparee === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }
        if( $requetePreparee->execute() === false )
        {
            echo $requetePreparee->errorinfo()[2];
            exit;
        }

        $resultat = $requetePreparee->fetch();

        return $resultat["mot"];
    }





}