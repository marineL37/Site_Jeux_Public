<?php
/*
    Cette classe permet d'initialiser un objet UneCase qui comporte différentes méthodes :
        - __construct()
        - obtenirTraversable()
        - obtenirVisible()
        - obtenirVisibilite()
        - obtenirGagne()
        - obtenirRobot()
        - obtenirSymbole()
        - modifRobot()
        - modifVisible()
*/

class UneCase
{
    const CASE_VIDE = " ";
    const CASE_MUR = "O";
    const CASE_ROBOT = "X";
    const CASE_SORTIE = "S";

    private $traversable;
    private $visible; // sert à l'affichage dynamique
    private $visibilite; // sert à l'affichage dynamique
    private $gagne;
    private $robot; 
    private $symboleAffiche;
    private $image;

    /*
        Méthode construisant l'objet Case qui est définit par :
        - son symbole à réafficher par la suite
        - son attribut robot : A la création aucune case ne peut contenir le robot
        - son attribut visible : A la création aucune case n'est visible
        - son attribut visibilité : est-il possible de voir à travers ?
        - son attribut traversable : est-il possible de le traverser ?
        - son attribut gagne : s'agit-il de la sortie ?
        En fonction de son symbole comparé à la constante on crée l'objet et ses attributs correspondant.
        ENTREE : le caractere stocké dans le fichier texte
     */
    public function __construct(string $symbole)
    {
        $this->symboleAffiche = $symbole;
        $this->visible = false;
        $this->robot = false;

        if ($symbole === self::CASE_MUR)
        {            
            $this->traversable = false; 
            $this->visibilite = false;
            $this->gagne = false;
        }
        elseif ($symbole === self::CASE_VIDE) 
        {
            $this->traversable = true;
            $this->visibilite = true;
            $this->gagne = false;
        } 
        elseif ($symbole === self::CASE_SORTIE) 
        {
            $this->traversable = true;
            $this->visibilite = false;
            $this->gagne = true;
        }
        elseif ($symbole === self::CASE_ROBOT) 
        {
            $this->robot = true;
            $this->visible = true;
            $this->visibilite = true;
        }
    }


    ////////////////////////// LES METHODES OBTENIR UN ATTRIBUT //////////////////////////
    /*
        Méthode renvoyant la valeur de l'attribut traversable.
        SORTIE : 
            - true : si l'objet est traversable (vide ou sortie).
            - false : si l'objet n'est pas traversable (murs).
    */
    public function obtenirTraversable(): bool
    {
        return $this->traversable;
    }


    /*
        Méthode renvoyant la valeur de l'attribut visible.
        SORTIE : 
            - true : si l'objet a été rendu visible par le parcourt du labyrinthe.
            - false : si l'objet n'a pas été approché à au moins 2 cases de distance si bonne visibilité.
    */
    public function obtenirVisible(): bool

    {
        return $this->visible;
    }


    /*
        Méthode renvoyant la valeur de l'attribut visibilité.
        Cette méthode peux servir à distinguer les cases vides des autres cases.
        SORTIE : 
            - true : si l'objet permet de voir à travers (les cases vides).
            - false : si l'objet permet de voir à travers (murs).
    */
    public function obtenirVisibilite(): bool
    {
        return $this->visibilite;
    }


    /*
        Méthode renvoyant la valeur de l'attribut gagne.
        SORTIE : 
            - true : si l'objet correspond à la sortie du labyrinthe.
            - false : si l'objet n'est pas la sortie.
    */
    public function obtenirGagne(): bool
    {
        return $this->gagne;
    }


    /*
        Méthode renvoyant la valeur de l'attribut robot.
        Le positionnement du robot étant alétoire est gérer par la class Labyrinthe, l'attribut robot est créé 
        par la méthode "modifRobot" (voir ci-dessou)
        SORTIE : 
            - true : si l'objet correspond au robot.
            - false : si l'objet n'est pas le robot.
    */
    public function obtenirRobot(): bool
    {
        return $this->robot;
    }


    /*
        Méthode renvoyant le symbole correspondant à l'objet case pour permettre l'affichage.
        SORTIE : un caractère correspondant à l'une des constantes en fonction de ses attributs.
     */
    public function obtenirSymbole(): string
    {
        return $this->symboleAffiche;
    }


    ////////////////////////// LES METHODES DE MODIFICATION DES ATTRIBUTS //////////////////////////
    /*
        Méthode permettant d'ajouter ou de supprimer l'attribut robot sur la case.
        ENTREE : la nouvelle valeur que dois prendre l'attribut robot
            - true : le robot se trouve sur la case.
            - false : le robot ne se trouve pas sur la case
     */
    public function modifRobot(bool $nouvelValeur)
    {
        $this->robot = $nouvelValeur;
        if( $nouvelValeur ) 
        {
            $this->symboleAffiche = self::CASE_ROBOT;
        } 
        else 
        {
            $this->symboleAffiche = self::CASE_VIDE;
        }
    }


    /*
        Méthode permettant de modifier l'attribut visible, afin de
        permettre d'afficher dynamiquement le labyrinthe en fonction des déplacements du robot (voir class Robot).
        Aucune entrée car toutes les cases ne sont pas visible au début et cette méthode permet de les rendres visibles.
     */
    public function modifVisible()
    {
        $this->visible = true;
    }
}
