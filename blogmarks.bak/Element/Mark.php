<?php
/** D�claration de la classe Element_Mark
 * @version    $Id: Mark.php,v 1.1 2004/02/26 13:37:34 mbertier Exp $
 */

/** Objet repr�sentant un bookmark.
 * @package    BlogMarks
 * @subpackage Elements
 */
class Element_Mark extends BlogMarks_Element {

    var $href = '';

    var $id = null;

    /** Date de cr�ation.
     * @var string    Une date au format ISO.*/
    var $created = '';

    /** Date de la derni�re modification.
     * @var string    Une date au format ISO.*/
    var $modified = '';

    /** TAGS.
     * @var    array   un tableau de tags d�crivant l'�l�ment. */
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

