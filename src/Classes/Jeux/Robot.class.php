<?php
/*
    Cette classe permet d'initialiser un objet robot qui comporte différentes méthodes :
        - __construct()
        - emplacement_robot()
        - casesVisibles()
        - regarder()
        - saisieDirectionRobot()
        - deplacer()
 */

class Robot
{
    private $page;
    private $connexion;
    private $ligne_robot;
    private $colonne_robot;
    private $labyrinthe;

/*
    Cette méthode permet de récupérer et stocker les objets utiles aux autres méthodes.
    ENTREE :
        - l'objet page : contient toutes les méthodes d'affichages de message (erreur, victoire, scores...)
        - l'objet connexion : contient les méthodes de mis à jour des scores
        - l'objet labyrinthe (format tableau d'objet UneCase)
    Cette méthode initialise la $_SESSION["finDePartieLaby"] à "false" pour gérer la partie.
 */
    public function __construct($page, $connexion, $labyrinthe)
    {
        // On récupère les objets utiles
        $this->page = $page;
        $this->connexion = $connexion;
        $this->labyrinthe = $labyrinthe;
        // Si la $_SESSION["finDePartieLaby"] n'existe pas, on la crée
        if (!isset($_SESSION["finDePartieLaby"])) {
            $_SESSION["finDePartieLaby"] = false;
        }
    }


    /*
        Cette méthode permet de trouver l'emplacement actuel du robot et de stocker son numéro de ligne et de colonne.
        On parcourt le labyrinthe (voir class Labyrinthe) pour trouver quelle case correspond
        à la position du robot, puis on stocke la ligne et la colonne correspondante.
     */
    private function emplacement_robot()
    {
        
        foreach($this->labyrinthe as $numeroLigne => $ligne) // On parcourt chaque ligne
        {
            foreach($ligne as $colonne => $case) // On parcourt chaque colonne
            {
                if( $case->obtenirRobot() ) // return "true" si on est sur la case du robot
                {
                    $this->ligne_robot = $numeroLigne;
                    $this->colonne_robot = $colonne;
                }
            }
        }
    }


    /*
        Cette méthode permet de stocker la position des cases à rendre visible pour l'affichage.
        Les cases sont rendues visibles par la position et le déplacement du robot.
        La liste des positions des cases est stockée dans la $_SESSION['listeCasesVisibles'] sous la forme suivante :
            [[$ligne, $colonne], [$ligne, $colonne], [$ligne, $colonne] ...etc...]
        ENTRE : le numéro de ligne et de colonne à enregistrer dans la $_SESSION
        SORTIE : aucune
    */
    private function casesVisibles(int $ligne, int $colonne)
    {
        // On s'assure que la position ne soit pas déjà présente dans la liste avant de l'ajouter
        if( !isset($_SESSION['listeCasesVisibles']) or !in_array(array($ligne, $colonne), $_SESSION['listeCasesVisibles']) )
        {
            $_SESSION['listeCasesVisibles'][] = [$ligne, $colonne];
        }
    }


    /*
        Cette méthode permet de rendre visible les cases autours du robot, dans les 8 directions (haut, haut-droite, droite, bas-droite,
        bas, bas-gauche, gauche, haut-gauche).Pour chacune de ces 8 directions : d'abord on teste voir si la case suivante 
        existe (pour éviter les erreurs lorsque le robot arrive sur les bords du labyrinthe), puis on rend visible la première case, 
        si celle-ci permet une visibilité on affiche les deuxièmes cases.
    */
    public function regarder()
    {
        $this->emplacement_robot(); // on met à jour $this->ligne_robot et $this->colonne_robot

        // REGARDER EN HAUT
        if (method_exists($this->labyrinthe[$this->ligne_robot - 1][$this->colonne_robot], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot);
            
            if ($this->labyrinthe[$this->ligne_robot - 1][$this->colonne_robot]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot);
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot + 1);
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot - 1);
            }
        }

        // REGARDER EN HAUT ET A DROITE
        if (method_exists($this->labyrinthe[$this->ligne_robot - 1][$this->colonne_robot + 1], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot + 1);

            if ($this->labyrinthe[$this->ligne_robot - 1][$this->colonne_robot + 1]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot + 2);
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot + 1);
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot + 2);
            }
        }

        // REGARDER A DROITE
        if (method_exists($this->labyrinthe[$this->ligne_robot][$this->colonne_robot + 1], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot, $this->colonne_robot + 1);

            if ($this->labyrinthe[$this->ligne_robot][$this->colonne_robot + 1]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot, $this->colonne_robot + 2);
                $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot + 2);
                $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot + 2);
            }
        }

        // REGARDER EN BAS ET A DROITE
        if (method_exists($this->labyrinthe[$this->ligne_robot + 1][$this->colonne_robot + 1], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot + 1);

            if ($this->labyrinthe[$this->ligne_robot + 1][$this->colonne_robot + 1]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot + 2);
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot + 1);
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot + 2);
            }
        }

        // REGARDER EN BAS
        if (method_exists($this->labyrinthe[$this->ligne_robot + 1][$this->colonne_robot], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot);

            if ($this->labyrinthe[$this->ligne_robot + 1][$this->colonne_robot]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot);
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot - 1);
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot + 1);
            }
        }

        // REGARDER EN BAS ET A GAUCHE
        if (method_exists($this->labyrinthe[$this->ligne_robot + 1][$this->colonne_robot - 1], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot - 1);

            if ($this->labyrinthe[$this->ligne_robot + 1][$this->colonne_robot - 1]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot - 1);
                $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot - 2);
                $this->casesVisibles($this->ligne_robot + 2, $this->colonne_robot - 2);
            }
        }

        // REGARDER A GAUCHE
        if (method_exists($this->labyrinthe[$this->ligne_robot][$this->colonne_robot - 1], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot, $this->colonne_robot - 1);

            if ($this->labyrinthe[$this->ligne_robot][$this->colonne_robot - 1]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot, $this->colonne_robot - 2);
                $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot - 2);
                $this->casesVisibles($this->ligne_robot + 1, $this->colonne_robot - 2);
            }
        }

        // REGARDER EN HAUT ET A GAUCHE
        if (method_exists($this->labyrinthe[$this->ligne_robot - 1][$this->colonne_robot - 1], 'modifVisible')) 
        {
            // On stocke les positions des cases à rendre visible
            $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot - 1);

            if ($this->labyrinthe[$this->ligne_robot - 1][$this->colonne_robot - 1]->obtenirVisibilite()) // Si on a une visibilité
            {
                // On stocke les positions des cases à rendre visible
                $this->casesVisibles($this->ligne_robot - 1, $this->colonne_robot - 2);
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot - 1);
                $this->casesVisibles($this->ligne_robot - 2, $this->colonne_robot - 2);
            }
        }
    }


    /*
        Méthode controlant la saisie du formulaire de déplacement du robot.
        Les seules saisies tolérées sont les lettres des 4 directions (haut: z, bas: s, gauche: q, droite: d).
        ENTREE : la saisie de l'utilisateur, et l'objet issu de la class PageWeb pour obtenir 
            la méthode de l'affichage du message d'erreur.
        SORTIE : true si saisie correcte, false sinon.
    */
    private function saisieDirectionRobot(string $mvt, $page) : bool
    {        
        if( $mvt === "z" or $mvt === "d" or $mvt === "s" or $mvt === "q" )
        {
            return true;
        }
        else
        {
            $page->labyAfficheErreurSaisie();
            return false;
        }
    }


    /*
        Cette méthode permet de déplacer le robot dans les 4 directions (haut, droite, bas, gauche).
        D'abord on utilise la méthode regarder() qui stocke les cases visible et récupère la ligne et la colonne actuelle du 
            robot par l'appelle de la méthode privée emplacement_robot().
        Puis on s'assure successivement que la partie n'est pas terminée, qu'il y a un bien une lettre qui a été saisie par le joueur,
            et que cette saisie est correcte (par le biais de la méthode saisieDirectionRobot).
        En fonction de la direction saisie on augmente/diminue la ligne/colonne où se trouve le robot, et on teste si la case à l'attribut
            traversable = true (voir Class UneCase). Si la case est traversable on déplace le robot et on regarde si la case correspond
            à la sortie du labyrinthe, si c'est le cas on affiche un message, on augmente le score et on met à jour la base de données.
            Pour chaque condition non respectée on affiche un message correspondant.
        ENTREE : aucune
        SORTIE : Mise à jour de : la $_SESSION, du labyrinthe (par référence) et de la liste des case vides
    */
    public function deplacer()
    {
        $this->regarder(); // on regarde autour et on met à jour $this->ligne_robot et $this->colonne_robot
        
        if (!$_SESSION["finDePartieLaby"]) // On vérifie que la partie ne soit pas finie
        {
            if (isset($_POST["mvt_robot"])) // On vérifie que le joueur à bien saisie quelque chose
            {
                $mvt = strtolower($_POST["mvt_robot"]); // on récupère et passe en miniscule la lettre saisie par le joueurs
                if ($this->saisieDirectionRobot($mvt, $this->page)) // On controle la saisie 
                {
                    if ($mvt === "z") // Si déplacement vers le HAUT
                    {
                        $nouvelle_ligne_robot = $this->ligne_robot - 1; // on dimine la ligne de - 1
                         // On vérifie que la case soit traversable
                        if ($this->labyrinthe[$nouvelle_ligne_robot][$this->colonne_robot]->obtenirTraversable())
                        {
                            // on enlève le robot de l'ancienne case
                            $this->labyrinthe[$this->ligne_robot][$this->colonne_robot]->modifRobot(false);
                            // on ajoute le robot sur la nouvelle case
                            $this->labyrinthe[$nouvelle_ligne_robot][$this->colonne_robot]->modifRobot(true);

                            // On vérifie si la case correspond à la sortie
                            if ($this->labyrinthe[$nouvelle_ligne_robot][$this->colonne_robot]->obtenirGagne()) 
                            {
                                $this->page->labyAfficheVictoire(); // on affiche un message de victoire
                                $_SESSION["utilisateurActuel"]["laby_nb_gagne"] += 1; // on augmente le score
                                // on met à jour les scores dans la base de donnée
                                $this->connexion->requeteMajUtilisateurScoreLaby($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["laby_nb_gagne"], $_SESSION["utilisateurActuel"]["laby_nb_perdu"]);
                                $_SESSION["finDePartieLaby"] = true; // on fini la partie
                            }
                        } 
                        else 
                        {
                            $this->page->labyAfficheErreurMur();
                        }
                    } 
                    
                    elseif ($mvt === "d") // Si déplacement vers la DROITE
                    {
                        $nouvelle_colonne_robot = $this->colonne_robot + 1; // on augmente la colonne de + 1
                        // On vérifie que la case soit traversable
                        if ($this->labyrinthe[$this->ligne_robot][$nouvelle_colonne_robot]->obtenirTraversable()) 
                        {
                            // on enlève le robot de l'ancienne case
                            $this->labyrinthe[$this->ligne_robot][$this->colonne_robot]->modifRobot(false);
                            // on ajoute le robot sur la nouvelle case
                            $this->labyrinthe[$this->ligne_robot][$nouvelle_colonne_robot]->modifRobot(true);
                            
                            // On vérifie si la case correspond à la sortie
                            if ($this->labyrinthe[$this->ligne_robot][$nouvelle_colonne_robot]->obtenirGagne()) 
                            {
                                $this->page->labyAfficheVictoire(); // on affiche un message de victoire
                                $_SESSION["utilisateurActuel"]["laby_nb_gagne"] += 1; // on augmente le score
                                // on met à jour les scores dans la base de donnée
                                $this->connexion->requeteMajUtilisateurScoreLaby($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["laby_nb_gagne"], $_SESSION["utilisateurActuel"]["laby_nb_perdu"]);
                                $_SESSION["finDePartieLaby"] = true; // on fini la partie
                            }
                        } 
                        else 
                        {
                            $this->page->labyAfficheErreurMur();
                        }
                    } 
                    
                    elseif ($mvt === "s") // Si déplacement vers le BAS 
                    {
                        $nouvelle_ligne_robot = $this->ligne_robot + 1; // on augmente la ligne de + 1
                        // On vérifie que la case soit traversable
                        if ($this->labyrinthe[$nouvelle_ligne_robot][$this->colonne_robot]->obtenirTraversable()) 
                        {
                            // on enlève le robot de l'ancienne case
                            $this->labyrinthe[$this->ligne_robot][$this->colonne_robot]->modifRobot(false);
                            // on ajoute le robot sur la nouvelle case
                            $this->labyrinthe[$nouvelle_ligne_robot][$this->colonne_robot]->modifRobot(true);

                            // On vérifie si la case correspond à la sortie
                            if ($this->labyrinthe[$nouvelle_ligne_robot][$this->colonne_robot]->obtenirGagne()) 
                            {
                                $this->page->labyAfficheVictoire(); // on affiche un message de victoire
                                $_SESSION["utilisateurActuel"]["laby_nb_gagne"] += 1; // on augmente le score
                                // on met à jour les scores dans la base de donnée
                                $this->connexion->requeteMajUtilisateurScoreLaby($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["laby_nb_gagne"], $_SESSION["utilisateurActuel"]["laby_nb_perdu"]); 
                                $_SESSION["finDePartieLaby"] = true; // on fini la partie
                            }
                        } 
                        else 
                        {
                            $this->page->labyAfficheErreurMur();
                        }
                    } 
                    
                    elseif ($mvt === "q") // Si déplacement vers la GAUCHE
                    {
                        $nouvelle_colonne_robot = $this->colonne_robot - 1; // on diminue la colonne de - 1
                        // On vérifie que la case soit traversable

                        if ($this->labyrinthe[$this->ligne_robot][$nouvelle_colonne_robot]->obtenirTraversable()) 
                        {
                            // on enlève le robot de l'ancienne case
                            $this->labyrinthe[$this->ligne_robot][$this->colonne_robot]->modifRobot(false);
                            // on ajoute le robot sur la nouvelle case
                            $this->labyrinthe[$this->ligne_robot][$nouvelle_colonne_robot]->modifRobot(true);

                            // On vérifie si la case correspond à la sortie
                            if ($this->labyrinthe[$this->ligne_robot][$nouvelle_colonne_robot]->obtenirGagne()) 
                            {
                                $this->page->labyAfficheVictoire(); // on affiche un message de victoire
                                $_SESSION["utilisateurActuel"]["laby_nb_gagne"] += 1; // on augmente le score
                                // on met à jour les scores dans la base de donnée
                                $this->connexion->requeteMajUtilisateurScoreLaby($_SESSION["utilisateurActuel"]["pseudo"], $_SESSION["utilisateurActuel"]["laby_nb_gagne"], $_SESSION["utilisateurActuel"]["laby_nb_perdu"]);
                                $_SESSION["finDePartieLaby"] = true; // on fini la partie
                            }
                        } 
                        else 
                        {
                            $this->page->labyAfficheErreurMur();
                        }
                    }
                    $this->labyrinthe = $this->regarder(); // On regarde après déplacement pour afficher dynamiquement le labyrinthe
                }
            }
        } 
        else // Si la partie est terminée
        {
            $this->page->labyAffichePartieFinie();
        }
    }
}