<?php
/** Déclaration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.2 2004/03/01 17:35:15 mbertier Exp $
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

# -- INCLUDES
require_once 'Element/Bm_Marks';

# -- 
define( ELEM_MARK, 'Bm_Marks' );

/** Classe "métier". Effectue tous les traitements et opérations.
 * @package    BlogMarks
 * @uses       Elements
 */
class BlogMarks_Marker {



# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_Marker () {}

    
    /** Création d'un mark. 
     * @param      array     $props      Un tableau de paramètres décrivant le mark.
     * @returns    string    L'URI du mark créé.
     */
    function createMark( $props ) {
        // WOW g super bien avancé 
        $CLASSNAME = ELEM_MARK;
        $m = new $CLASSNAME;

        return $mark_uri;
    }
    
    
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>

