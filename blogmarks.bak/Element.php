<?php
/** D�claration de la classe BlogMarks_Element
 * @version    $Id: Element.php,v 1.1 2004/02/26 13:37:34 mbertier Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les �l�ments BlogMarks.
 * @package    BlogMarks
 * @subpackage Elements
 */
class BlogMarks_Element extends DB_DataObject {

    /**  Le titre de l'�l�ment.
     * @var string */
    var $title = '';

    /** Courte description de l'�l�ment.
     * @var string */
    var $desc = '';

    /** Langue de l'�l�ment.
     * @var string */
    var $lang = '';
    

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_Element () {}
    
    
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>

