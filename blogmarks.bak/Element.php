<?php
/** Déclaration de la classe Blogmarks_Element
 * @version    $Id: Element.php,v 1.5 2004/03/09 16:56:43 mbertier Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les éléments Blogmarks.
 * @package    Elements
 */
class Blogmarks_Element extends DB_DataObject {
  
# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #
  
  /** Constructeur. */
  function BlogMarks_Element () {}


  /** Initialisation de propriétés en fonction d'un tableau associatif.
   * Les valeurs des clés du tableau correspondant à une propriétés de l'objet
   * sont utilisées pour la mise à jour. Les autres propriétés sont ignorées.
   * 
   * @param      array      $props
   * @return
   */
  function populateProps( $props ) {

      // Récupération des propriétés par défaut de l'objet
      $my_props = get_class_vars( get_class($this) );

      foreach ( $props as $label => $value ) {
          // On ne modifie que les propriétés connues
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

