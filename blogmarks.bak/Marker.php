<?php
/** Déclaration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.5 2004/03/04 16:30:36 mbertier Exp $
 */

require_once 'PEAR.php';

# -- CONFIGURATION

# Dataobjects
$config = parse_ini_file('/home/mbertier/dev/PEAR_OVERLAY/blogmarks/config.ini',TRUE);
foreach( $config as $class => $values ) {
    $options =& PEAR::getStaticProperty( $class, 'options' );
    $options = $values;
}


# -- Includes
require_once 'blogmarks/Element/Factory.php';

/** Classe "métier". Effectue tous les traitements et opérations.
 * @package    BlogMarks
 * @uses       Elements
 * @todo       Validation des paramètres dans les méthodes publiques (et les autres mêmes ;)
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilisés couramment par Marker.
     * @var array */
    var $slots = array();
    

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_Marker () {
        // Initialisation des slots
        $this->_initSlots();
    }

    
    /** Création d'un mark. 
     * @param      array     $props      Un tableau associatif de paramètres décrivant le mark.
     *                                   Les clés du tableau correpondent aux noms des champs de la base de données.
     * @returns    string    L'URI du mark créé.
     */
    function createMark( $props ) {

        // Instanciation et initialisation d'un Link
        $link =& $this->slots['ef']->makeElement( 'Bm_Links' );

        // Si le lien n'est pas déjà enregistré, on le fait.
        if ( $link->get( 'href', $props['href'] ) == 0 ) {

            // Récupération d'infos supplémentaires à partir de l'URL
            $link->fetchUrlInfo();

            // Ajout du lien à la base de données
            $link->insert();
        }


        // Création du Mark.
        $mark =& $this->slots['ef']->makeElement( 'Bm_Marks' );
        
        $mark->bm_Links_id = $link->id;
        $mark->bm_Users_id = 1;     // MOCK!

        // Si le Mark n'existe pas, on le crée
        if ( $mark->find(true) == 0 ) {

            // Définition des propriétés
            $mark->title    = $props['title'];
            $mark->summary  = $props['summary'];
            $mark->lang     = $props['lang'];
            $mark->via      = $props['via'];

            // Dates
            $date = date("Ymd Hms");
            $mark->created  = $date;
            $mark->issued   = isset( $props['issued'] ) ? $props['issued'] : $date;
            $mark->modified = 0;

            // Insertion dans la base de données
            $mark->insert();
        }
        

        // Récupération de l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }
    

    /** Mise à jour d'un mark.
     * @param      string   $uri      URI identifiant le mark
     * @param      array    $props    Un tableau de propriétés à mettre à jour.
     * @returns    
     */
    function updateMark( $uri, $props ) {
        $e =& $this->slots['ef']->makeElement( 'Mark' );

    }

    /** Suppression d'un mark.
     * @param     string   $uri      URI identifiant le mark
     * @param     array    $props    Un tableau de propriétés à mettre à jour.    
     */
    function deleteMark( $uri, $props ) {
        
    }

    /** Génération de l'URI d'un Mark
     * @param     object     Element_Bm_Marks     Une référence à un Mark.
     * @returns   string     L'URI du Mark.
     */
    function getMarkUri( &$mark ) {
        $pattern = 'http://www.blogmarks.net/users/%s/?mark_id=%u';
        $uri = sprintf( $pattern, "MOCK!", $mark->id );

        return $uri;
    }

# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #

    /** Initialisation des slots. 
     */
    function _initSlots() {

        // Array( slot_name, array(class_name, class_file) );
        $slots_info = array( 'ef' => array('Element_Factory', 'blogmarks/Element/Factory.php') );

        foreach ( $slots_info as $slot_name => $class_info ) {
            // Inclusion de la déclaration de la classe
            require_once $class_info[1];

            // Instanciation
            $obj =& new $class_info[0];
            
            $this->slots[$slot_name] = $obj;
            
        }

        return true;
        
    }
}
?>

