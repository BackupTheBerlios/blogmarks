<?php
/** Déclaration de la classe Element_Mark
 * @version    $Id: Mark.php,v 1.1 2004/02/26 13:37:34 mbertier Exp $
 */

/** Objet représentant un bookmark.
 * @package    BlogMarks
 * @subpackage Elements
 */
class Element_Mark extends BlogMarks_Element {

    var $href = '';

    var $id = null;

    /** Date de création.
     * @var string    Une date au format ISO.*/
    var $created = '';

    /** Date de la dernière modification.
     * @var string    Une date au format ISO.*/
    var $modified = '';

    /** TAGS.
     * @var    array   un tableau de tags décrivant l'élément. */
    var $tags = array();

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function Element_Mark () {}
    
    
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>

