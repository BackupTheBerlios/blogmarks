<?php
/** D�claration de la classe Element_Factory
 * @version    $Id: Factory.php,v 1.1 2004/03/02 17:29:14 mbertier Exp $
 * @todo       Passer les define dans les fichiers de conf
 * @todo       D�finir une liste d'�l�ments valides
 */


define( BM_ELEM_LOCATION, 'blogmarks/Element' );
define( BM_ELEM_PREFIX, 'Element_' );

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
     * @param      string      $elem_type      Le type de l'�l�ment � cr�er (nom de la classe -prefixe)
     * @param      array       $params         Tableau associatif dont les cl�s correspondent � des propri�t�s de l'objet.
     *                                         Les cl�s ne correspondant pas a des propri�t�s sont ignor�es
     * @returns    object      La r�f�rence � une instance d'�l�ment.
     */
    function &makeElement( $elem_type, $params ) {

        if ( ! $this->isRegisteredElement($elem_type) ) { return false; }

        // Inclusion de la classe
        require_once BM_ELEM_LOCATION . "/$elem_type.php";

        // D�finition du nom de la classe
        $class_name = BM_ELEM_PREFIX . $elem_type;

        // Instanciation
        $obj =& new $class_name;

        // Initialisation des propri�t�s
        // (j'ai l'impression que c pas la bonne m�thode).
        $class_vars = get_class_vars( get_class($obj) );
        foreach ( $params as $prop => $val ) {

            // On ignore les param�tres ne correspondant pas � des propri�t�s existantes.
            if ( array_key_exists($prop, $class_vars) ) {
                $obj->$prop = $val;
            }

        }

        return $obj;
    }
    

    /** Permet de savoir si un �l�ment est enregistr� (valide) ou non.
     * @param      string      $element_type       Le type de l'�l�ment (nom de la classe - pr�fixe)
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

