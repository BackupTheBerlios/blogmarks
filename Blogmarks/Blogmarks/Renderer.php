<?php
/** D�claration de la classe BlogMarks_Renderer
 * @version    $Id: Renderer.php,v 1.3 2004/06/25 12:14:26 benfle Exp $
 * @license    http://www.opensource.org/licenses/artistic-license.php
 */

/** Classe abstraite d�finissant les m�thodes
 *  � impl�menter pour cr�er un renderer de BlogMarks.
 *
 *
 * @package    Renderers
 * @author     BlogMarksTeam <dev@blogmarks.net>
 */
class BlogMarks_Renderer {

	/** R�f�rence � l'instance de l'objet d�cor�.
	 * @var    object BlogMarks_List
	 * @access private
     */
	var $_decorated = null;

	/** R�f�rence � la d�coration (r�sultat de visit)
     * @var    objet quelconque
     * @access private
     */
	var $_decoration = null;

	/** R�f�rence � l'instance de la classe m�tier
	 * @var     object Blogmarks_Marker
	 * @access  private
	 */
	var $_marker = null;

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

  /** Constructeur. */
  function BlogMarks_Renderer () {}


  /** DECORATOR. 
   * @param      object Blogmarks_Element      $element
   */
  function visit( &$element ) {}
    

  /** Affichage de l'�l�ment. */
  function render() {}


# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>