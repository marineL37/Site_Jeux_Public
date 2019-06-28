<?php

/*
    Cette classe permet d'initialiser un objet Labyrinthe qui sera utilisé sous 3 formats :
        - le type tableau de tableau de chaine de caractères, exemple : [["OOOOOOOOO"], ["O OO  O O"], ["O OO  O O"] ...(x nligne)]
        - le type tableau de tableau d'objet "UneCase", chaque caractères présent dans le type précédent est remplacer par
            son équivalent en objet "UneCase", qui auront des attributs et méthodes (cf UneCase.class.php)
        - le type chaine de caractères utile pour l'affichage, exemple : "OOOOOOOOOO\nO OO   O O\nO OO   O O\n" ...etc
    
    qui comporte différentes méthodes :
        - __construct()
        - sortieProche()
        - chercheCasesInterdites()
        - labyAleatoire()
        - getLabyTableau()
        - deplacerRobot()
        - conversionLabyTableauVersObjet()
        - conversionLabyObjetVersTableau()
        - solution()
        - conversionLabyObjetVersImage()
        - obtenirLabyComplet()
*/

class Labyrinthe
{
    private $carte = [];
    private $nbLigne;
    private $nbColonne;
    private $labyTableau;
    private $labyTableauObjet;
    private $listeCasesInterdites;
    private $nbCasesVides;


    /*
        Lors de la construction de l'objet, on récupère les dimensions du labyrinthe
    */
    public function __construct()
    {
        // On récupère les dimensions du labyrinthe à générer
        $this->nbLigne = $_SESSION["labyNbLigne"];
        $this->nbColonne = $_SESSION["labyNbColonne"];
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                              Méthodes utiles à la génération aléatoire du labyrinthe                                            //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
        Cette méthode permet (au moment de la génération aléatoire du labyrinthe) de déterminer si la sortie
        est sur l'une des cases adjacentes à la case actuelle dans les 4 directions :
            - bas = ligne +1
            - haut = ligne -1
            - droite = colonne +1
            - gauche = colonne -1.
        ENTREE : le labyrinthe (array), le numéro de la ligne actuelle et le numéro de la colonne actuelle
        SORTIE : Booleen à true si l'une des cases adjacentes est celle qui correspond à la sortie, false sinon.
    */
    private function sortieProche(array $labyTableau, int $ligne, int $colonne) : bool
    {
        $sortieProche = false;
        if( ($labyTableau[$ligne +1][$colonne] === "S") or ($labyTableau[$ligne -1][$colonne] === "S") or
            ($labyTableau[$ligne][$colonne +1] === "S") or ($labyTableau[$ligne][$colonne -1] === "S"))
        {
            $sortieProche = true;
        }
        return $sortieProche;
    }



    /*
        Cette méthode permet (au moment de la génération aléatoire du labyrinthe) de déterminer si les cases adjacentes doivent être
        interdite de passage pour limiter la taille des couloirs du labyrinthe.
        Une case devient interdite si la case actuelle a 2 cases adjacentes déjà vides exemple :
                                            "0" = mur; "I" = interdite
                                                OOOOOOOOOOO
                                                O   OOI IOO
                                                OOI OO   OO
                                                OOO IOI IOO
                                                OOO     OOO
                                                OOOOOOOOOOO
            
        ENTREE : les entiers correspondant à la position ligneRobot et colonneRobot actuelle
        SORTIE : aucune (mise à jour de l'attribut "$this->listeCasesInterdites")
    */
    private function chercheCasesInterdites(int $positionLigneRobot, int $positionColonneRobot)
    {
        // On test tout les cas possibles :

        // Dans un premier temps : le cas des croisements :
        // SI la case en haut et la case à droite sont vides alors, la case en haut et à droite est interdite
        if( $this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot] === " " // en haut
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot + 1] === " ") // à droite
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot - 1, $positionColonneRobot + 1]; // en haut et à droite
        }
        // SI la case en haut et la case à gauche sont vides alors, la case en haut et à gauche est interdite
        if( $this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot] === " "  // en haut
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot - 1] === " ") // à gauche
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot - 1, $positionColonneRobot - 1]; // en haut et à gauche
        }
        // SI la case en bas et la case à gauche sont vides alors, la case en bas et à gauche est interdite
        if( $this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot] === " " // en bas
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot - 1] === " ") // à gauche
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot + 1, $positionColonneRobot - 1];
        }
        // SI la case en bas et la case à droite sont vides alors, la case en bas et à droite est interdite
        if( $this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot] === " "  // en bas
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot + 1] === " ") // à droite
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot + 1, $positionColonneRobot + 1]; // en bas à droite
        }

        // Dans un second temps : le cas des angles :
        // Si on vient de la gauche et que l'on va vers le haut ou le bas on interdit la case à gauche
        if( ($this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot - 1] === " " // en haut à gauche
        and $this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot] === " ") // en haut
        or ($this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot - 1] === " " // en bas à gauche
        and $this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot] === " ") ) // en bas
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot, $positionColonneRobot - 1];
        }
        // Si on vient de la droite et que l'on va vers le haut ou bas on interdit la case de droite
        if( ($this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot + 1] === " " // en haut à droite
        and $this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot] === " ") // en haut
        or ($this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot] === " " // en bas
        and $this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot + 1] === " ") ) // en bas à droite
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot, $positionColonneRobot + 1];
        }
        // Si on vient d'en haut et que l'on va vers la gauche ou la droite on interdit la case du dessu
        if( ($this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot + 1] === " " // en haut à droite
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot + 1] === " ") // à droite
        or ($this->labyTableau[$positionLigneRobot - 1][$positionColonneRobot - 1] === " " // en haut à gauche
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot - 1] === " ") ) // à gauche
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot - 1, $positionColonneRobot];
        }
        // Si on vient d'en bas et que l'on va vers la gauche ou la droite on interdit la case en dessou
        if( ($this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot + 1] === " " // en bas à droite
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot + 1] === " ") // à droite
        or ($this->labyTableau[$positionLigneRobot + 1][$positionColonneRobot - 1] === " " // en bas à gauche
        and $this->labyTableau[$positionLigneRobot][$positionColonneRobot - 1] === " ") ) // à gauche
        {
            $this->listeCasesInterdites[] = [$positionLigneRobot + 1, $positionColonneRobot];
        }
    }



    /*
        Cette méthode permet de générer aléatoirement un labyrinthe de taille et de difficulté connue.
        ENTREE : La difficulté choisie par le joueur.
            D'abord on génère un tableau de la taille correspondante au choix et on le remplis de murs "O".
            Puis on génère aléatoirement la position du robot puis celle de la sortie (en fonction de celle du robot).
            
            Dans un premier temps on trace le chemin qui mène directement à la sortie. Puis on replace le robot à sa position initiale.
            Dans un second temps, à partir de la position du robot, on génère aléatoirement un nombre entre 1 et 4 pour obtenir 
            une direction, puis on génère aléatoirement un nombre de déplacement à effectuer dans cette direction (sans sortir des limites),
            jusqu'à avoir atteint le pourcentage de cases vides correspondant au niveau de difficulté choisi.

        SORTIE : mise à jour de l'attibut $this->labyTableau (contenant le labyrinthe)
    */
    public function labyAleatoire(int $difficulteChoisie)
    {
        // On initialise le tableau qui contiendra le labyrinthe
        $this->labyTableau = [];
        // Celui qui contiendra la liste des cases qu'il est interdit d'effacer
        $this->listeCasesInterdites = [];
        // Le nombre de cases vides pour en calculer le pourcentage afin de controler la difficulté
        $this->nbCasesVides = 0;
        
        // Avec une double boucle, on remplis chaque case du tableau avec des murs "O"
        for ($i=0; $i < $this->nbLigne; $i++) 
        { 
            for ($j=0; $j < $this->nbColonne; $j++) 
            { 
                $this->labyTableau[$i][$j] = "O";
            }
            $this->labyTableau[$i][$this->nbColonne +1] = "\n";
        }
        
        // On définit aléatoirement le numéro de ligne où se trouve le joueur
        $positionLigneRobot = rand(1, ($this->nbLigne -2)); // de 1 à nbLigne -2 permet d'exclure les bords
        // On définit aléatoirement le numéro de colonne où se trouve le joueur
        $positionColonneRobot = rand(1, ($this->nbColonne - 2)); // de 1 à nbColonne -2 permet d'exclure les bords
        // On conserve ces valeurs dans une variable
        $positionRobot = [$positionLigneRobot, $positionColonneRobot];
        
        // On stock le résultat de la division entière par 2 du nombre de lignes et de colonnes total du labyrinthe.
        // Cela permet travailler sur le labyrinthe comme s'il était divisé en 4, 
        // afin de positionner la sortie en fonction de l'emplacement du robot
        $distanceMinimumLigne = intdiv($this->nbLigne, 2);
        $distanceMinimumColonne = intdiv($this->nbColonne, 2);
        // Si le robot se trouve dans le premier quart alors la sortie sera placée dans le dernier quart
        if( $positionLigneRobot <= $distanceMinimumLigne and $positionColonneRobot <= $distanceMinimumColonne )
        {
            // On définit aléatoirement la position de la sortie dans le dernier quart du labyrinthe
            $positionLigneSortie = rand($distanceMinimumLigne, ($this->nbLigne - 2));
            $positionColonneSortie = rand($distanceMinimumColonne, ($this->nbColonne - 2));
        }
        // Si le robot se trouve dans le second quart alors la sortie sera placée dans le troisième quart
        elseif( $positionLigneRobot <= $distanceMinimumLigne and $positionColonneRobot < $this->nbColonne )
        {
            // On définit aléatoirement la position de la sortie dans le troisième qaurt
            $positionLigneSortie = rand($distanceMinimumLigne, ($this->nbLigne - 2));
            $positionColonneSortie = rand(1, $distanceMinimumColonne);
        }
        // Si le robot se trouve dans le troisième quart alors la sortie sera placée dans le second quart
        elseif( $positionLigneRobot < $this->nbLigne and $positionColonneRobot <= $distanceMinimumColonne )
        {
            // On définit aléatoirement la position de la sortie dans le second qaurt
            $positionLigneSortie = rand(1, $distanceMinimumLigne);
            $positionColonneSortie = rand($distanceMinimumColonne, ($this->nbColonne - 2));
        }
        // Si le robot se trouve dans le dernier quart alors la sortie sera placée dans le premier quart
        elseif( $positionLigneRobot < $this->nbLigne and $positionColonneRobot < $this->nbColonne )
        {
            // On définit aléatoirement la position de la sortie dans le dernier qaurt
            $positionLigneSortie = rand(1, ($distanceMinimumLigne));
            $positionColonneSortie = rand(1, ($distanceMinimumColonne));
        }
        // On positionne la sortie dans le labyrinthe.
        $this->labyTableau[$positionLigneSortie][$positionColonneSortie] = "S";
        

        // On initialise un booléen permettant de déterminer si le robot à parcouru un chemin qui mène à la sortie
        $sortieProche = false;
        // Dans un premier temps on trace un chemin directe du robot vers la sortie
        while( !$sortieProche )
        {
            // Si la sortie est au dessu du robot, on fait monter le robot jusqu'à la ligne correspondante à celle de la sortie
            if( $positionLigneSortie <= $positionLigneRobot )
            {
                // On récupère le nombre de case (dans le sens bas-haut) qui séparent la sortie du robot
                $nbCase = abs($positionLigneRobot - $positionLigneSortie);
                // Pour chaque déplacement :
                for ($i=0; $i < $nbCase; $i++)
                {
                    // La ligne où se trouve le robot diminue de 1
                    $positionLigneRobot -= 1;
                    // Puis on s'assure que la case ne soit pas celle de la sortie
                    if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                    {
                        // La case devient alors vide
                        $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                        // Le nombre de cases vides augmente de +1
                        $this->nbCasesVides += 1;
                    }
                }
                
            }
            // Si la sortie est en dessou du robot, on fait descendre le robot jusqu'à la ligne correspondante à celle de la sortie
            elseif( $positionLigneSortie >= $positionLigneRobot )
            {
                // On récupère le nombre de case (dans le sens haut-bas) qui séparent la sortie du robot
                $nbCase = abs($positionLigneSortie - $positionLigneRobot);
                // Pour chaque déplacement :
                for ($i=0; $i < $nbCase; $i++)
                {
                    // La ligne où se trouve le robot augmente de 1
                    $positionLigneRobot += 1;
                    // Puis on s'assure que la case ne soit pas celle de la sortie
                    if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                    {
                        // La case devient alors vide
                        $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                        // Le nombre de cases vides augmente de +1
                        $this->nbCasesVides += 1;
                    }
                }
                
            }

            // Si la sortie est à gauche du robot, on déplace le robot vers la droite jusqu'à la colonne correspondante à celle de la sortie
            if( $positionColonneSortie <= $positionColonneRobot )
            {
                // On récupère le nombre de case (dans le sens droite-gauche) qui séparent la sortie du robot
                $nbCase =  abs($positionColonneRobot - $positionColonneSortie) - 1;
                // Pour chaque déplacement :
                for ($i=0; $i < $nbCase; $i++)
                {
                    // La colonne où se trouve le robot diminue de 1
                    $positionColonneRobot -= 1;
                    // Puis on s'assure que la case ne soit pas celle de la sortie
                    if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                    {
                        // La case devient alors vide
                        $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                        // Le nombre de cases vides augmente de +1
                        $this->nbCasesVides += 1;

                        
                    }
                }
                $sortieProche = $this->sortieProche($this->labyTableau, $positionLigneRobot, $positionColonneRobot);
            }
            // Si la sortie est à droite du robot, on déplace le robot vers la gauche jusqu'à la colonne correspondante à celle de la sortie
            elseif( $positionColonneSortie >= $positionColonneRobot )
            {
                // On récupère le nombre de case (dans le sens gauche-droite) qui séparent la sortie du robot
                $nbCase = abs($positionColonneSortie - $positionColonneRobot) - 1;
                // Pour chaque déplacement :
                for ($i=0; $i < $nbCase; $i++)
                {
                    // La colonne où se trouve le robot augmente de 1
                    $positionColonneRobot += 1;
                    // Puis on s'assure que la case ne soit pas celle de la sortie
                    if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                    {
                        // La case devient alors vide
                        $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                        // Le nombre de cases vides augmente de +1
                        $this->nbCasesVides += 1;
                    }
                }
                // Si la sortie est proche de l'emplacement actuel du robot on s'arrète
                $sortieProche = $this->sortieProche($this->labyTableau, $positionLigneRobot, $positionColonneRobot);
            }
        }

        // On replace le robot à sa position initiale
        $this->labyTableau[$positionRobot[0]][$positionRobot[1]] = "X";
        $positionLigneRobot = $positionRobot[0];
        $positionColonneRobot = $positionRobot[1];

        // Si $difficulte = 1 alors le pourcentage de case vide doit être inférieur à 20%
        if( $difficulteChoisie === 1 ) $difficulte = 20;
        elseif( $difficulteChoisie === 2 ) $difficulte = 35;
        elseif( $difficulteChoisie === 3 ) $difficulte = 60;

        // Tant que le pourcentage de cases vides n'a pas atteint le pourcentage correspondant au niveau de difficulté 
        // On déplace le robot de manière aléatoire pour vider le labyrinthe
        while( (($this->nbCasesVides * 100) / ($this->nbLigne * $this->nbColonne)) < $difficulte )
        {
            // On génère aléatoirement un nombre entre 1 et 4, chaque valeur représente une des 4 directions
            $direction = rand(1, 4);
            
            // Si direction = 1 : Déplacement vers le BAS 
            // ET que le robot ne se trouve pas déjà sur l'avant dernière ligne (la dernière est obligatoirement une ligne de murs)
            if( ($direction === 1) and ($positionLigneRobot < ($this->nbLigne - 2)) )
            {
                // On calcule le nombre de déplacement maximum que le robot peut effectuer sans sortir des limites du labyrinthe
                // $nbDeplacementMax = nbLigneTotale - la position actuelle +2 (+2 pour exclure la première et dernière ligne)
                $nbDeplacementMax = $this->nbLigne - ($positionLigneRobot + 2);

                // Si le nbDeplacementMax n'est pas de 0
                if( $nbDeplacementMax !== 0 )
                {
                    // On génère aléatoirement un nombre de déplacement dans cette direction compris entre 1 et nbDeplacementMax
                    $nbCase = rand(1, $nbDeplacementMax);
                    // Pour chaque déplacement :
                    for ($i=0; $i < $nbCase; $i++)
                    {
                        // La ligne où se trouve le robot augmente de 1
                        $positionLigneRobot += 1;

                        // On vérifie si la case n'est pas interdite :
                        if( !in_array(array($positionLigneRobot, $positionColonneRobot), $this->listeCasesInterdites) )
                        {
                            // Puis on s'assure que la case ne soit pas celle de la sortie
                            if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                            {
                                // La case devient alors vide
                                $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                                // Le nombre de cases vides augmente de +1
                                $this->nbCasesVides += 1;
                            }
                            
                            // On vérifie dans les 4 directions si la sortie est à proximité
                            $sortieProche = $this->sortieProche($this->labyTableau, $positionLigneRobot, $positionColonneRobot);
                            if( $sortieProche )
                            {
                                // Si la sortie est proche on stoppe la boucle for pour générer une nouvelle direction
                                break;
                            }
                        }
                        else
                        {
                            // Si la case est interdite on stoppe la boucle for pour générer une nouvelle direction
                            break;
                        }
                        // On met à jour la liste des cases interdites à chaque déplacement
                        $this->chercheCasesInterdites($positionLigneRobot, $positionColonneRobot);
                    }
                }
            }
        
            // Si direction = 2 : Déplacement vers le HAUT 
            // ET que le robot ne se trouve pas déjà sur seconde ligne (la première est obligatoirement une ligne de murs)
            elseif( ($direction === 2) and ($positionLigneRobot > 1))
            {
                // On génère aléatoirement un nombre de déplacement dans cette direction 
                // compris entre 1 et position actuelle -2 (-2 pour exclure la première et dernière ligne)
                $nbCase = rand(1, ($positionLigneRobot - 2));                
                // Pour chaque déplacement :
                for ($i=0; $i < $nbCase; $i++)
                {
                    // La ligne où se trouve le robot diminue de 1
                    $positionLigneRobot -= 1;

                    // On vérifie si la case n'est pas interdite :
                    if( !in_array(array($positionLigneRobot, $positionColonneRobot), $this->listeCasesInterdites) )
                    {
                        // Puis on s'assure que la prochaine case ne soit pas celle de la sortie
                        if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                        {
                            // La case devient alors vide
                            $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                            // Le nombre de cases vides augmente de +1
                            $this->nbCasesVides += 1;
                        }

                        // On vérifie dans les 4 directions si la sortie est à proximité
                        $sortieProche = $this->sortieProche($this->labyTableau, $positionLigneRobot, $positionColonneRobot);
                        if( $sortieProche ) 
                        {
                            // Si la sortie est proche on stoppe la boucle for pour générer une nouvelle direction
                            break;
                        }
                    }
                    else
                    {
                        // Si la case est interdite on stoppe la boucle for pour générer une nouvelle direction
                        break;
                    }
                    // On met à jour la liste des cases interdites à chaque déplacement
                    $this->chercheCasesInterdites($positionLigneRobot, $positionColonneRobot);
                }
            }
        
            // Si direction = 3 : Déplacement vers la DROITE 
            // ET que le robot ne se trouve pas déjà sur l'avant dernière colonne (la dernière est obligatoirement une colonne de murs)
            elseif( ($direction === 3) and ($positionColonneRobot < ($this->nbColonne - 2)) ) 
            {
                // On calcule le nombre de déplacement maximum que le robot peut effectuer sans sortir des limites du labyrinthe
                // $nbDeplacementMax = nbColonneTotale - la position actuelle +2 (+2 pour exclure la première et dernière colonne)
                $nbDeplacementMax = $this->nbColonne - ($positionColonneRobot + 2);

                // Si le nbDeplacementMax n'est pas de 0
                if( $nbDeplacementMax !== 0 )
                {
                    // On génère aléatoirement un nombre de déplacement dans cette direction compris entre 1 et nbDeplacementMax
                    $nbCase = rand(1, $nbDeplacementMax);
                    // Pour chaque déplacement :
                    for ($i=0; $i < $nbCase; $i++)
                    {
                        // La Colonne où se trouve le robot augmente de 1
                        $positionColonneRobot += 1;
                        
                        // On vérifie si la case n'est pas interdite :
                        if( !in_array(array($positionLigneRobot, $positionColonneRobot), $this->listeCasesInterdites) )
                        {
                            // On s'assure que la prochaine case ne soit pas celle de la sortie
                            if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                            {
                                // La case devient alors vide
                                $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                                // Le nombre de cases vides augmente de +1
                                $this->nbCasesVides += 1;
                            }
                            // On vérifie dans les 4 directions si la sortie est à proximité
                            $sortieProche = $this->sortieProche($this->labyTableau, $positionLigneRobot, $positionColonneRobot);
                            if( $sortieProche ) 
                            {
                                // Si la sortie est proche on stoppe la boucle for pour générer une nouvelle direction
                                break;
                            }
                        }
                        else
                        {
                            // Si la case est interdite on stoppe la boucle for pour générer une nouvelle direction
                            break;
                        }
                        // On met à jour la liste des cases interdites à chaque déplacement
                        $this->chercheCasesInterdites($positionLigneRobot, $positionColonneRobot);
                    }
                }        
            }
        
            // Si direction = 4 : Déplacement vers la GAUCHE 
            // ET que le robot ne se trouve pas déjà sur la seconde colonne (la première est obligatoirement une colonne de murs)
            elseif( ($direction === 4) and ($positionColonneRobot > 1) )
            {
                // On génère aléatoirement un nombre de déplacement dans cette direction 
                // compris entre 1 et position actuelle -2 (-2 pour exclure la première et dernière colonne)
                $nbCase = rand(1, ($positionColonneRobot - 2));
                // Pour chaque déplacement :
                for ($i=0; $i < $nbCase; $i++)
                {
                    // La Colonne où se trouve le robot diminue de 1
                    $positionColonneRobot -= 1;

                    // On vérifie si la case n'est pas interdite :
                    if( !in_array(array($positionLigneRobot, $positionColonneRobot), $this->listeCasesInterdites) )
                    {
                        // On s'assure que la prochaine case ne soit pas celle de la sortie
                        if( $this->labyTableau[$positionLigneRobot][$positionColonneRobot] !== "S" )
                        {
                            // La case devient alors vide
                            $this->labyTableau[$positionLigneRobot][$positionColonneRobot] = " ";
                            // Le nombre de cases vides augmente de +1
                            $this->nbCasesVides += 1;
                        }
                        // On vérifie dans les 4 directions si la sortie est à proximité   
                        $sortieProche = $this->sortieProche($this->labyTableau, $positionLigneRobot, $positionColonneRobot);
                        if( $sortieProche ) 
                        {
                            // Si la sortie est proche on stoppe la boucle for pour générer une nouvelle direction
                            break;
                        }
                    }
                    else
                    {
                        // Si la case est interdite on stoppe la boucle for pour générer une nouvelle direction
                        break;
                    }
                    // On met à jour la liste des cases interdites à chaque déplacement
                    $this->chercheCasesInterdites($positionLigneRobot, $positionColonneRobot);
                }
            }
        }
        // On replace le robot à sa position initiale
        $this->labyTableau[$positionRobot[0]][$positionRobot[1]] = "X";
    }



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                    Méthodes utiles à la gestion du jeu du labyrinthe                                            //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
        Cette méthode retourne le labyrinthe de type tableau
    */
    public function getLabyTableau()
    {
        return $this->labyTableau;
    }
    

    /*
        Cette méthode permet de déplacer le robot
        D'abord on récupère dans la session la carte du labyrinthe sous sa forme tableau de chaine de caractères que l'on convertit 
        en sa forme tableau d'objet UneCase.
        Puis on créer l'objet robot afin de pouvoir le déplacer sur la carte.
        Enfin on met à jour la session avec la carte contenant le nouvel emplacement du robot.
        ENTREE : la page html générée (pour les messages concernant les mauvais déplacements du robot), et la connexion à la base
                de données pour la gestion des scores
    */
    public function deplacerRobot($page, $connexion)
    {
        // On récupère la carte du labyrinthe sous sa forme tableau d'objet
        $this->conversionLabyTableauVersObjet($_SESSION["carte"]);
        // On créé l'objet robot
        $robot = new Robot($page, $connexion, $this->labyTableauObjet);
        // On déplace le robot en surveillant la tape sur le clavier (voir labyrinthe.js)
        $robot->deplacer();
        // On met à jour la carte
        $_SESSION['carte'] = $this->conversionLabyObjetVersTableau($this->labyTableauObjet);
    }
    

    /*
        Cette méthode permet de convertir le labyrinthe sous forme d'un tableau de tableau de caractères vers son format 
        tableau d'objet "UneCase".
        ENTREE : le labyrinthe sous forme d'un tableau de tableau de caractères
        SORTIE : le labyrinthe sous forme d'un tableau d'objet "UneCase"
    */
    public function conversionLabyTableauVersObjet($tableau)
    {
        // On met à jour l'attribut labyTableau de l'objet Labyrinthe
        $this->labyTableau = $tableau;
        // On instancie l'attribut labyTableauObjet
        $this->labyTableauObjet = [];
        // On parcourt le labyrinthe sous forme d'un tableau qui contient par exemple : [[OOOOOOOOO], [O OO  O O] ...(x nligne)]
        foreach ($this->labyTableau as $numeroLigne => $ligne)
        {
            // Boucle for permettant de ne pas tenir compte des retours à la ligne pour créer nos cases
            for ($i = 0; $i < $this->nbColonne; $i++) 
            {
                // On crée l'objet UneCase en fonction du caratère passé en paramètre exemple: $this->labyTableau[$numeroLigne][$i] = "O"
                $this->labyTableauObjet[$numeroLigne][$i] = new UneCase($this->labyTableau[$numeroLigne][$i]);
            }
        }
    }

    
     /*
        Cette méthode permet de convertir le labyrinthe sous forme d'un tableau d'objet "UneCase" vers son format 
        tableau de tableau de caractères.
        Cette méthode est utilisée pour mettre à jour la $_SESSION['carte'] qui permet l'affichage du labyrinthe.
        Dans un premier temps, on récupère donc la liste des cases rendues visible par la position (ou déplacement) du robot.
        Cette liste des cases visibles est stockée dans la $_SESSION['listeCasesVisibles'].
        ENTREE : le labyrinthe sous forme d'un tableau d'objet "UneCase"
        SORTIE : le labyrinthe sous forme d'un tableau de tableau de caractères
    */
    public function conversionLabyObjetVersTableau($labyObjet)
    {
        // On parcourt $_SESSION['listeCasesVisibles'] qui contient la liste des positions des cases visibles,
        // stockée sous le format : [[ligne, colonne], [ligne, colonne], [ligne, colonne] ...etc...]
        // exemple : $_SESSION['listeCasesVisibles'] = [[5, 8], [4, 8], [5, 9], [4, 7], [5, 9], [6, 9] ...etc...]
        foreach($_SESSION['listeCasesVisibles'] as $cle => $valeur) 
        {
            // Chaque case stockée dans cette liste est rendue visible
            $labyObjet[$valeur[0]][$valeur[1]]->modifVisible();
        }

        // Puis on parcourt le labyrinthe sous forme d'un tableau (par exemple : [[OOOOOOOOO], [O OO  O O] ...(x nligne)])
        foreach($labyObjet as $numeroLigne => $ligne)
        {
            // Boucle for permettant de ne pas tenir compte des retours à la ligne pour créer nos cases
            for($i = 0; $i < $this->nbColonne; $i++) 
            {
                // l'attribut labyTableau est mis à jour avec le symbole correspondant à l'objet UneCase parcourut
                $this->labyTableau[$numeroLigne][$i] = $labyObjet[$numeroLigne][$i]->obtenirSymbole();
            }
        }
        return $this->labyTableau;
    }



    /*
        Méthode permettant de convertir l'ensemble du labyrinthe sous forme d'une image à afficher, 
            cette image affiche la solution du labyrinthe. A partir du tableau d'objet UneCase on récupère l'image correspondate 
            au symbole de chaque case, que l'on place sur l'image du labyrinthe, puis on encode l'image et retourne la balise HTML 
            contenant le lien vers l'image générée.
        ENTREE : aucune
        SORTIE : Le lien vers l'image du labyrinthe
    */
    public function solution()
    {
        // On récupère la carte que l'on convertie en tableau d'objet
        $this->conversionLabyTableauVersObjet($_SESSION["carte"]);
        // On crée l'image au format correspondant à la dimension choisie par l'utilisateur
        $image = imagecreatetruecolor($this->nbColonne*20, $this->nbLigne*20); // x20 car c'est la taille des images
        // On parcourt le tableau d'objet UneCase
        foreach ($this->labyTableauObjet as $numeroLigne => $ligne)
        {
            // Puis on parcourt chaque colonne
            foreach ($ligne as $colonne => $case)
            {
                // On récupère le symbole correspondant à la case
                $this->carte[$numeroLigne][$colonne] = $case->obtenirSymbole();
                $img_url = "";
                // En fonction du symbole, on récupère l'image correspondante
                switch ($this->carte[$numeroLigne][$colonne]) {
                    case "X": $img_url = "images/Labyrinthe/android-20x20.png"; break;
                    case "O": $img_url = "images/Labyrinthe/wall-20x20.png"; break;
                    case " ": $img_url = "images/Labyrinthe/vide-20x20.png"; break;
                    case "S": $img_url = "images/Labyrinthe/stairs-20x20.png"; break;
                }
                // On crée une nouvelle image depuis l'URL du fichier
                $sprite = imagecreatefrompng($img_url);
                // On positionne l'image correspondante au symbole sur l'image du labyrinthe
                imagecopy($image, $sprite, $colonne*20, $numeroLigne*20, 0, 0, 20, 20);
                // On détruit l'image correspondante au symbole pour passer au suivant
                imagedestroy($sprite);
            } 
            // On convertie une ligne en chaine de caractères
            $this->carte[$numeroLigne] = implode($this->carte[$numeroLigne]);

        }
        // ob_start : démarre la temporisation de sortie. Tant qu'elle est enclenchée, aucune donnée, 
        // hormis les en-têtes, n'est envoyée au navigateur, mais temporairement mise en tampon.
        ob_start();
        // On convertie l'image en format png
        imagepng($image);
        // On récupère les données de l'image à partir du contenu du tampon
        $image_data = ob_get_contents(); 
        // On détruit les données du tampon
        ob_end_clean();
        // On détruit l'image
        imagedestroy($image);
        // On encode les données de l'image en base64
        $data = 'data:image/png;base64,'. base64_encode($image_data);
        // On créer le lien vers l'image
        $lienImage = '<img src="' . $data . '" alt="laby" />';
        // On retourne la balise html contenant l'image pour affichage
        return $lienImage; 
    }

    
    /*
        Méthode permettant de convertir le labyrinthe sous forme d'une image à afficher. 
            A partir du tableau d'objet UneCase on récupère l'image correspondante 
            au symbole de chaque case, que l'on place sur l'image du labyrinthe, puis on encode l'image et retourne la balise HTML 
            contenant le lien vers l'image générée.
        ENTREE : aucune
        SORTIE : Le lien vers l'image du labyrinthe
    */
    public function conversionLabyObjetVersImage() : string
    {
        // On crée l'image au format correspondant à la dimension choisie par l'utilisateur
        $image = imagecreatetruecolor($this->nbColonne*20, $this->nbLigne*20); // x20 car c'est la taille des images
        // On parcourt chaque ligne
        foreach ($this->labyTableauObjet as $numeroLigne => $ligne)
        {
            // Puis on parcourt chaque colonne
            foreach ($ligne as $colonne => $case)
            {
                // Si l'objet est visible on récupère le symbole correspondant
                if( $case->obtenirVisible() )
                {
                    $this->carte[$numeroLigne][$colonne] = $case->obtenirSymbole();
                    $img_url = "";
                    // En fonction du symbole, on récupère l'image correspondante
                    switch ($this->carte[$numeroLigne][$colonne]) {
                        case "X": $img_url = "images/Labyrinthe/android-20x20.png"; break;
                        case "O": $img_url = "images/Labyrinthe/wall-20x20.png"; break;
                        case " ": $img_url = "images/Labyrinthe/vide-20x20.png"; break;
                        case "S": $img_url = "images/Labyrinthe/stairs-20x20.png"; break;
                    }
                    // On crée une nouvelle image depuis l'URL du fichier
                    $sprite = imagecreatefrompng($img_url);
                    // On positionne l'image correspondante au symbole sur l'image du labyrinthe
                    imagecopy($image, $sprite, $colonne*20, $numeroLigne*20, 0, 0, 20, 20);
                    // On détruit l'image correspondante au symbole pour passer au suivant
                    imagedestroy($sprite);
                }
                // Sinon on le remplace par du vide
                else 
                {
                    $this->carte[$numeroLigne][$colonne] = " ";
                }
            } 
            // On convertie une ligne en chaine de caractère
            $this->carte[$numeroLigne] = implode($this->carte[$numeroLigne]);

        }
        // ob_start : démarre la temporisation de sortie. Tant qu'elle est enclenchée, aucune donnée, 
        // hormis les en-têtes, n'est envoyée au navigateur, mais temporairement mise en tampon.
        ob_start();
        // On convertie l'image en format png
        imagepng($image);
        // On récupère les données de l'image à partir du contenu du tampon
        $image_data = ob_get_contents(); 
        // On détruit les données du tampon
        ob_end_clean();
        // On détruit l'image
        imagedestroy($image);
        // On encode les données de l'image en base64
        $data = 'data:image/png;base64,'. base64_encode($image_data);
        // On créer le lien vers l'image
        $lienImage = '<img src="' . $data . '" alt="laby" />';
        // On retourne la balise html contenant l'image pour affichage
        return $lienImage; 
        
    }

    /*
        Méthode permettant de rendre l'intégralité des cases visibles. Cette méthode est appelée lorsque le joueur
        a trouver la sortie, afin de révéler l'ensemble du labyrinthe lorsque la partie est terminée.
        ENTREE : aucune
        SORTIE : mise à jour de la $_SESSION['listeCasesVisibles']
    */
    public function obtenirLabyComplet()
    {
        // On parcourt chaque ligne
        foreach ($this->labyTableauObjet as $numeroLigne => $ligne)
        {
            // Puis on parcourt chaque case pour les rendre toutes visibles
            foreach ($ligne as $colonne => $case)
            {
                // Toutes les cases n'étant pas déjà dans la liste des cases visibles y sont ajoutées.
                if( !isset($_SESSION['listeCasesVisibles']) or !in_array(array($ligne, $colonne), $_SESSION['listeCasesVisibles']) )
                {
                    $case->modifVisible();
                    $_SESSION['listeCasesVisibles'][] = [$numeroLigne, $colonne];
                }
            }
        }
    }
}