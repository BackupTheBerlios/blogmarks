<?php
/** Déclaration de la classe BlogMarks_Link
 * @version    $Id: Link.php,v 1.1 2004/03/01 13:18:57 benfle Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les liens BlogMarks.
 * @package    BlogMarks
 */
class BlogMarks_Link extends DB_DataObject {

  /**  L'URI du lien.
   * @var string */
  var $href = '';
  
  /**  Le titre de la ressource pointée.
   * @var string */
  var $title = '';

  /** Le type MIME de la ressource pointée.
   * @var string */
  var $type = '';

  /** Langue de la ressource pointée.
   * @var string */
  var $lang = '';
    

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

  /** Constructeur. */
  function BlogMarks_Link ($href, $title, $type, $lang) {
    $this->href  = $href;
    $this->title = $title;
    $this->type  = $type;
    $this->lang  = $lang;
  }
  
  /** Construit un lien à partir d'un URI. */
  function LinkFromUri ($uri) {
    // envoit une requête GET sur $uri pour recupere title, type et lang
    $this->href = $uri;
  }

  /** Enregistre le lien dans la base de données */
  function saveLink () {
    global $db;  
    return $db->query("INSERT INTO bm_links VALUES ($this->href, 
                       $this->title, $this->type, $this->lang)");
  }

  /** supprimme un lien de la base */
  function deleteLink($href) {
    global $db;
    return $db->query("DELETE FROM bm_links WHERE href = $href");
  }


# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>