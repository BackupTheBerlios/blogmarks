<?php
/** D�claration de la classe BlogMarks_Marker
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

/** Classe "m�tier". Effectue tous les traitements et op�rations.
 *
 * @package    Blogmarks
 * @uses       Element_Factory
 * @todo       Validation des param�tres dans les m�thodes publiques (et les autres m�mes ;)
 * @todo       Fichier de conf d�di�
 * @todo       Erreur 500 en cas de tentative de cr�ation d'un �l�ment d�ja existant.
 * @todo       Mise en place de l'authentification dans toutes les m�thodes
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilis�s couramment par Marker.
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
    
    /** Cr�ation d'un mark. 
     * @param      array     $props      Un tableau associatif de param�tres d�crivant le mark.
     *                                   Les cl�s du tableau correpondent aux noms des champs de la base de donn�es.
     * @return     mixed     L'URI du mark cr��
     */
    function createMark( $props ) {

        // permissions: Pour pouvoir cr�er un Mark, il faut �ter authentifi�
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( $user && ! $user->isAuthenticated() ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Instanciation et initialisation d'un Link
        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );

        // Si le lien n'est pas d�j� enregistr�, on le fait.
        if ( $link->get( 'href', $props['href'] ) == 0 ) {
            $link = $this->createLink( $props['href'], true );
            if ( Blogmarks::isError($link) ) { return $link; }
        }


        // Cr�ation du Mark.
        $mark =& $this->_slots['ef']->makeElement( 'Bm_Marks' );
        
        // Le possesseur du Mark est l'utilisateur connect�.
        $u =& $this->_slots['auth']->getConnectedUser();
        $mark->bm_Users_id = $u->id;

        $mark->bm_Links_id = $link->id;

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
            $res = $mark->insert();
            if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        } 

        else { return Blogmarks::raiseError( "Le Mark existe d�j�.", 500 ); }


        // R�cup�ration de l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }
    

    /** Mise � jour d'un mark.
     * @param      int      $id       ID identifiant le mark
     * @param      array    $props    Un tableau de propri�t�s � mettre � jour.
     * @return    string    L'uri du mark mis � jour.
     */
    function updateMark( $id, $props ) {
        $mark =& $this->_slots['ef']->makeElement( 'Bm_Marks' );

        // Si le mark � mettre � jour n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le Mark [$id]  n'existe pas.", 404 );
        }

        // permissions: Pour mettre � jour un Mark, il faut le poss�der
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) &&  ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );

        
        // Si l'URL associ�e au Mark doit �tre modifi�e
        if ( isset($props['href']) ) {

            // R�cup�ration du Link associ�
            $link =& $mark->getLink( 'bm_Links_id', 'Bm_Links', 'id' );

            // L'URL doit �tre modifi�e
            if ( $link->href !== $props['href'] ) {
                
                // Si le Link existe d�ja, on se contente de modifier l'association
                if ( $link->get('href', $props['href']) > 0 ) {
                    $mark->bm_Links_id = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
                } 

                // Aucun Link correspondant n'existe, on en cr�e un
                else {
                    $link =& $this->createLink( $props['href'], true );
                    $mark->bm_Links_id = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
                }
            }

        } // Fin if URL associ�e

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
        
        // Si le mark � effacer n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le mark [$id] n'existe pas.", 404 );
        }

        // permissions: Pour effacer un Mark, il faut le poss�der
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Suppression du Mark
        $res = $mark->delete();
        if ( Blogmarks::isError($res) ) { return $res; }

        return true;
    }

    /** G�n�ration de l'URI d'un Mark
     * @param     object     Element_Bm_Marks     Une r�f�rence � un Mark.
     * @return   string     L'URI du Mark.
     */
    function getMarkUri( &$mark ) {
        $pattern = 'http://www.blogmarks.net/users/%s/?mark_id=%u';
        $uri = sprintf( $pattern, "MOCK!", $mark->id );

        return $uri;
    }


# ------- LINKS

    /** Cr�ation d'un Link.
     * @param     string     href          URL d�signant la ressource.
     * @param     bool       autofetch     (optionnel) Si vrai, appel automatique de fetchUrlInfo() (defaut: false)
     * @return   objet Element_Bm_Links   Le Links cr��
     */
    function createLink( $href, $autofetch = false ) {
        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );

        // permissions: Pour cr�er un Link, il faut �tre authentifi�
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );
        
        $link->href = $href;
        
        // Si le Link existe d�ja on se contente de renvoyer son URI
        if ( $link->find(true) ) { return  $link; }

        // Sinon, cr�ation du Link
        else { $link->insert(); }


        // R�cup�ration des informations de la page (si autofetch)
        if ( $autofetch === true ) { 
            $link->fetchUrlInfo(); 
            $res = $link->update();
            if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
        }

        return $link;
        
    }

    /** Mise � jour d'un Link.
     * @param     int      $id              L'identifiant du Link.
     * @param     array    $props           Un tableau associatif de la forme : <pre>array( 'label_champs_db' => 'valeur' )</pre>
     * @param     bool     $autofetch       (optionnel) Si vrai, appel automatique de fetchUrlInfo() (au cas ou l'url du link change) (defaut: false)
     * @return    object Element_Bm_Links   Le Link mis � jour
     */
    function updateLink( $id, $props = array(), $autofetch = false ) {
        
        // permissions: Pour mettre � jour un Link, il faut �tre authentifi�
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->isAuthenticated() )  return Blogmarks::raiseError( "Permission denied", 401 );

        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );
        
        // Si le Link n'existe pas -> erreur
        if ( ! $link->get($id) ) { 
            return Blogmarks::raiseError( "Le Link requis [$id] n'existe pas", 404 );
        }

        // Si un Link avec une URL �quivalente existe -> erreur
        if ( $link->get('href', $props['href']) ) {
            return Blogmarks::raiseError( "Un autre Link [$link->id] d�signe d�ja cette ressource", 500 );
        }

        // Table rase...
        unset( $link );
        
        // R�cup�ration du Link � mettre � jour
        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );
        $link->get( $id );

        // Mise � jour des propri�t�s de l'objet
        $old_href = $link->href;
        $link->populateProps( $props );
        
        // Autofetch (si n�cessaire et requis)
        if ( $link->href !== $props['href'] && $autofetch ) $link->fetchUrlInfo();

        // Mise � jour de l'enregistrement dans la base de donn�es
        $res = $link->update();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        return $link;
    }


    /** Suppression d'un Link. 
     * @param    int      L'identifiant du Link dans la base de donn�es
     * @return   
     */
    function deleteLink( $id ) {

        // permissions: Pour effacer un Link, il faut �tre authentifi�
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( isset($user) && ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );


        $link =& $this->_slots['ef']->makeElement( 'Bm_Links' );

        // Si le Link � effacer n'existe pas -> erreur 404
        if ( ! $link->get($id) ) {
            return Blogmarks::raiseError( "Le Link [$id] n'existe pas.", 404 );
        }

        // Suppression du Link
        $res = $link->delete();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        return true;        
    }


    /** G�n�ration de l'URI d'un Link.
     * @param    object Element_Bm_Links     Une r�f�rence au Link dont on veut obtenir l'URI
     * @return   string     L'URI du Link
     */
    function getLinkUri( &$link ) {
        $pattern = 'http://www.blogmarks.net/links/?link_id=%u';
        $uri = sprintf( $pattern, $link->id );

        return $uri;
    }


# ------- TAGS

    /** Cr�ation d'un nouveau Tag.
     * @param      array      $props     Un tableau associatif d�crivant les propri�t�s du Tag
     * @return     string     L'uri du tag cr��
     */
    function createTag( $props = array() ) {

        $tag =& $this->_slots['ef']->makeElement( 'Bm_Tags' );
        
        // Si le tag existe d�ja -> erreur 500
        $tag->id = $props['id'];
        if ( $tag->find() ) { return Blogmarks::raiseError( "Le Tag [$tag->id] existe d�j�.", 500 ); }

        // Initialisation des propri�t�s de l'objet
        $tag->populateProps( $props );

        // Insertion dans la base de donn�es
        $res = $tag->insert();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }
        
        // On renvoie l'uri du nouveau tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }

 
    /** Cr�ation d'un Tag public.
     * @param      array     $props
     * @return
     */
    function createPublicTag( $props = array() ) {
        $props['status'] = 'public';
        return $this->createTag( &$props );
    }

    /** Cr�ation d'un Tag priv�.
     * @param      array     $props
     * @return
     */
    function createPrivateTag( $props = array() ) {
        $props['status'] = 'private';
        return $this->createTag( &$props );
    }
    
    /** Mise � jour d'un Tag.
     * @param      string    $id     L'identifiant du Tag dans la base de donn�es
     * @param      array     $props  Un tableau associatif d�crivant les propri�t�s � mettre � jour
     * @return     string    L'uri du Tag mis � jour
     */
    function updateTag( $id, $props ) {
        $tag =& $this->_slots['ef']->makeElement( 'Bm_Tags' );

        // Si le Tag n'existe pas -> erreur 404
        $tag->id = $id;
        if ( ! $tag->find() ) { Blogmarks::raiseError( "Le tag [$id] n'existe pas.", 404 ); }

        // Mise � jour des propri�t�s du Tag
        $tag->populateProps( $props );
        $res = $tag->update();
        if ( Blogmarks::isError($res) ) { return $res; }

        // On retroune l'uri du Tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }


    /** Suppression d'un tag.
     * @param     string    $id    L'identifiant du Tag dans la base de donn�es.
     */
    function deleteTag( $id ) {
        $tag =& $this->_slots['ef']->makeElement( 'Bm_Tags' );

        // Si le Link � effacer n'existe pas -> erreur 404
        if ( ! $tag->get($id) ) {
            return Blogmarks::raiseError( "Le Tag [$id] n'existe pas.", 404 );
        }

        // Suppression du Link
        $tag->delete();

        return true;        
    }

    
    /** Renvoie l'uri du Tag pass� en param�tre.
     * @param      object Element_Bm_Tags    Le Tag dont on recherche l'uri
     * @return     string       L'uri du Tag.
     */
    function getTagUri( $tag ) {
        $pattern = 'http://www.blogmarks.net/tags/?tags=%f';
        $uri = sprintf( $pattern, $tag->id );

        return $uri;
    }


    /** Renvoie un tableau comprenant tous les tags existants dans la base 
     * de donn�es similaires au tag pass� en param�tre.
     *
     * Dans l'espoir d'�viter une multiplication intempestive des Tags.
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
     * Les param�tres sont transmis � Blogmarks_Auth::authenticate()
     * @param      string      $login        Le login de l'utilisateur.
     * @param      string      $cli_digest   Le digest du client, qui sera compar� au digest server.
     * @param      string      $nonce        Cha�ne al�atoire utilis�e par le client pour cr�er le digest.
     * @param      string      $timestamp    Utilis� par le client pour g�n�rer le digest.
     *
     * @return     mixed       True en cas de succ�s ou Blogmarks_Exception en cas d'erreur.
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
            // Inclusion de la d�claration de la classe
            require_once $class_info[1];

            // Instanciation
            $obj =& new $class_info[0];
            
            $this->_slots[$slot_name] = $obj;
            
        }

        return true;
        
    }
}
?>