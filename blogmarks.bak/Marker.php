<?php
/** D�claration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.19 2004/03/31 12:21:24 mbertier Exp $
 * @todo       Comment fonctionne les permissions sur les Links ?
 */

# -- Includes
require_once 'PEAR.php';
require_once 'Blogmarks/Blogmarks.php';
require_once 'Blogmarks/Element/Factory.php';

/** Classe "m�tier". Effectue tous les traitements et op�rations.
 *
 * @package    Blogmarks
 * @uses       Element_Factory
 * @uses       Blogmarks_Auth
 *
 * @todo       Validation des param�tres dans les m�thodes publiques (et les autres m�mes ;)
 * @todo       Fichier de conf d�di�
 * @todo       _errorStack et m�thodes associ�es
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilis�s couramment par Marker.
     * @var      array 
     * @access   private  */
    var $_slots = array();
    
    var $_static;

# ------------------------ #
# -- METHODES PUBLIQUES -- #
# ------------------------ #


    /** Retourne une r�f�rence � Marker, qui n'est cr�� que s'il n'existe pas encore.
     * Doit �tre appel� de cette fa�on : <code>$marker =& new Blogmarks_Marker::singleton();</code>
     * @return      object Blogmarks_Marker
     */
    function &singleton() {
        static $instance;

        if (!isset($instance)) {
            $instance = new Blogmarks_Marker;
        }

        return $instance;
    }

    /** Constructeur. 
     * @warning      Ne doit jamais �tre appel� directement, � part par Blogmarks_Marker::singleton() 
     */
    function Blogmarks_Marker() {
        // Initialisation des slots
        $this->_initSlots();

        // Configuration des datatobjects
        $config = parse_ini_file( dirname(__FILE__) . '/config.ini', TRUE);

        foreach( $config as $class => $values ) {
            $options =& PEAR::getStaticProperty( $class, 'options' );
            $options = $values;
        }
    }



# ------- MARKS
    
    /** Cr�ation d'un mark.
     * Dans $props, en plus des propri�t�s correspondants � la DB, on peux renseigner deux cl�s suppl�mentaires :
     *    - $props['tags']      -> un tableau d'id de Tags � associer au Mark
     *    - $props['public']    -> true, false, ou une date future (format mysql datetime) � laquelle le Mark deviendra public.
     *
     * @param      array     $props      Un tableau associatif de param�tres d�crivant le mark.
     *                                   Les cl�s du tableau correpondent aux noms des champs de la base de donn�es.
     * @return     mixed     L'URI du mark cr��
     * @perms      Pour pouvoir cr�er un Mark, il faut �tre authentifi�.
     */
    function createMark( $props ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated() ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Instanciation et initialisation d'un Link
        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Si le lien n'est pas d�j� enregistr�, on le fait.
        if ( $link->get( 'href', $props['href'] ) == 0 ) {
            $link = $this->createLink( $props['href'], true );
            if ( Blogmarks::isError($link) ) { return $link; }
        }


        // Cr�ation du Mark.
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
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
            $date = date("Ymd HIs");
            $mark->created  = $date;
            $mark->modified = $date;

            // Public / priv�
            if ( $props['public'] === true  ) $pub = $date;
            if ( $props['public'] === false ) $pub = 0;
            else $pub = $props['public'];
            $mark->issued   = $pub;

            // Insertion dans la base de donn�es
            $res = $mark->insert();
            if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        } 
        
        // Si le Mark existe d�ja -> erreur 500
        else { return Blogmarks::raiseError( "Le Mark existe d�j�.", 500 ); }

        // Gestion des associations Mark / Tags
        if ( is_array($props['tags']) ) {
            $res = $this->associateTagsToMark( $props['tags'], $mark );
            if ( Blogmarks::isError($res) ) return $res;
        }

        // R�cup�ration de l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }
    

    /** Mise � jour d'un Mark.
     * @param      int      $id       ID identifiant le mark
     * @param      array    $props    Un tableau de propri�t�s � mettre � jour.
     *                                La valeur de l'index 'mergetags' sera pass�e � Blogmarks_Marker::associateTagsToMark
     * @return    string    L'uri du mark mis � jour.
     * @perms     Pour mettre � jour un Mark, il faut le poss�der
     */
    function updateMark( $id, $props ) {
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );

        // Si le mark � mettre � jour n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le Mark [$id]  n'existe pas.", 404 );
        }

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );

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
                    if ( Blogmarks::isError($res) ) return $res;
                }
            }

        } // Fin if URL associ�e

        // Mise � jour des propri�t�s
        $mark->title    = $props['title'];
        $mark->summary  = $props['summary'];
        $mark->lang     = $props['lang'];
        
        // Dates
        $date = date("Ymd Hms");
        $mark->modified = $date;

        // Public / priv�
        if ( $props['public'] === true  ) $pub = $date;
        if ( $props['public'] === false ) $pub = 0;
        else $pub = ( isset($props['public']) ? $props['public'] : 0 );
        $mark->issued   = $pub;

        // Tags
        if ( is_array($props['tags']) ) {
            $res =& $this->associateTagsToMark( $props['tags'], $mark, $props['mergetags'] );
            if ( Blogmarks::isError($res) ) return $res;
        }

        // Insertion dans la base de donn�es
        $res = $mark->update();
        if ( Blogmarks::isError($res) ) return $res;

        // On renvoie l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }


    /** Suppression d'un mark.
     * @param      int      $id       URI identifiant le mark
     * @return     mixed    true ou Blogmarks_Exception en cas d'erreur.
     * @perms      Pour effacer un Mark, il faut le poss�der
     */
    function deleteMark( $id ) {
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
        // Si le mark � effacer n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) return Blogmarks::raiseError( "Le mark [$id] n'existe pas.", 404 );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Suppression du Mark
        $res = $mark->delete();
        if ( Blogmarks::isError($res) ) return $res;

        return true;
    }


    /** G�n�ration de l'URI d'un Mark
     * @param     object     Element_Bm_Marks     Une r�f�rence � un Mark.
     * @return   string     L'URI du Mark.
     */
    function getMarkUri( &$mark ) {
        $pattern = 'http://www.blogmarks.net/users/%s/?mark_id=%u';
        
        // R�cup�ration du login du possesseur du Mark
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        $user->get( $mark->bm_Users_id );

        // G�n�ration de l'uri
        $uri = sprintf( $pattern, $user->login, $mark->id );

        return $uri;
    }


    /** G�re les associations de Tags � un Mark.
     * R�gles de gestion :
     *  - Tag d�ja associ�             -> aucune action
     *  - Tag existant non-associ�     -> association du Tag au Mark
     *  - Tag non-existant             -> cr�ation d'un Tag priv� correpondant et association au Mark
     * 
     * @param      array                        $tags      Tableau d'identifiants de Tags
     * @param      object Element_Bm_Marks      $mark
     * @param      bool                         $merge     Si true, on merge les Tags pass�s en param�tres avec les Tags associ�s d�j� existants
     * @return
     * @perms      il faut poss�der le Mark pour �diter les associations de Tags
     *
     * @todo      Comportement � d�finir pour la gestion des erreurs : arret imm�diat en cas d'erreur ?
     */
    function associateTagsToMark( $tags, $mark, $merge = false ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($mark) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // D�sassociations
        if ( ! $merge && is_array($tags) ) {
            $deprec_tags = array_diff( $mark->getTags(), $tags );
            foreach ( $deprec_tags as $tag_id ) {
                $mark->remTagAssoc( $tag_id );
            }
        }

        // Associations
        foreach ( $tags as $tag_name ) {
            $tag =& Element_Factory::makeElement( 'Bm_Tags' );

            // Tag non-existant
            if ( ! $tag->get($tag_name) ) {
                echo "$tag_name<br />";
                // Utilisateur connect�
                $user =& $this->_slots['auth']->getConnectedUser();

                // Cr�ation d'un tag priv� correspondant
                $res =& $this->createPrivateTag( array('id'          => $tag_name,
                                                       'bm_Users_id' => $user->id) );
                if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res;

                // Association au Mark
                //                $res =& $this->associateTagToMark( $tag, $mark );
                $res =& $mark->addTagAssoc( $tag->id );
                if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res; // _errorStack ne va pas durer.....
                
            }

            // Tag d�j� associ�
            if ( $tag->isAssociatedToMark($mark->id) ) { continue; }

            // Tag existant non-associ�
            elseif ( ! $tag->isAssociatedToMark($mark->id) ) {
                //                $res =& $this->associateTagToMark( $tag, $mark );
                $res =& $mark->addTagAssoc( $tag->id );
                if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res;
            }
            
        } # -- fin foreach
    }


    /** Associe un Tag � un Mark.
     * @param      object Element_Bm_Tags      $tag
     * @param      object Element_Bm_Marks     $mark
     * @return     mixed      true ou Blogmarks_Exception en cas d'erreur.
     * @perms      Il faut poss�der le Mark pour �diter les associations de Tags
     */
    function associateTagToMark( &$tag, &$mark ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($mark) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // On v�rifie si le Tag n'est pas d�ja associ� au Mark
        if ( $tag->isAssociatedToMark($mark->id) ) return Blogmarks::raiseError( "Le Tag [$tag->id] est d�j� associ� au Mark [$mark->id].", 500 );
        
        // Association
        $res =& $mark->addTagAssoc( $tag->id );
        if ( Blogmarks::isError($res) ) return $res;
        else return true;
    }


# ------- LINKS

    /** Cr�ation d'un Link.
     * @param     string     href          URL d�signant la ressource.
     * @param     bool       autofetch     (optionnel) Si vrai, appel automatique de fetchUrlInfo() (defaut: false)
     * @return    objet Element_Bm_Links    Le Links cr��
     * @perms     Pour cr�er un Link, il faut �tre authentifi�
     */
    function createLink( $href, $autofetch = false ) {
        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );
        
        $link->href = $href;
        
        // Si le Link existe d�ja on se contente de renvoyer son URI
        if ( $link->find(true) ) { return  $link; }

        // Sinon, cr�ation du Link
        else { $link->insert(); }


        // R�cup�ration des informations de la page (si autofetch)
        if ( $autofetch === true ) { 
            $link->fetchUrlInfo(); 
            $res = $link->update();
            if ( Blogmarks::isError($res) ) return $res;
        }

        return $link;
        
    }


    /** Mise � jour d'un Link.
     * @param     int      $id              L'identifiant du Link.
     * @param     array    $props           Un tableau associatif de la forme : <pre>array( 'label_champs_db' => 'valeur' )</pre>
     * @param     bool     $autofetch       (optionnel) Si vrai, appel automatique de Element_Bm_Links::fetchUrlInfo()
     *                                      (au cas ou l'url du link change) (defaut: false)
     * @return    object Element_Bm_Links   Le Link mis � jour
     * @perms     Pour mettre � jour un Link, il faut �tre authentifi�
     */
    function updateLink( $id, $props = array(), $autofetch = false ) {
        
        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated() )  return Blogmarks::raiseError( "Permission denied", 401 );

        $link =& Element_Factory::makeElement( 'Bm_Links' );
        
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
        $link =& Element_Factory::makeElement( 'Bm_Links' );
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
     * @return   mixed    true ou Blogmarks_Eception en cas d'erreur
     * @perms    Pour effacer un Link, il faut �tre authentifi�
     */
    function deleteLink( $id ) {

        // permissions: 
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );


        $link =& Element_Factory::makeElement( 'Bm_Links' );

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
     * @param    object Element_Bm_Links     $link      Une r�f�rence au Link dont on veut obtenir l'URI
     * @return   string                      L'URI du Link
     */
    function getLinkUri( &$link ) {
        $pattern = 'http://www.blogmarks.net/links/?link_id=%u';
        $uri = sprintf( $pattern, $link->id );

        return $uri;
    }


# ------- TAGS

    /** Cr�ation d'un nouveau Tag.
     * @param      array      $props     Un tableau associatif d�crivant les propri�t�s du Tag
     * @return     mixed      L'uri du tag cr�� ou Blogmarks_Exception en cas d'erreur
     * @perms      Pour cr�er un Tag, il faut �tre authentifi�
     */
    function createTag( $props = array() ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );

        $tag =& Element_Factory::makeElement( 'Bm_Tags' );
        
        // Si le tag existe d�ja -> erreur 500
        $tag->id = $props['id'];
        if ( $tag->find() ) return Blogmarks::raiseError( "Le Tag [$tag->id] existe d�j�.", 500 );

        // Initialisation des propri�t�s de l'objet
        $tag->populateProps( $props );

        // Insertion dans la base de donn�es
        $res = $tag->insert();
        if ( Blogmarks::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );
        
        // On renvoie l'uri du nouveau tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }

 
    /** Cr�ation d'un Tag public.
     * @param      array     $props
     * @return     mixed      L'uri du tag cr�� ou Blogmarks_Exception en cas d'erreur
     */
    function createPublicTag( $props = array() ) {
        $props['status'] = 'public';
        return $this->createTag( &$props );
    }


    /** Cr�ation d'un Tag priv�.
     * @param      array     $props
     * @return     mixed      L'uri du tag cr�� ou Blogmarks_Exception en cas d'erreur
     */
    function createPrivateTag( $props = array() ) {
        $props['status'] = 'private';
        return $this->createTag( &$props );
    }

    
    /** Mise � jour d'un Tag.
     * @param      string    $id     L'identifiant du Tag dans la base de donn�es
     * @param      array     $props  Un tableau associatif d�crivant les propri�t�s � mettre � jour
     * @return     string    L'uri du Tag mis � jour
     * @perms      Pour mettre � jour un Tag, il faut le poss�der
     */
    function updateTag( $id, $props ) {
        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

        // Si le Tag n'existe pas -> erreur 404
        $tag->id = $id;
        if ( ! $tag->find() ) { Blogmarks::raiseError( "Le tag [$id] n'existe pas.", 404 ); }

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($tag) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Mise � jour des propri�t�s du Tag
        $tag->populateProps( $props );
        $res = $tag->update();
        if ( Blogmarks::isError($res) ) { return $res; }

        // On retourne l'uri du Tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }


    /** Suppression d'un tag.
     * @param     string    $id    L'identifiant du Tag dans la base de donn�es.
     * @perms     Pour effacer un Tag, il faut le poss�der
     */
    function deleteTag( $id ) {
        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

        // Si le Link � effacer n'existe pas -> erreur 404
        if ( ! $tag->get($id) ) return Blogmarks::raiseError( "Le Tag [$id] n'existe pas.", 404 );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($tag) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Suppression du Tag
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


# ------- MARKSLISTS

    /** Renvoie la liste des Marks d'un utilisateur.
     * Si l'utilisateur requeteur n'est pas l'utilisateur possesseur, 
     * seule la liste de ses Marks publics de l'utilisateur possesseur est renvoy�e.
     *
     * @param      string      $login_user      
     * @param      array       $include_tags    Ids de Tags, seuls les Marks correspondants aux Tags list�s ici seront s�lectionn�s
     * @param      array       $exclude_tags    Ids de Tags, les Marks correspondants � ces Tags ne seront pas s�lectionn�s
     *
     */
    function getMarksListOfUser( $login_user, $include_tags = null, $exclude_tags = null ) {

        // permissions
        $cur_user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($cur_user) ) return $cur_user;
        $include_priv = ( $cur_user->login === $login_user ? true : false );

        // On v�rifie que l'utilisateur existe
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        if ( ! $user->get( 'login', $login_user ) ) return Blogmarks::raiseError( "L'utilisateur [$login_user] n'existe pas", 404 );

        // R�cup�ration de la liste des Marks
        $res = $user->getMarksList( $include_tags, $exclude_tags, $include_priv );

        return $res;
    }


# ------- AUTH

    /** Authentification d'un utilisateur.
     * Les param�tres sont transmis � Blogmarks_Auth::authenticate()
     * @param      string      $login        Le login de l'utilisateur.
     * @param      string      $cli_digest   Le digest du client, qui sera compar� au digest server.
     * @param      string      $nonce        Cha�ne al�atoire utilis�e par le client pour cr�er le digest.
     * @param      string      $timestamp    Utilis� par le client pour g�n�rer le digest.
     * @param      bool        $make_session Cr�er une session (d�faut: false)
     *
     * @return     mixed       True en cas de succ�s ou Blogmarks_Exception en cas d'erreur.
     */
    function authenticate( $login, $cli_digest, $nonce, $timestamp, $make_session = false ) {
        $res =& $this->_slots['auth']->authenticate( $login, $cli_digest, $nonce, $timestamp, $make_session );
        return $res;
    }

                       
# ----------------------- #
# -- METHODES PRIVEES  -- #
# ----------------------- #

    /** Initialisation des slots. 
     * @access    private
     */
    function _initSlots() {
        
        // Array( slot_name, array(class_name, class_file) );
        $slots_info = array( 'auth' => array( 'Blogmarks_Auth',  'Blogmarks/Auth.php' ) );
        
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