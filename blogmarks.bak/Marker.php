<?php
/** Déclaration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.18 2004/03/30 12:34:58 mbertier Exp $
 * @todo       Comment fonctionne les permissions sur les Links ?
 */

# -- Includes
require_once 'PEAR.php';
require_once 'Blogmarks/Blogmarks.php';
require_once 'Blogmarks/Element/Factory.php';

/** Classe "métier". Effectue tous les traitements et opérations.
 *
 * @package    Blogmarks
 * @uses       Element_Factory
 * @uses       Blogmarks_Auth
 *
 * @todo       Validation des paramètres dans les méthodes publiques (et les autres mêmes ;)
 * @todo       Fichier de conf dédié
 * @todo       _errorStack et méthodes associées
 */
class BlogMarks_Marker {

    /** Tableau d'objets utilisés couramment par Marker.
     * @var      array 
     * @access   private  */
    var $_slots = array();
    

# ------------------------ #
# -- METHODES PUBLIQUES -- #
# ------------------------ #

    /** Constructeur. */
    function BlogMarks_Marker () {
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
    
    /** Création d'un mark.
     * Dans $props, en plus des propriétés correspondants à la DB, on peux renseigner deux clés supplémentaires :
     *    - $props['tags']      -> un tableau d'id de Tags à associer au Mark
     *    - $props['public']    -> true, false, ou une date future (format mysql datetime) à laquelle le Mark deviendra public.
     *
     * @param      array     $props      Un tableau associatif de paramètres décrivant le mark.
     *                                   Les clés du tableau correpondent aux noms des champs de la base de données.
     * @return     mixed     L'URI du mark créé
     * @perms      Pour pouvoir créer un Mark, il faut être authentifié.
     */
    function createMark( $props ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated() ) return Blogmarks::raiseError( "Permission denied", 401 );


        // Instanciation et initialisation d'un Link
        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Si le lien n'est pas déjà enregistré, on le fait.
        if ( $link->get( 'href', $props['href'] ) == 0 ) {
            $link = $this->createLink( $props['href'], true );
            if ( Blogmarks::isError($link) ) { return $link; }
        }


        // Création du Mark.
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
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
            $date = date("Ymd HIs");
            $mark->created  = $date;
            $mark->modified = $date;

            // Public / privé
            if ( $props['public'] === true  ) $pub = $date;
            if ( $props['public'] === false ) $pub = 0;
            else $pub = $props['public'];
            $mark->issued   = $pub;

            // Insertion dans la base de données
            $res = $mark->insert();
            if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        } 
        
        // Si le Mark existe déja -> erreur 500
        else { return Blogmarks::raiseError( "Le Mark existe déjà.", 500 ); }

        // Gestion des associations Mark / Tags
        $res = $this->associateTagsToMark( $props['tags'], $mark );
        if ( Blogmarks::isError($res) ) return $res;

        // Récupération de l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }
    

    /** Mise à jour d'un Mark.
     * @param      int      $id       ID identifiant le mark
     * @param      array    $props    Un tableau de propriétés à mettre à jour.
     *                                La valeur de l'index 'mergetags' sera passée à Blogmarks_Marker::associateTagsToMark
     * @return    string    L'uri du mark mis à jour.
     * @perms     Pour mettre à jour un Mark, il faut le posséder
     */
    function updateMark( $id, $props ) {
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );

        // Si le mark à mettre à jour n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) {
            return Blogmarks::raiseError( "Le Mark [$id]  n'existe pas.", 404 );
        }

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns( $mark ) ) return Blogmarks::raiseError( "Permission denied", 401 );

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
                    if ( Blogmarks::isError($res) ) return $res;
                }
            }

        } // Fin if URL associée

        // Mise à jour des propriétés
        $mark->title    = $props['title'];
        $mark->summary  = $props['summary'];
        $mark->lang     = $props['lang'];
        
        // Dates
        $date = date("Ymd Hms");
        $mark->modified = $date;

        // Public / privé
        if ( $props['public'] === true  ) $pub = $date;
        if ( $props['public'] === false ) $pub = 0;
        else $pub = $props['public'];
        $mark->issued   = $pub;

        // Tags
        $this->associateTagsToMark( $props['tags'], $mark, $props['mergetags'] );

        // Insertion dans la base de données
        $res = $mark->update();
        if ( Blogmarks::isError($res) ) { return Blogmarks::raiseError( $res->getMessage(), $res->getCode() ); }

        // On renvoie l'URI du Mark
        $uri = $this->getMarkUri( $mark );

        return $uri;
    }


    /** Suppression d'un mark.
     * @param      int      $id       URI identifiant le mark
     * @return     mixed    true ou Blogmarks_Exception en cas d'erreur.
     * @perms      Pour effacer un Mark, il faut le posséder
     */
    function deleteMark( $id ) {
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
        // Si le mark à effacer n'existe pas -> erreur 404
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


    /** Génération de l'URI d'un Mark
     * @param     object     Element_Bm_Marks     Une référence à un Mark.
     * @return   string     L'URI du Mark.
     */
    function getMarkUri( &$mark ) {
        $pattern = 'http://www.blogmarks.net/users/%s/?mark_id=%u';
        
        // Récupération du login du possesseur du Mark
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        $user->get( $mark->bm_Users_id );

        // Génération de l'uri
        $uri = sprintf( $pattern, $user->login, $mark->id );

        return $uri;
    }


    /** Gère les associations de Tags à un Mark.
     * Règles de gestion :
     *  - Tag déja associé             -> aucune action
     *  - Tag existant non-associé     -> association du Tag au Mark
     *  - Tag non-existant             -> création d'un Tag privé correpondant et association au Mark
     * 
     * @param      array                        $tags      Tableau d'identifiants de Tags
     * @param      object Element_Bm_Marks      $mark
     * @param      bool                         $merge     Si true, on merge les Tags passés en paramètres avec les Tags associés déjà existants
     * @return
     * @perms      il faut posséder le Mark pour éditer les associations de Tags
     *
     * @todo      Comportement à définir pour la gestion des erreurs : arret immédiat en cas d'erreur ?
     */
    function associateTagsToMark( $tags, $mark, $merge = false ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($mark) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Désassociations
        if ( ! $merge ) {
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
                // Utilisateur connecté
                $user =& $this->_slots['auth']->getConnectedUser();

                // Création d'un tag privé correspondant
                $res =& $this->createPrivateTag( array('id'          => $tag_name,
                                                       'bm_Users_id' => $user->id) );
                if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res;

                // Association au Mark
                //                $res =& $this->associateTagToMark( $tag, $mark );
                $res =& $mark->addTagAssoc( $tag->id );
                if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res; // _errorStack ne va pas durer.....
                
            }

            // Tag déjà associé
            if ( $tag->isAssociatedToMark($mark->id) ) { continue; }

            // Tag existant non-associé
            elseif ( ! $tag->isAssociatedToMark($mark->id) ) {
                //                $res =& $this->associateTagToMark( $tag, $mark );
                $res =& $mark->addTagAssoc( $tag->id );
                if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res;
            }
            
        } # -- fin foreach
    }


    /** Associe un Tag à un Mark.
     * @param      object Element_Bm_Tags      $tag
     * @param      object Element_Bm_Marks     $mark
     * @return     mixed      true ou Blogmarks_Exception en cas d'erreur.
     * @perms      Il faut posséder le Mark pour éditer les associations de Tags
     */
    function associateTagToMark( &$tag, &$mark ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($mark) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // On vérifie si le Tag n'est pas déja associé au Mark
        if ( $tag->isAssociatedToMark($mark->id) ) return Blogmarks::raiseError( "Le Tag [$tag->id] est déjà associé au Mark [$mark->id].", 500 );
        
        // Association
        $res =& $mark->addTagAssoc( $tag->id );
        if ( Blogmarks::isError($res) ) return $res;
        else return true;
    }


# ------- LINKS

    /** Création d'un Link.
     * @param     string     href          URL désignant la ressource.
     * @param     bool       autofetch     (optionnel) Si vrai, appel automatique de fetchUrlInfo() (defaut: false)
     * @return    objet Element_Bm_Links    Le Links créé
     * @perms     Pour créer un Link, il faut être authentifié
     */
    function createLink( $href, $autofetch = false ) {
        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );
        
        $link->href = $href;
        
        // Si le Link existe déja on se contente de renvoyer son URI
        if ( $link->find(true) ) { return  $link; }

        // Sinon, création du Link
        else { $link->insert(); }


        // Récupération des informations de la page (si autofetch)
        if ( $autofetch === true ) { 
            $link->fetchUrlInfo(); 
            $res = $link->update();
            if ( Blogmarks::isError($res) ) return $res;
        }

        return $link;
        
    }


    /** Mise à jour d'un Link.
     * @param     int      $id              L'identifiant du Link.
     * @param     array    $props           Un tableau associatif de la forme : <pre>array( 'label_champs_db' => 'valeur' )</pre>
     * @param     bool     $autofetch       (optionnel) Si vrai, appel automatique de Element_Bm_Links::fetchUrlInfo()
     *                                      (au cas ou l'url du link change) (defaut: false)
     * @return    object Element_Bm_Links   Le Link mis à jour
     * @perms     Pour mettre à jour un Link, il faut être authentifié
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

        // Si un Link avec une URL équivalente existe -> erreur
        if ( $link->get('href', $props['href']) ) {
            return Blogmarks::raiseError( "Un autre Link [$link->id] désigne déja cette ressource", 500 );
        }

        // Table rase...
        unset( $link );
        
        // Récupération du Link à mettre à jour
        $link =& Element_Factory::makeElement( 'Bm_Links' );
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
     * @return   mixed    true ou Blogmarks_Eception en cas d'erreur
     * @perms    Pour effacer un Link, il faut être authentifié
     */
    function deleteLink( $id ) {

        // permissions: 
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );


        $link =& Element_Factory::makeElement( 'Bm_Links' );

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
     * @param    object Element_Bm_Links     $link      Une référence au Link dont on veut obtenir l'URI
     * @return   string                      L'URI du Link
     */
    function getLinkUri( &$link ) {
        $pattern = 'http://www.blogmarks.net/links/?link_id=%u';
        $uri = sprintf( $pattern, $link->id );

        return $uri;
    }


# ------- TAGS

    /** Création d'un nouveau Tag.
     * @param      array      $props     Un tableau associatif décrivant les propriétés du Tag
     * @return     mixed      L'uri du tag créé ou Blogmarks_Exception en cas d'erreur
     * @perms      Pour créer un Tag, il faut être authentifié
     */
    function createTag( $props = array() ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );

        $tag =& Element_Factory::makeElement( 'Bm_Tags' );
        
        // Si le tag existe déja -> erreur 500
        $tag->id = $props['id'];
        if ( $tag->find() ) return Blogmarks::raiseError( "Le Tag [$tag->id] existe déjà.", 500 );

        // Initialisation des propriétés de l'objet
        $tag->populateProps( $props );

        // Insertion dans la base de données
        $res = $tag->insert();
        if ( Blogmarks::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );
        
        // On renvoie l'uri du nouveau tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }

 
    /** Création d'un Tag public.
     * @param      array     $props
     * @return     mixed      L'uri du tag créé ou Blogmarks_Exception en cas d'erreur
     */
    function createPublicTag( $props = array() ) {
        $props['status'] = 'public';
        return $this->createTag( &$props );
    }


    /** Création d'un Tag privé.
     * @param      array     $props
     * @return     mixed      L'uri du tag créé ou Blogmarks_Exception en cas d'erreur
     */
    function createPrivateTag( $props = array() ) {
        $props['status'] = 'private';
        return $this->createTag( &$props );
    }

    
    /** Mise à jour d'un Tag.
     * @param      string    $id     L'identifiant du Tag dans la base de données
     * @param      array     $props  Un tableau associatif décrivant les propriétés à mettre à jour
     * @return     string    L'uri du Tag mis à jour
     * @perms      Pour mettre à jour un Tag, il faut le posséder
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

        // Mise à jour des propriétés du Tag
        $tag->populateProps( $props );
        $res = $tag->update();
        if ( Blogmarks::isError($res) ) { return $res; }

        // On retourne l'uri du Tag
        $uri = $this->getTagUri( $tag );
        return $uri;
    }


    /** Suppression d'un tag.
     * @param     string    $id    L'identifiant du Tag dans la base de données.
     * @perms     Pour effacer un Tag, il faut le posséder
     */
    function deleteTag( $id ) {
        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

        // Si le Link à effacer n'existe pas -> erreur 404
        if ( ! $tag->get($id) ) return Blogmarks::raiseError( "Le Tag [$id] n'existe pas.", 404 );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($tag) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Suppression du Tag
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


# ------- MARKSLISTS

    /** Renvoie la liste des Marks d'un utilisateur.
     * Si l'utilisateur requeteur n'est pas l'utilisateur possesseur, 
     * seule la liste de ses Marks publics de l'utilisateur possesseur est renvoyée.
     *
     * @param      string      $login_user      
     * @param      array       $include_tags    Ids de Tags, seuls les Marks correspondants aux Tags listés ici seront sélectionnés
     * @param      array       $exclude_tags    Ids de Tags, les Marks correspondants à ces Tags ne seront pas sélectionnés
     *
     */
    function getMarksListOfUser( $login_user, $include_tags = null, $exclude_tags = null ) {

        // permissions
        $cur_user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($cur_user) ) return $cur_user;
        $include_priv = ( $cur_user->login === $login_user ? true : false );

        // On vérifie que l'utilisateur existe
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        if ( ! $user->get( 'login', $login_user ) ) return Blogmarks::raiseError( "L'utilisateur [$login_user] n'existe pas", 404 );

        // Récupération de la liste des Marks
        $res = $user->getMarksList( $include_tags, $exclude_tags, $include_priv );

        return $res;
    }


# ------- AUTH

    /** Authentification d'un utilisateur.
     * Les paramètres sont transmis à Blogmarks_Auth::authenticate()
     * @param      string      $login        Le login de l'utilisateur.
     * @param      string      $cli_digest   Le digest du client, qui sera comparé au digest server.
     * @param      string      $nonce        Chaîne aléatoire utilisée par le client pour créer le digest.
     * @param      string      $timestamp    Utilisé par le client pour générer le digest.
     * @param      bool        $make_session Créer une session (défaut: false)
     *
     * @return     mixed       True en cas de succès ou Blogmarks_Exception en cas d'erreur.
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