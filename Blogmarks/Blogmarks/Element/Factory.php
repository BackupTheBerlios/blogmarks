<?php
/** Déclaration de la classe Element_Factory
 * @version    $Id: Factory.php,v 1.2 2004/06/25 12:14:26 benfle Exp $
 * @todo       Passer les define dans les fichiers de conf
 * @todo       Définir une liste d'éléments valides
 */


define( 'BM_ELEM_LOCATION', 'Blogmarks/Element' );
define( 'BM_ELEM_PREFIX', 'Element_' );

/** Factory d'elements (...)
 * @package    Elements
 * @see        http://www.phppatterns.com/index.php/article/articleview/49/1/1/
 */
class Element_Factory {

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function Element_Factory () {}
    
    /** 
     * @param      string      $elem_type      Le type de l'élément à créer (nom de la classe -prefixe)
     * @param      array       $params         Tableau associatif dont les clés correspondent à des propriétés de l'objet.
     *                                         Les clés ne correspondant pas a des propriétés sont ignorées
     * @returns    object      La référence à une instance d'élément.
     */
    function &makeElement( $elem_type, $params = array() ) {

        //if ( ! $this->isRegisteredElement($elem_type) ) { return false; }

        // Inclusion de la classe
        require_once BM_ELEM_LOCATION . "/$elem_type.php";

        // Définition du nom de la classe
        $class_name = BM_ELEM_PREFIX . $elem_type;

        // Instanciation
        $obj =& new $class_name;

        // Initialisation des propriétés
        $obj->populateProps( $params );

        return $obj;
    }
    

    /** Permet de savoir si un élément est enregistré (valide) ou non.
     * @param      string      $element_type       Le type de l'élément (nom de la classe - préfixe)
     * @returns    bool
     */
    function isRegisteredElement( $elem_type ) {
        
        return true;
    }
# ----------------------- #
# -- methodes PRIVEES   --#
# ----------------------- #
    
    
}
?>