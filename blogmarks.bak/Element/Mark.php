<?php
/** Déclaration de la classe Element_Mark
 * @version    $Id: Mark.php,v 1.2 2004/03/01 13:21:29 benfle Exp $
 */

/** Objet représentant un bookmark.
 * @package    BlogMarks
 * @subpackage Elements
 */
class Element_Mark extends BlogMarks_Element {

  /** Lien du blogmark.
   * @var Blogmarks_Link */
  var $link;

  /** Propriétaire du blogmark.
   * @var Blogmarks_User */
  var $author;

  /** URI de la copie d'écran du site pointé.
   * @var string */
  var $screenshot;

  /** Date de création.
   * @var string    Une date au format ISO.*/
  var $created = '';
  
  /** Date de la dernière modification.
   * @var string    Une date au format ISO.*/
  var $modified = '';
  
  /** Date de publication du blogmark
   * @var string    Une date au format ISO. */
  var $issued = '';

  /** URI du site où le lien a été trouvé.
   * @var string */
  var $via = '';

  /** TAGS.
   * @var    array   un tableau de Element_Tags décrivant l'élément. */
  var $tags = array();
  
# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #
  
  /** Constructeur. */
  function Element_Mark () {}
  
  /** Ajoute un tag au blogmark.
   * @param object Element_Tag tag à ajouter. 
   */
  function AddTag (&$tag) {}

  /** Supprimme un tag du blogmark. 
   * @param string identifiant du tag à supprimmer.
   */
  function DeleteTag ($tag_id) {}
  
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
  
    
}
?>

