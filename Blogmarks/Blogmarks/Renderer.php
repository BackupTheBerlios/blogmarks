<?php
/** Déclaration de la classe BlogMarks_Renderer
 * @version    $Id: Renderer.php,v 1.1 2004/05/19 13:00:21 benfle Exp $
 * @license    http://www.opensource.org/licenses/artistic-license.php
 */

/** Classe abstraite définissant les méthodes
 *  à implémenter pour créer un renderer de BlogMarks.
 *
 *
 * @todo    Il serait surement possible d'autogénérer cette classe abstraite
 *          à partir des classes du package Elements.
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


  /** DECORATOR. */
  function visit() {}
    

  /** Affichage de l'élément. */
  function render() {}


# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>