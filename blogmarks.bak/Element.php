<?php
/** Déclaration de la classe BlogMarks_Element
 * @version    $Id: Element.php,v 1.2 2004/03/01 13:21:29 benfle Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les éléments BlogMarks.
 * @package    BlogMarks
 * @subpackage Elements
 */
class BlogMarks_Element extends DB_DataObject {
  
  /**  L'identifiant de l'élément (URI).
   * @var string */
  var $id = '';
  
  /**  Le titre de l'élément.
   * @var string */
  var $title = '';
  
  /** Courte description de l'élément.
   * @var string */
  var $summary = '';
  
  /** Langue de l'élément.
   * @var string */
  var $lang = '';
  

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #
  
  /** Constructeur. */
  function BlogMarks_Element ($id, $title, $summary, $lang) {
    $this->id      = $id;
    $this->title   = $title;
    $this->summary = $summary;
    $this->lang    = $lang;
  }
  
  
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
  
}
?>

