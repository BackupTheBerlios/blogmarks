<?php
/** Déclaration de la classe Element_Tag
 * @version    $Id: Tag.php,v 1.1 2004/03/01 13:20:52 benfle Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les tags BlogMarks.
 * @package    BlogMarks
 * @subpackage Elements
 */
class Element_Tag extends BlogMarks_Element {

  /**  Tag parent.
     * @var object Element_Tag */
    var $subTagOf;

    /**  Statut du tag (privé/public).
     * @var boolean */
    var $status;

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function Element_Tag () {}
    
    
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>