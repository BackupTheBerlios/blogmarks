<?php
/** D�claration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.3 2004/03/02 17:29:14 mbertier Exp $
 */

# -- CONFIGURATION

# Libs
ini_set( 'include_path', ini_get('include_path') . ':' . '/home/mbertier/dev/PEAR_OVERLAY' );  

# Dataobjects
$config = parse_ini_file('config.ini',TRUE);
foreach( $config as $class => $values ) {
    $options =& PEAR::getStaticProperty( $class, 'options' );
    $options = $values;
}


# -- Includes
require_once 'blogmarks/Element/Factory.php';

/** Classe "m�tier". Effectue tous les traitements et op�rations.
 * @package    BlogMarks
 * @uses       Elements
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilis�s couramment par Marker.
     * @var array */
    var $slots = array();
    

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_Marker () {}

    
    /** Cr�ation d'un mark. 
     * @param      array     $props      Un tableau de param�tres d�crivant le mark.
     * @returns    string    L'URI du mark cr��.
     * @todo       TESTER!
     */
    function createMark( $props ) {
        $e =& $this->slots['element_factory']->makeElement( 'Mark', $props );
        $e->insert(); // + gestion des erreurs.
    }
    
    
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
}
?>

