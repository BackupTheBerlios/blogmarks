<?php
/** D�claration de la classe Blogmarks_Element
 * @version    $Id: Element.php,v 1.5 2004/03/09 16:56:43 mbertier Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les �l�ments Blogmarks.
 * @package    Elements
 */
class Blogmarks_Element extends DB_DataObject {
  
# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #
  
  /** Constructeur. */
  function BlogMarks_Element () {}


  /** Initialisation de propri�t�s en fonction d'un tableau associatif.
   * Les valeurs des cl�s du tableau correspondant � une propri�t�s de l'objet
   * sont utilis�es pour la mise � jour. Les autres propri�t�s sont ignor�es.
   * 
   * @param      array      $props
   * @return
   */
  function populateProps( $props ) {

      // R�cup�ration des propri�t�s par d�faut de l'objet
      $my_props = get_class_vars( get_class($this) );

      foreach ( $props as $label => $value ) {
          // On ne modifie que les propri�t�s connues
          if ( array_key_exists($label, $my_props) ) {
              $this->$label = $value;
          }
      }

  }
  
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
  
}
?>

