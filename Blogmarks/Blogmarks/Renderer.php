<?php
/** Déclaration de la classe BlogMarks_Renderer
 * @version    $Id: Renderer.php,v 1.2 2004/05/19 13:06:32 mbertier Exp $
 * @license    http://www.opensource.org/licenses/artistic-license.php
 */

/** Classe abstraite définissant les méthodes
 *  à implémenter pour créer un renderer de BlogMarks.
 *
 *
 * @package    Renderers
 * @author     BlogMarksTeam <dev@blogmarks.net>
 */
class BlogMarks_Renderer {

  /** Référence à l'instance de l'objet décoré.
   * @var    object BlogMarks_List
   * @access private
   */
  var $_decorated = null;

  /** Référence à la décoration (résultat de visit)
   * @var    objet quelconque
   * @access private
   */
  var $_decoration = null;

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

  /** Constructeur. */
  function BlogMarks_Renderer () {}


  /** DECORATOR. 
   * @param      object Blogmarks_Element      $element
   */
  function visit( &$element ) {}
    

  /** Affichage de l'élément. */
  function render() {}


# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>