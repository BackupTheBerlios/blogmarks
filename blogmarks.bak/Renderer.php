<?php
/** D�claration de la classe BlogMarks_Renderer
 * @version    $Id: Renderer.php,v 1.1 2004/02/26 13:37:34 mbertier Exp $
 * @license    http://www.opensource.org/licenses/artistic-license.php
 */

/** Classe abstraite d�finissant les m�thodes
 *  � impl�menter pour cr�er un renderer de BlogMarks.
 *
 *
 * @todo    Il serait surement possible d'autog�n�rer cette classe abstraite
 *          � partir des classes du package Elements.
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

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_Renderer () {}


    /** DECORATOR. */
    function visit() {}
    

    /** Affichage de l'�l�ment. */
    function display() {}


# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>