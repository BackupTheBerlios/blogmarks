<?php
/** D�claration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.18 2004/06/01 14:12:34 mbertier Exp $
 * @todo       Comment fonctionne les permissions sur les Links ?
 */

# -- Includes
require_once 'PEAR.php';
require_once 'Blogmarks.php';
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

        if ( ! isset($instance) ) $instance = new Blogmarks_Marker;

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
     *    - $props['public']    -> true, false, ou une date future (timestamp) � laquelle le Mark deviendra public.
     *
     * @param      array     $props      Un tableau associatif de param�tres d�crivant le mark.
     *                                   Les cl�s du tableau correpondent aux noms des champs de la base de donn�es.
     * @return     mixed     L'URI du mark cr��
     * @perms      Pour pouvoir cr�er un Mark, il faut �tre authentifi�.
     * @todo       check d'erreurs sur la cr�ation du Link via
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

        $mark->href = $link->id;

        // Si le Mark n'existe pas, on le cr�e
        if ( ! $mark->find(true) ) {

            // D�finition des propri�t�s
            $mark->title    = isset($props['title'])   ? $props['title']   : null;
            $mark->summary  = isset($props['summary']) ? $props['summary'] : null;
            $mark->lang     = isset($props['lang'])    ? $props['lang']    : null;

            // Cr�ation des Links associ�s
            foreach ( $mark->getLinksFields() as $field ) {
                $link =& Element_Factory::makeElement( 'Bm_Links' );

                // Si le Link n'existe pas, on le cr�e
                if ( ! $link->get('href', $props[$field]) ) {
                    $link =& $this->createLink( $props[$field], true );
                }
                $mark->$field = $link->id;

            } // -- Fin de la cr�ation des Links associ�s

            // Dates
            $date = date("Ymd HIs");
            $mark->created  = $date;
            $mark->modified = $date;

            // Public / priv�
            $props['public'] = isset($props['public']) ? $props['public'] : true;
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
     *                                La valeur de l'index 'mergetags' sera pass�e � Blogmarks_Marker::associateTagsToMark()
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

        // Mise � jour des URLs associ�es
        foreach ( $mark->getLinksFields() as $field ) {

            // Une mise � jour est requise
            if ( isset($props[$field]) ) {
    
                $link =& Element_Factory::makeElement( 'Bm_Links' );
                
                // Si le Link existe d�ja, on se contente de modifier l'association
                if ( $link->get('href', $props[$field]) ) {
                    $mark->$field = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) return $res;
                } 
                
                // Si aucun Link correspondant n'existe, on en cr�e un
                else {
                    $link =& $this->createLink( $props[$field], true );
                    $mark->$field = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) return $res;
                }
            }
        } // Fin mise � jour des URLs associ�es


        // Mise � jour des propri�t�s
        $mark->title    = isset($props['title'])   ? $props['title']   : $mark->title;
        $mark->summary  = isset($props['summary']) ? $props['summary'] : $mark->summary;
        $mark->lang     = isset($props['lang'])    ? $props['lang']    : $mark->lang;
        
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
     *                                                     defaut: FALSE
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

        // Suppression de caract�res g�nants
        // -- TODO: un vrai callback de nettoyage
        array_walk( $tags, 'trim' );

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
            elseif ( $tag->isAssociatedToMark($mark->id) ) continue;

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
        if ( $tag->isAssociatedToMark($mark->id) )
            return Blogmarks::raiseError( "Le Tag [$tag->id] est d�j� associ� au Mark [$mark->id].", 500 );
 

        // Association
        $res =& $mark->addTagAssoc( $tag->id );
        if ( Blogmarks::isError($res) ) return $res;
        else return true;
    }


    /** R�cup�ration d'un Mark.
     * @param      int      $mark_id      L'identifiant du Mark dans la base de donn�es
     * @return     mixed    Element_bm_Marks ou Blogmarks_Exception en cas d'erreur.
     */
    function getMark( $mark_id ) {
        
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
        // Si le Mark n'existe pas -> erreur 404
        if ( ! $mark->get($mark_id) ) return Blogmarks::raiseError( "Le Mark [$mark_id] n'existe pas.", 404 );

        // permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) {
            if ( $mark->isPrivate() ) return Blogmarks::raiseError( "Permission denied", 401 );
        }

        else { 
            if ( $mark->isPrivate() && ! $user->owns($mark) ) return Blogmarks::raiseError( "Permission denied", 401 );
        }
            
        return $mark;
    }


# ------- LINKS

    /** Cr�ation d'un Link.
     * @param     string     href          URL d�signant la ressource.
     * @param     bool       autofetch     (optionnel) Si vrai, appel automatique de fetchUrlInfo() (defaut: false)
     * @return    object Element_Bm_Links   Le Links cr��
     * @perms     Pour cr�er un Link, il faut �tre authentifi�
     */
    function createLink( $href, $autofetch = false ) {
        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );
        
        $link->href = $href;
        
        // Si le Link existe d�ja on se contente de renvoyer l'existant
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
        if ( Blogmarks::isError($res) ) return $res;

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
        return $this->createTag( $props );
    }


    /** Cr�ation d'un Tag priv�.
     * @param      array     $props
     * @return     mixed      L'uri du tag cr�� ou Blogmarks_Exception en cas d'erreur
     */
    function createPrivateTag( $props = array() ) {
        $props['status'] = 'private';
        return $this->createTag( $props );
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


    /** R�cup�ration d'une liste de Marks en fonction des crit�res pass�s en param�tre.
     * La m�thode attend un tableau associatif d�finissant les crit�res de s�lection : 
     *         - user_login    => recherche au sein des marks d'un utilisateur en particulier (sinon recherche globale)
     *         - date_in       => date au format mysql. On ne recherche que les marks cr��s ult�rieurement � cette date
     *         - date_out      => date au format mysql. On ne recherche que les marks cr��s post�rieurement � cette date
     *         - exclude_tags  => tableau de tags. Les marks d�crits par ces tags ne seront pas s�lectionn�s
     *         - include_tags  => tableau de tags. Seuls les marks d�crits par ces tags seront s�lectionn�s
     *         - select_priv   => bool�en. Si vrai, recherche aussi au sein des marks priv�s 
     *                            (si niveau de permission suffisant).
     *         - order_by      => array( string champs ou array(champs1, champs2, ...), string ASC / DESC )
     * 
     * La m�thode accepte aussi des indexes nomm�s comme ceux que renvoie la m�thode Bm_Marks::getSearchFields().
     * Il sera effectu�e une recherche sur le contenu de ces champs. Le param�tre prend la forme suivante :
     *         - 'nomchamps' => array( '%%', 'LIKE' ) ou
     *         - 'nomchamps' => array( '.*', 'REGEX')
     *
     * Pour la syntaxe de regex, ce r�f�rer � la documentation de MySQL : {@link http://dev.mysql.com/doc/mysql/en/Regexp.html}
     *
     * @param      array      $cond      Tableau associatif d�finissant les crit�re de s�lection des Marks
     *                                     
     *                                    
     * @return     DB_DataObject ou Blogmarks_Exception en cas d'erreur.
     */
    function getMarksList( $cond ) {
        
        $now = date( "Ymd His" );        
        $marks =& Element_Factory::makeElement( 'Bm_Marks' );

        // Recherche au sein des Marks d'un utilisateur donn�
        if ( isset($cond['user_login']) ) {

            // On v�rifie que l'utilisateur existe
            $user =& Element_Factory::makeElement( 'Bm_Users' );
            if ( ! $user->get( 'login', $cond['user_login'] ) ) 
                return Blogmarks::raiseError( "L'utilisateur [". $cond['user_login'] ."] n'existe pas", 404 );

            // -- TODO: V�rification du niveau de permission
            /*
            $cur_user &= $this->_slots['auth']->getConnectedUser();
            $cond['select_priv'] = ( $cur_user->id == $user->id ) ? $cond['select_priv'] : false;
            */
            $marks->bm_Users_id = $user->id;
        }

        // Recherche au sein d'une plage de dates donn�e
        if ( isset($cond['date_in']) )  $marks->whereAdd( "created >= ". $cond['date_in'] );
        if ( isset($cond['date_out']) ) $marks->whereAdd( "created <= ". $cond['date_out'] );

        // INNER JOIN
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->joinAdd( $marks );        

        // S�lection des Marks � exclure
        if ( isset($cond['exclude_tags']) && is_array($cond['exclude_tags']) && count($cond['exclude_tags']) ) {

            // Debug info
            $assocs->debug( 'Excluding '. count($cond['exclude_tags']) .' Tags...', __FUNCTION__, 1 );

            // Constitution de la clause WHERE de la requ�te, � partir de la liste des Tags � ignorer
            foreach ( $cond['exclude_tags'] as $tag_id ) $assocs->whereAdd( "bm_Tags_id = '$tag_id'", 'AND' );

            if ( $assocs->find() ) {
                while ( $assocs->fetch() ) { 
                    $excluded_marks[] = $assocs->bm_Marks_id;
                    
                    // D�doublonnage des r�sultats
                    $excluded_marks = array_unique( $excluded_marks );
                }
            }
        }

        // Reset
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->joinAdd();
        $marks->joinAdd();
        $marks->whereAdd();

        // LEFT JOIN (sinon les Marks non d�crits par des Tags ne sont pas s�lectionn�)
        $marks->joinAdd( $assocs, 'LEFT' );

        // -- S�lection des Marks � inclure
        // Selon un Tag les d�crivant
        if ( isset($cond['include_tags']) && is_array($cond['include_tags']) ) {
            foreach ( $cond['include_tags'] as $tag_id ) $marks->whereAdd( "bm_Tags_id = '$tag_id'", 'OR' );
        }

        // On ne s�lectionne pas les Marks dont le Tag est exclu
        if ( isset($excluded_marks) ) {
            foreach ( $excluded_marks as $mark_id ) $marks->whereAdd( "bm_Marks_id != '$mark_id'", 'AND' );
        }
        
        // Par d�faut, on ne s�lectionne que les Marks publics
        if ( ! isset($cond['select_priv']) || $cond['select_priv'] == false ) {
            $marks->whereAdd( "issued != 0 ",  'AND' );
            $marks->whereAdd( "issued < '$now'", 'AND' );
        }

        // Ajout des clauses de recherche
        foreach ( $marks->getSearchFields() as $f ) {
            // On doit rechercher sur un des champs
            if ( isset($cond[$f]) && is_array($cond[$f]) ) {
                
                // Constitution du WHERE
                // "nomchamps LIKE / REGEXP pattern"
                $q = "$f ". $cond[$f][1] ." '". $marks->escape($cond[$f][0]). "'";

                // Le champs sur lequel on effectue la recherche se trouve dans une autre table
                // TODO -- faire fonctionner le bouzin
                if ( count(array_keys($marks->getLinksFields(), $f)) ) {
                    $links =& Element_Factory::makeElement( 'Bm_Links' );
                    $marks->joinAdd( $links, 'LEFT' );
                    //                    $links->href = 'http';
                    //                    $marks->whereAdd( $q, 'AND' );
                }

                // Recherche simple
                else $marks->whereAdd( $q, 'AND' );
            }
        }

        // Tri des r�sultats
        if ( isset($cond['order_by']) ) {
            
            $fields = $cond['order_by'][0];
            $dir = isset($cond['order_by'][1]) ? $cond['order_by'][1] : 'ASC';
            $str_order = null;

            // Tri selon champs multiples
            if ( is_array($fields) ) {

                // Constitution de la clause
                foreach ( $fields as $f ) $str_order .= "$f,";

                // Suppression de la virgule finale
                $str_order = substr( $str_order, 0, strlen($str_order) - 1 );
            }

            // Tri selon un champs unique
            elseif ( is_string($fields) ) {
                $str_order = $fields;
            }

            // Direction du tri
            $str_order = "$str_order $dir";

            $marks->orderBy( $str_order );

        }

        // HACK: permet de ne pas avoir de doublons
        $marks->groupBy( 'id' );

        return ( $marks->find() > 0 ? $marks : Blogmarks::raiseError( 'Aucun Mark disponible avec ces crit�res.', 404 ) );

                   
    }


# ------- USERS

    /** Cr�ation d'un utilisateur.
     * @param       array      $props      Tableau associatif des propri�t�s de l'utilisateur � cr�er (login, pwd, email)
     * @return      mixed      true ou Blogmarks_Exception en cas d'erreur
     *
     * @todo        Checks des permissions dans les m�thodes *User
     */
    function createUser( $props ) {
        
        $user =& Element_Factory::makeElement( 'Bm_Users' );

        // On v�rifie qu'un utilisateur avec un pseudo identique n'existe pas d�j�
        $user->login = $props['login'];
        if ( $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$user->login] existe d�j�.", 470 );

        // Les mots de passe sont stock�s en md5
        $user->pwd = md5( $props['pwd'] );

        // Email
        $user->email = $props['email'];
        
        // Insertion dans la base de donn�es
        $res =& $user->insert();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        return true;
        
    }

    
    /** Mise � jour des propri�t�s d'un utilisateur.
     * On ne peut pas mettre � jour le login.
     * @param      string     $login      Le login de l'utilisateur � mettre � jour
     * @param      array      $props      Tableau associatif des propri�t�s � mettre � jour (pwd, email)
     * @return     mixed      true ou Blogmarks_Exception en cas d'erreur
     */
    function updateUser( $login, $props ) {
        
        $user =& Element_Factory::makeElement( 'Bm_Users' );

        // On v�rifie que l'utilisateur existe
        $user->login = $login;
        if ( ! $user->find(true) ) return Blogmarks::raiseError( "L'utilisateur [$user->login] n'existe pas.", 404 );

        // Mise � jour des propri�t�s
        $user->pwd   = isset($props['pwd'])   ? md5($props['pwd']) : $user->pwd;
        $user->email = isset($props['email']) ? $props['email']    : $user->email;

        // Mise � jour
        $res =& $user->update();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );
    }


    /** Suppression d'un utilisateur
     * @param      string      $login      Le login de l'utilisateur.
     * @return     mixed       true ou Blogmarks_Exception en cas d'erreur
     */
    function deleteUser( $login ) {

        $user =& Element_Factory::makeElement( 'Bm_Users' );

        // On v�rifie que l'utilisateur existe
        $user->login = $login;
        if ( ! $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$user->login] n'existe pas.", 404 );

        // Suppression
        $res =& $user->delete();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        return true;
    }

    /** Permet de savoir si l'utilisateur est authentifi�.
     * @return      bool       true ou false
     */
    function userIsAuthenticated() {
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return false;
        return $user->isAuthenticated();
        
    }


    /** Renvoie des informations � propos de l'utilisateur connect�.
     * Renvoie un tableau associatif si la m�thode est appel�e sans param�tre, ou la valeur 
     * du champs ($field) pass� en param�tre.
     *
     * @param      string      $field      (optionnal) 
     * @return     mixed       array ou string
     */
    function getUserInfo( $field = null ) {
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;

        // Champs dont on a le droit de r�cup�rer la valeurs
        $info_fields = $user->getInfoFields();

        $ret = null;

        // Un seul champs est demand�
        if ( $field ) $ret = $user->$field;

        // Renvoi de toutes les infos
        else foreach ( $info_fields as $field ) $ret[$field] = $user->$field;

        return $ret;
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

    /** D�connexion de l'utilisateur en cours.
     * @return      bool */
    function disconnectUser() { return $this->_slots['auth']->disconnectUser(); }

                       
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

            // On ne recr�e l'objet que si n�cessaire
            if ( ! isset($this->_slots[$slot_name]) ) {
                
                // Inclusion de la d�claration de la classe
                require_once $class_info[1];
                
                // Instanciation
                $obj =& new $class_info[0];
                
                $this->_slots[$slot_name] = $obj;
            }
        }
        
        return true;
        
    }
}
?>
