<?php
/** D�claration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.6 2004/03/05 11:41:05 mbertier Exp $
 */

require_once 'PEAR.php';
require_once 'Blogmarks.php';

# -- CONFIGURATION

# Dataobjects
$config = parse_ini_file('/home/mbertier/dev/PEAR_OVERLAY/blogmarks/config.ini',TRUE);
foreach( $config as $class => $values ) {
    $options =& PEAR::getStaticProperty( $class, 'options' );
    $options = $values;
}


# -- Includes
require_once 'blogmarks/Element/Factory.php';

/** Classe "m�tier". Effectue tous les traitements et op�rations.
 * @package    Blogmarks
 * @uses       Element_Factory
 * @todo       Validation des param�tres dans les m�thodes publiques (et les autres m�mes ;)
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilis�s couramment par Marker.
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

    
    /** Cr�ation d'un mark. 
     * @param      array     $props      Un tableau associatif de param�tres d�crivant le mark.
     *                                   Les cl�s du tableau correpondent aux noms des champs de la base de donn�es.
     * @returns    string    L'URI du mark cr��.
     */
    function createMark( $props ) {

        // Instanciation et initialisation d'un Link
        $link =& $this->slots['ef']->makeElement( 'Bm_Links' );

        // Si le lien n'est pas d�j� enregistr�, on le fait.
        if ( $link->get( 'href', $props['href'] ) == 0 ) {

            // R�cup�ration d'infos suppl�mentaires � partir de l'URL
            $link->fetchUrlInfo();

            // Ajout du lien � la base de donn�es
            $link->insert();
        }


        // Cr�ation du Mark.
        $mark =& $this->slots['ef']->makeElement( 'Bm_Marks' );
        
        $mark->bm_Links_id = $link->id;
        $mark->bm_Users_id = 1;     // MOCK!

        // Si le Mark n'existe pas, on le cr�e
        if ( $mark->find(true) == 0 ) {

            // D�finition des propri�t�s
            $mark->title    = $props['title'];
            $mark->summary  = $props['summary'];
            $mark->lang     = $props['lang'];
            $mark->via      = $props['via'];

            // Dates
            $date = date("Ymd Hms");
            $mark->created  = $date;
            $mark->issued   = isset( $props['issued'] ) ? $props['issued'] : $date;
            $mark->modified = 0;

            // Insertion dans la base de donn�es
            $mark->insert();
        }
        

        // R�cup�ration de l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }
    

    /** Mise � jour d'un mark.
     * @param      int      $id       ID identifiant le mark
     * @param      array    $props    Un tableau de propri�t�s � mettre � jour.
     * @returns    string   L'uri du mark mis � jour.
     */
    function updateMark( $id, $props ) {
        $mark =& $this->slots['ef']->makeElement( 'Bm_Marks' );
        
        // Si le mark � mettre � jour n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( 404 );
        }
        
        // Mise � jour des propri�t�s
        $mark->title    = $props['title'];
        $mark->summary  = $props['summary'];
        $mark->lang     = $props['lang'];
        $mark->via      = $props['via'];
        
        // Dates
        $date = date("Ymd Hms");
        $mark->issued   = isset( $props['issued'] ) ? $props['issued'] : $mark->issued;
        $mark->modified = $date;
        
        // Insertion dans la base de donn�es
        $mark->update();
        
        // On renvoie l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }

    /** Suppression d'un mark.
     * @param     int      $id       URI identifiant le mark
     * @returns   mixed    true ou Blogmarks_Exception en cas d'erreur.
     */
    function deleteMark( $id ) {
        $mark =& $this->slots['ef']->makeElement( 'Bm_Marks' );
        
        // Si le mark � effacer n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le mark [$id] n'existe pas.", 404 );
        }

        // Suppression du Mark
        $mark->delete();

        return true;
    }

    /** G�n�ration de l'URI d'un Mark
     * @param     object     Element_Bm_Marks     Une r�f�rence � un Mark.
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
     * @access    private
     */
    function _initSlots() {

        // Array( slot_name, array(class_name, class_file) );
        $slots_info = array( 'ef' => array('Element_Factory', 'blogmarks/Element/Factory.php') );

        foreach ( $slots_info as $slot_name => $class_info ) {
            // Inclusion de la d�claration de la classe
            require_once $class_info[1];

            // Instanciation
            $obj =& new $class_info[0];
            
            $this->slots[$slot_name] = $obj;
            
        }

        return true;
        
    }
}
?>

