<?php
/** D�claration de la classe Element_Mark
 * @version    $Id: Mark.php,v 1.2 2004/03/01 13:21:29 benfle Exp $
 */

/** Objet repr�sentant un bookmark.
 * @package    BlogMarks
 * @subpackage Elements
 */
class Element_Mark extends BlogMarks_Element {

  /** Lien du blogmark.
   * @var Blogmarks_Link */
  var $link;

  /** Propri�taire du blogmark.
   * @var Blogmarks_User */
  var $author;

  /** URI de la copie d'�cran du site point�.
   * @var string */
  var $screenshot;

  /** Date de cr�ation.
   * @var string    Une date au format ISO.*/
  var $created = '';
  
  /** Date de la derni�re modification.
   * @var string    Une date au format ISO.*/
  var $modified = '';
  
  /** Date de publication du blogmark
   * @var string    Une date au format ISO. */
  var $issued = '';

  /** URI du site o� le lien a �t� trouv�.
   * @var string */
  var $via = '';

  /** TAGS.
   * @var    array   un tableau de Element_Tags d�crivant l'�l�ment. */
  var $tags = array();
  
# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #
  
  /** Constructeur. */
  function Element_Mark () {}
  
  /** Ajoute un tag au blogmark.
   * @param object Element_Tag tag � ajouter. 
   */
  function AddTag (&$tag) {}

  /** Supprimme un tag du blogmark. 
   * @param string identifiant du tag � supprimmer.
   */
  function DeleteTag ($tag_id) {}
  
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
  
    
}
?>

