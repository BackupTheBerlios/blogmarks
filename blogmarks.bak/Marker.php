<?php
/** Déclaration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.9 2004/03/12 16:53:21 mbertier Exp $
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

/** Classe "métier". Effectue tous les traitements et opérations.
 *
 * @package    Blogmarks
 * @uses       Element_Factory
 * @todo       Validation des paramètres dans les méthodes publiques (et les autres mêmes ;)
 * @todo       Fichier de conf dédié
 * @todo       Erreur 500 en cas de tentative de création d'un élément déja existant.
 * @todo       Mise en place de l'authentification dans toutes les méthodes
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilisés couramment par Marker.
     * @var      array 
     * @access   private  */
    var $_slots = array();
    

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_Marker () {
        // Initialisation des slots
        $this->_initSlots();
    }

# ------- MARKS
    
    /** Création d'un mark. 
     * @param      array     $props      Un tableau associatif de paramètres décrivant le mark.
     *                                   Les clés du tableau correpondent aux noms des champs de la base de données.
     * @return     mixed     L'URI du mark créé
     */
    function createMark( $props ) {

        // permissions: Pour pouvoir créer un Mark, il faut êter authentifié
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( $user && ! $user->isAuthenticated() ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Instanciation et initialisation d'un Link
        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );

        // Si le lien n'est pas déjà enregistré, on le fait.
        if ( $link->get( 'href', $props['href'] ) == 0 ) {
            $link = $this->createLink( $props['href'], true );
            if ( Blogmarks::isError($link) ) { return $link; }
        }


        // Création du Mark.
        $mark =& $this->_slots['ef']->makeElement( 'Bm_Marks' );
        
        // Le possesseur du Mark est l'utilisateur connecté.
        $u =& $this->_slots['auth']->getConnectedUser();
        $mark->bm_Users_id = $u->id;

        $mark->bm_Links_id = $link->id;

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
            $res = $mark->insert();
            if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        } 

        else { return Blogmarks::raiseError( "Le Mark existe déjà.", 500 ); }


        // Récupération de l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }
    

    /** Mise à jour d'un mark.
     * @param      int      $id       ID identifiant le mark
     * @param      array    $props    Un tableau de propriétés à mettre à jour.
     * @return    string    L'uri du mark mis à jour.
     */
    function updateMark( $id, $props ) {
        $mark =& $this->_slots['ef']->makeElement( 'Bm_Marks' );

        // Si le mark à mettre à jour n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le Mark [$id]  n'existe pas.", 404 );
        }

        // permissions: Pour mettre à jour un Mark, il faut le posséder
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) &&  ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );

        
        // Si l'URL associée au Mark doit être modifiée
        if ( isset($props['href']) ) {

            // Récupération du Link associé
            $link =& $mark->getLink( 'bm_Links_id', 'Bm_Links', 'id' );

            // L'URL doit être modifiée
            if ( $link->href !== $props['href'] ) {
                
                // Si le Link existe déja, on se contente de modifier l'association
                if ( $link->get('href', $props['href']) > 0 ) {
                    $mark->bm_Links_id = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
                } 

                // Aucun Link correspondant n'existe, on en crée un
                else {
                    $link =& $this->createLink( $props['href'], true );
                    $mark->bm_Links_id = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
                }
            }

        } // Fin if URL associée

        // Mise à jour des propriétés
        $mark->title    = $props['title'];
        $mark->summary  = $props['summary'];
        $mark->lang     = $props['lang'];
        $mark->via      = $props['via'];
        
        // Dates
        $date = date("Ymd Hms");
        $mark->issued   = isset( $props['issued'] ) ? $props['issued'] : $mark->issued;
        $mark->modified = $date;
        
        // Insertion dans la base de données
        $res = $mark->update();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        // On renvoie l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }

    /** Suppression d'un mark.
     * @param     int      $id       URI identifiant le mark
     * @return   mixed    true ou Blogmarks_Exception en cas d'erreur.
     */
    function deleteMark( $id ) {
        $mark =& $this->_slots['ef']->makeElement( 'Bm_Marks' );
        
        // Si le mark à effacer n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le mark [$id] n'existe pas.", 404 );
        }

        // permissions: Pour effacer un Mark, il faut le posséder
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Suppression du Mark
        $res = $mark->delete();
        if ( Blogmarks::isError($res) ) { return $res; }

        return true;
    }

    /** Génération de l'URI d'un Mark
     * @param     object     Element_Bm_Marks     Une référence à un Mark.
     * @return   string     L'URI du Mark.
     */
    function getMarkUri( &$mark ) {
        $pattern = 'http://www.blogmarks.net/users/%s/?mark_id=%u';
        $uri = sprintf( $pattern, "MOCK!", $mark->id );

        return $uri;
    }


# ------- LINKS

    /** Création d'un Link.
     * @param     string     href          URL désignant la ressource.
     * @param     bool       autofetch     (optionnel) Si vrai, appel automatique de fetchUrlInfo() (defaut: false)
     * @return   objet Element_Bm_Links   Le Links créé
     */
    function createLink( $href, $autofetch = false ) {
        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );

        // permissions: Pour créer un Link, il faut être authentifié
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );
        
        $link->href = $href;
        
        // Si le Link existe déja on se contente de renvoyer son URI
        if ( $link->find(true) ) { return  $link; }

        // Sinon, création du Link
        else { $link->insert(); }


        // Récupération des informations de la page (si autofetch)
        if ( $autofetch === true ) { 
            $link->fetchUrlInfo(); 
            $res = $link->update();
            if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
        }

        return $link;
        
    }

    /** Mise à jour d'un Link.
     * @param     int      $id              L'identifiant du Link.
     * @param     array    $props           Un tableau associatif de la forme : <pre>array( 'label_champs_db' => 'valeur' )</pre>
     * @param     bool     $autofetch       (optionnel) Si vrai, appel automatique de fetchUrlInfo() (au cas ou l'url du link change) (defaut: false)
     * @return    object Element_Bm_Links   Le Link mis à jour
     */
    function updateLink( $id, $props = array(), $autofetch = false ) {
        
        // permissions: Pour mettre à jour un Link, il faut être authentifié
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->isAuthenticated() )  return Blogmarks::raiseError( "Permission denied", 401 );

        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );
        
        // Si le Link n'existe pas -> erreur
        if ( ! $link->get($id) ) { 
            return Blogmarks::raiseError( "Le Link requis [$id] n'existe pas", 404 );
        }

        // Si un Link avec une URL équivalente existe -> erreur
        if ( $link->get('href', $props['href']) ) {
            return Blogmarks::raiseError( "Un autre Link [$link->id] désigne déja cette ressource", 500 );
        }

        // Table rase...
        unset( $link );
        
        // Récupération du Link à mettre à jour
        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );
        $link->get( $id );

        // Mise à jour des propriétés de l'objet
        $old_href = $link->href;
        $link->populateProps( $props );
        
        // Autofetch (si nécessaire et requis)
        if ( $link->href !== $props['href'] && $autofetch ) $link->fetchUrlInfo();

        // Mise à jour de l'enregistrement dans la base de données
        $res = $link->update();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        return $link;
    }


    /** Suppression d'un Link. 
     * @param    int      L'identifiant du Link dans la base de données
     * @return   
     */
    function deleteLink( $id ) {

        // permissions: Pour effacer un Link, il faut être authentifié
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );


        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );

        // Si le Link à effacer n'existe pas -> erreur 404
        if ( ! $link->get($id) ) {
            return Blogmarks::raiseError( "Le Link [$id] n'existe pas.", 404 );
        }

        // Suppression du Link
        $res = $link->delete();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        return true;        
    }


    /** Génération de l'URI d'un Link.
     * @param    object Element_Bm_Links     Une référence au Link dont on veut obtenir l'URI
     * @return   string     L'URI du Link
     */
    function getLinkUri( &$link ) {
        $pattern = 'http://www.blogmarks.net/links/?link_id=%u';
        $uri = sprintf( $pattern, $link->id );

        return $uri;
    }


# ------- TAGS

    /** Création d'un nouveau Tag.
     * @param      array      $props     Un tableau associatif décrivant les propriétés du Tag
     * @return     string     L'uri du tag créé
     */
    function createTag( $props = array() ) {

        $tag =& $this->_slots['ef']->makeElement( 'Bm_Tags' );
        
        // Si le tag existe déja -> erreur 500
        $tag->id = $props['id'];
        if ( $tag->find() ) { return Blogmarks::raiseError( "Le Tag [$tag->id] existe déjà.", 500 ); }

        // Initialisation des propriétés de l'objet
        $tag->populateProps( $props );

        // Insertion dans la base de données
        $res = $tag->insert();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
        
        // On renvoie l'uri du nouveau tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }

 
    /** Création d'un Tag public.
     * @param      array     $props
     * @return
     */
    function createPublicTag( $props = array() ) {
        $props['status'] = 'public';
        return $this->createTag( &$props );
    }

    /** Création d'un Tag privé.
     * @param      array     $props
     * @return
     */
    function createPrivateTag( $props = array() ) {
        $props['status'] = 'private';
        return $this->createTag( &$props );
    }
    
    /** Mise à jour d'un Tag.
     * @param      string    $id     L'identifiant du Tag dans la base de données
     * @param      array     $props  Un tableau associatif décrivant les propriétés à mettre à jour
     * @return     string    L'uri du Tag mis à jour
     */
    function updateTag( $id, $props ) {
        $tag =& $this->_slots['ef']->makeElement( 'Bm_Tags' );

        // Si le Tag n'existe pas -> erreur 404
        $tag->id = $id;
        if ( ! $tag->find() ) { Blogmarks::raiseError( "Le tag [$id] n'existe pas.", 404 ); }

        // Mise à jour des propriétés du Tag
        $tag->populateProps( $props );
        $res = $tag->update();
        if ( Blogmarks::isError($res) ) { return $res; }

        // On retroune l'uri du Tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }


    /** Suppression d'un tag.
     * @param     string    $id    L'identifiant du Tag dans la base de données.
     */
    function deleteTag( $id ) {
        $tag =& $this->_slots['ef']->makeElement( 'Bm_Tags' );

        // Si le Link à effacer n'existe pas -> erreur 404
        if ( ! $tag->get($id) ) {
            return Blogmarks::raiseError( "Le Tag [$id] n'existe pas.", 404 );
        }

        // Suppression du Link
        $tag->delete();

        return true;        
    }

    
    /** Renvoie l'uri du Tag passé en paramètre.
     * @param      object Element_Bm_Tags    Le Tag dont on recherche l'uri
     * @return     string       L'uri du Tag.
     */
    function getTagUri( $tag ) {
        $pattern = 'http://www.blogmarks.net/tags/?tags=%f';
        $uri = sprintf( $pattern, $tag->id );

        return $uri;
    }


    /** Renvoie un tableau comprenant tous les tags existants dans la base 
     * de données similaires au tag passé en paramètre.
     *
     * Dans l'espoir d'éviter une multiplication intempestive des Tags.
     *
     * @param    string    $name     Le nom du Tag
     * @return   array     Un tableau associatif de la forme: <pre>array( 'relevance', 'tag' )</pre>
     * 
     * @see      http://www.php.net/levenshtein
     * @see      http://www.php.net/metaphone
     *
     */
    function getSimilarTags( $name ) {}


# ------- AUTH

    /** Authentification d'un utilisateur.
     * Les paramètres sont transmis à Blogmarks_Auth::authenticate()
     * @param      string      $login        Le login de l'utilisateur.
     * @param      string      $cli_digest   Le digest du client, qui sera comparé au digest server.
     * @param      string      $nonce        Chaîne aléatoire utilisée par le client pour créer le digest.
     * @param      string      $timestamp    Utilisé par le client pour générer le digest.
     *
     * @return     mixed       True en cas de succès ou Blogmarks_Exception en cas d'erreur.
     */
    function authenticate( $login, $cli_digest, $nonce, $timestamp ) {
        $res = $this->_slots['auth']->authenticate( $login, $cli_digest, $nonce, $timestamp );
        return $res;
    }
                       
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #

    /** Initialisation des slots. 
     * @access    private
     */
    function _initSlots() {

        // Array( slot_name, array(class_name, class_file) );
        $slots_info = array( 'ef'   => array( 'Element_Factory', 'blogmarks/Element/Factory.php' ),
                             'auth' => array( 'Blogmarks_Auth',  'blogmarks/Auth.php' ) );

        foreach ( $slots_info as $slot_name => $class_info ) {
            // Inclusion de la déclaration de la classe
            require_once $class_info[1];

            // Instanciation
            $obj =& new $class_info[0];
            
            $this->_slots[$slot_name] = $obj;
            
        }

        return true;
        
    }
}
?>