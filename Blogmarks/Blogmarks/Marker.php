<?php
/** Déclaration de la classe BlogMarks_Marker
 * @version    $Id: Marker.php,v 1.23 2004/06/26 16:41:56 benfle Exp $
 * @todo       Comment fonctionne les permissions sur les Links ?
 */

# -- Includes
require_once 'PEAR.php';
require_once 'Blogmarks.php';
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
    
    var $_static;

# ------------------------ #
# -- METHODES PUBLIQUES -- #
# ------------------------ #


    /** Retourne une référence à Marker, qui n'est créé que s'il n'existe pas encore.
     * Doit être appelé de cette façon : <code>$marker =& new Blogmarks_Marker::singleton();</code>
     * @return      object Blogmarks_Marker
     */
    function &singleton() {
        static $instance;

        if ( ! isset($instance) ) $instance = new Blogmarks_Marker;

        return $instance;
    }


    /** Constructeur. 
     * @warning      Ne doit jamais être appelé directement, à part par Blogmarks_Marker::singleton() 
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
    
    /** Création d'un mark.
     * Dans $props, en plus des propriétés correspondants à la DB, on peux renseigner deux clés supplémentaires :
     *    - $props['tags']      -> un tableau d'id de Tags à associer au Mark
     *    - $props['public']    -> true, false, ou une date future (timestamp) à laquelle le Mark deviendra public.
     *
     * @param      array     $props      Un tableau associatif de paramètres décrivant le mark.
     *                                   Les clés du tableau correpondent aux noms des champs de la base de données.
     * @return     mixed     Le Mark créé ou Blogmarks_Exception en cas d'erreur.
     *
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
        if ( $link->get( 'href', $props['related'] ) == 0 ) {
            $link = $this->createLink( $props['related'], true );
            if ( Blogmarks::isError($link) ) { return $link; }
        }


        // Création du Mark.
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
        // Le possesseur du Mark est l'utilisateur connecté.
        $u =& $this->_slots['auth']->getConnectedUser();

        // Si le Mark n'existe pas, on le crée
        $mark->author = $u->login;
        $mark->related = $link->id;

        if ( ! $mark->find(true) ) {

            // Définition des propriétés
            $mark->title    = isset($props['title'])   ? $props['title']   : null;
            $mark->summary  = isset($props['summary']) ? $props['summary'] : null;
            $mark->lang     = isset($props['lang'])    ? $props['lang']    : null;

            // Création des Links associés
            foreach ( $mark->getLinksFields() as $field ) {
                $link =& Element_Factory::makeElement( 'Bm_Links' );

                // Si le Link n'existe pas, on le crée
                if ( isset($props[$field]) && $props[$field] != '' && ! $link->get('href', $props[$field]) ) {
                    $link =& $this->createLink( $props[$field], true );
                }
                $mark->$field = $link->id;

            } // -- Fin de la création des Links associés

            // Dates
            $date = date("Ymd His");
            $mark->created  = $date;
            $mark->modified = $date;

            // Public / privé
            $props['public'] = isset($props['public']) ? $props['public'] : true;  // Les Marks sont publics par défaut
            if     ( $props['public'] == true  ) $pub = $date;
            elseif ( $props['public'] == false ) $pub = 0;
            else   $pub = $props['public'];
            $mark->issued   = $pub;

            // Insertion dans la base de données
            $res = $mark->insert();
            if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        } 
        
        // Si le Mark existe déja -> erreur 500
        else { return Blogmarks::raiseError( "Le Mark existe déjà.", 500 ); }

        // Gestion des associations Mark / Tags
        if ( is_array($props['tags']) && count($props['tags']) ) {
            $res = $this->associateTagsToMark( $props['tags'], $mark );
            if ( Blogmarks::isError($res) ) return $res;
        }

        return $mark;
    }
    

    /** Mise à jour d'un Mark.
     * @param      int      $id       ID identifiant le mark
     * @param      array    $props    Un tableau de propriétés à mettre à jour.
     *                                La valeur de l'index 'mergetags' (false par défaut) sera passée à Blogmarks_Marker::associateTagsToMark()
     * @return     mixed    Le Mark créé ou Blogmarks_Exception en cas d'erreur.
     * @perms     Pour mettre à jour un Mark, il faut le posséder ou être administrateur.
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
        if ( ! $user->owns( $mark ) && ! $user->isAdmin() ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Mise à jour des URLs associées
        foreach ( $mark->getLinksFields() as $field ) {

            // Une mise à jour est requise
            if ( isset($props[$field]) && $props[$field] != '' ) {
    
                $link =& Element_Factory::makeElement( 'Bm_Links' );
                
                // Si le Link existe déja, on se contente de modifier l'association
                if ( $link->get('href', $props[$field]) ) {
                    $mark->$field = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) return $res;
                } 
                
                // Si aucun Link correspondant n'existe, on en crée un
                else {
                    $link =& $this->createLink( $props[$field], true );
                    $mark->$field = $link->id;
                    $res = $mark->update();
                    if ( Blogmarks::isError($res) ) return $res;
                }
            }
        } // Fin mise à jour des URLs associées


        // Mise à jour des propriétés
        $mark->title    = isset($props['title'])   ? $props['title']   : $mark->title;
        $mark->summary  = isset($props['summary']) ? $props['summary'] : $mark->summary;
        $mark->lang     = isset($props['lang'])    ? $props['lang']    : $mark->lang;
        
        // Dates
        $date = date("Ymd His");
        $mark->modified = $date;

        // Public / privé
        $props['public'] = isset($props['public']) ? $props['public'] : true;  // Les Marks sont publics par défaut
        if     ( $props['public'] == true) $pub = $date;
        elseif ( $props['public'] == false ) $pub = 0;
        else   $pub = $props['public'];
        $mark->issued   = $pub;

        // Tags
        if ( is_array($props['tags']) ) {
            $merge = isset( $props['mergetags'] ) ? $props['mergetags'] : false; // mergetags est FALSE par défaut
            $res =& $this->associateTagsToMark( $props['tags'], $mark, $merge );
            if ( Blogmarks::isError($res) ) return $res;
        }

        // Insertion dans la base de données
        $res = $mark->update();
        if ( Blogmarks::isError($res) ) return $res;

        return $mark;
    }


    /** Suppression d'un mark.
     * @param      int      $id       Identifiant du Mark
     * @return     mixed    true ou Blogmarks_Exception en cas d'erreur.
     * @perms      Pour effacer un Mark, il faut le posséder ou être administrateur.
     */
    function deleteMark( $id ) {
        $mark =& Element_Factory::makeElement( 'Bm_Marks' );
        
        // Si le mark à effacer n'existe pas -> erreur 404
        if ( ! $mark->get( $id ) ) return Blogmarks::raiseError( "Le mark [$id] n'existe pas.", 404 );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns( $mark ) && ! $user->isAdmin() ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Supression des associations avec des Tags
        foreach ( $this->getTags($id) as $tag_id ) $mark->remTagAssoc( $tag_id );

        // Suppression du Mark
        $res = $mark->delete();
        if ( Blogmarks::isError($res) ) return $res;

        return true;
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
     *                                                     defaut: FALSE
     * @return
     * @perms      il faut posséder le Mark ou être administrateur pour éditer les associations de Tags
     *
     * @todo      Comportement à définir pour la gestion des erreurs : arret immédiat en cas d'erreur ?
     */
    function associateTagsToMark( $tags, $mark, $merge = false ) {

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($mark) && ! $user->isAdmin() ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Nettoyage des Tags avant association

        $this->_cleanTags( $tags ); 


		// Récupére l'info (si nécessaire), déassocie ou associe ensuite

        foreach ( $tags as $tag_name ) 
		{
			// Récupère les infos du tag s'il existe et le crée sinon

			$tag =& Element_Factory::makeElement( 'Bm_Tags' );

            // Utilisateur connecté
            $user =& $this->_slots['auth']->getConnectedUser();
	
			// Recherche du mark, création si nécessaire
			if ( ereg ('^private:(.+)$', $tag_name, $regs) )
			{
				// Tag privé
				$tag->author = $user->login;
				$tag->title  = $regs[1];

				// Tag non-existant
				if ( ! $tag->find() ) 
				{
					// Création d'un tag privé correspondant
			        $res =& $this->createPrivateTag( array('title'  => $regs[1],
														   'author' => $user->login) );
					$tag = $res;
				} else
					$tag->fetch();
			} else
			{
				// Tag public
				$tag->author = NULL;
				$tag->title  = $tag_name;

				// Tag non-existant
				if ( ! $tag->find() ) 
				{
					// Création d'un tag public correspondant
			        $res =& $this->createPublicTag( array('title'  => $tag_name) );
					$tag = $res;
				} else
					$tag->fetch();            
			}

			// enregistre l'id du tag pour les desacciations
			$tags_id[] = $tag->id;

			if ( Blogmarks::isError($tag) ) $this->_errorStack[] =& $tag;

			// Association au Mark

			$res =& $mark->addTagAssoc( $tag->id );
			if ( Blogmarks::isError($res) ) $this->_errorStack[] =& $res; // _errorStack ne va pas durer....

		} # -- fin foreach

		
		// Désassociation
		if ( ! $merge )
		{
			$diff = array_diff( $this->getTags($mark->id), $tags_id);
			foreach ( $diff as $tag_id )
			{
				$mark->remTagAssoc( $tag_id );
			}
		}
	}


    /** Récupération d'un Mark.
     * @param      int      $mark_id      L'identifiant du Mark dans la base de données
     * @return     mixed    Element_bm_Marks ou Blogmarks_Exception en cas d'erreur.
     * @perms      Si Le Mark est privé, il faut le posséder ou être administrateur pour le récupérer.
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
            if ( $mark->isPrivate() 
                 && ! $user->owns($mark) 
                 && ! $user->isAdmin() ) return Blogmarks::raiseError( "Permission denied", 401 );
        }
            
        return $mark;
    }


# ------- LINKS

    /** Création d'un Link.
     * @param     string     href          URL désignant la ressource.
     * @param     bool       autofetch     (optionnel) Si vrai, appel automatique de fetchUrlInfo() (defaut: false)
     * @return    object Element_Bm_Links   Le Links créé
     * @perms     Pour créer un Link, il faut être authentifié
     */
    function createLink( $href, $autofetch = false ) {
        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAuthenticated() ) return Blogmarks::raiseError( "Permission denied", 401 );
        
        // Normalisation de l'URL
        //        $this->_normalizeUrl( $href );

        $link->href = $href;
        
        // Si le Link existe déja on se contente de renvoyer l'existant
        if ( $link->find(true) ) { return  $link; }

        // Sinon, création du Link
        else { $link->insert(); }


        // Récupération des informations de la page (si autofetch)
        if ( $autofetch == true ) { 
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

        // Normalisation de l'URL
        //        $this->_normalizeUrl( $props['$href'] );
        
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
        if ( Blogmarks::isError($res) ) return $res;

        return $link;
    }


    /** Suppression d'un Link. 
     * @param    int      L'identifiant du Link dans la base de données
     * @return   mixed    true ou Blogmarks_Eception en cas d'erreur
     * @perms    Pour effacer un Link, il faut être administrateur
     */
    function deleteLink( $id ) {

        // permissions: 
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->isAdmin()) return Blogmarks::raiseError( "Permission denied", 401 );


        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Si le Link à effacer n'existe pas -> erreur 404
        if ( ! $link->get($id) ) {
            return Blogmarks::raiseError( "Le Link [$id] n'existe pas.", 404 );
        }

        // Suppression du Link
        $res = $link->delete();
        if ( Blogmarks::isError($res) ) return $res;

        return true;        
    }



# ------- TAGS

    /** Création d'un nouveau Tag.
     * @param      array      $props     Un tableau associatif décrivant les propriétés du Tag
     * @return     mixed      L'instance du Bm_Tags créé ou Blogmarks_Exception en cas d'erreur
     * @perms      Pour créer un Tag, il faut être authentifié
     */
    function createTag( $props = array() ) {

		// permissions
		$user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
		if ( ! $user->isAuthenticated()) return Blogmarks::raiseError( "Permission denied", 401 );

		// si ce n'est pas un administrateur pour tag publique , il ne peut indiquer summary, issued et ico
		if ( !$user->isAdmin() && $props['author'] == null ) 
		{ 
			unset ($props['issued']); 
			unset ($props['summary']);
			unset ($props['ico']);
		}
	
        $tag =& Element_Factory::makeElement( 'Bm_Tags' );
        
        // Si le tag existe déja -> erreur 500
		$tag->title  = $props['title'];
		$tag->author = $props['author'];

        if ( $tag->find() ) return Blogmarks::raiseError( "Le Tag [$tag->id] existe déjà.", 500 );

		// ajout de la date de derniere modification si nécessaire
		$props['modified'] = date('Ymd His');

        // Initialisation des propriétés de l'objet
        $tag->populateProps( $props );

        // Insertion dans la base de données
        $res = $tag->insert();
        if ( Blogmarks::isError($res) ) return $res;
        
        return $tag;
    }

 
    /** Création d'un Tag public.
     * @param      array     $prop
     * @return     mixed     L'instance du Bm_Tags créé ou Blogmarks_Exception en cas d'erreur
     */
    function createPublicTag( $props = array() ) {
		$props['author'] = null;
        return $this->createTag( $props );
    }


    /** Création d'un Tag privé.
     * @param      array     $props
     * @return     mixed     L'instance du Bm_Tags créé ou Blogmarks_Exception en cas d'erreur
     */
    function createPrivateTag( $props = array() ) {

		//
		if ( !isset ( $props['author'] ) || $props['author'] == '' )
			return Blogmarks::raiseError( "Ne peut créer un tag privé sans propriétaire.", 500 );
        return $this->createTag( $props );
    }

    
    /** Mise à jour d'un Tag.
     * @param      string    $id     L'identifiant du Tag dans la base de données
     * @param      array     $props  Un tableau associatif décrivant les propriétés à mettre à jour
     *
     * @return     mixed     true ou Blogmarks_Exception en cas d'erreur
     * @perms      Pour mettre à jour un Tag, il faut le posséder ou être administrateur
     */
    function updateTag( $id, $props ) {
        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

        // Si le Tag n'existe pas -> erreur 404
        $tag->id = $id;
        if ( ! $tag->find(true) ) { return Blogmarks::raiseError( "Le tag [$id] n'existe pas.", 404 ); }

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( $tag->isPrivate() && ! $user->owns($tag) 
			|| $tag->isPublic() && ! $user->isAdmin() ) 
		{
			return Blogmarks::raiseError( "Permission denied", 401 );
		}

        // Mise à jour des propriétés du Tag
        $tag->populateProps( $props );
        $tag->id = $id;
		if ( $tag->isPrivate() )
			$tag->author = $user->login;
		$tag->modified = date("Ymd His");
        $res = $tag->update();
        if ( Blogmarks::isError($res) ) { return $res; }

        return true;
    }


    /** Suppression d'un tag.
     * @param     string    $id    L'identifiant du Tag dans la base de données.
     * @perms     Pour effacer un Tag, il faut le posséder ou être administrateur
     */
    function deleteTag( $id ) {
        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

        // Si le Link à effacer n'existe pas -> erreur 404
        if ( ! $tag->get($id) ) return Blogmarks::raiseError( "Le Tag [$id] n'existe pas.", 404 );

        // Permissions
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;
        if ( ! $user->owns($tag) ) return Blogmarks::raiseError( "Permission denied", 401 );

        // Suppression des associations Tag / Marks correspondantes
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->bm_Tags_id = $id;
        $assocs->delete();

        // Suppression du Tag
        $tag->delete();

        return true;        
    }

    /** Renvoie la valeur d'un tag à partir de son titre et de son propriétaire pour les tags privés.
     * @param      string         $title       Titre du tag recherché
	 * @param      string         $user        Login du propriétaire si c'est un tag privé
     * @return     mixed          Bm_Tags ou Blogmarks_Exception en cas d'erreur
     */
    function getTagId ($title, $user = null ) {

        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

		// permission
		if ( $user != null )
		{
			if ( $this->userIsAuthenticated() )
			{
				$rec_user = $this->_slots['auth']->getConnectedUser();
				if ( Blogmarks::isError ($rec_user) )
					return $rec_user;
				if ( $user != $rec_user->login )
					return BLogmarks::raiseError( "Permission denied", 401);
			} else
				return BLogmarks::raiseError( "Permission denied", 401);
		}

        // Récupération du tag
		$tag->title = $title;
		$tag->author = $user;
		if ( $tag->find() == 0 )
			if ( $user == null )
				return Blogmarks::raiseError( "Aucun tag public [$title] n'existe", 404);
			else
				return Blogmarks::raiseError( "Aucun tag privé [$title] n'existe pour [$user]", 404);
		$tag->fetch();
        return $tag->id;
    }

	/** Renvoie la valeur d'un tag.
	 * @param      int        $id     Identifiant du tag recherché.
	 * @return     mixed      Bm_Tags ou Blogmarks_Exception en cas d'erreur
	 */
	 function getTag ($id) {

		$tag =& Element_Factory::makeElement( 'Bm_Tags' );
		$tag->get('id', $id);

		return $tag;
	 }

	/** Renvoie la liste des tags d'un mark
	* @param     int        $id     L'identifiant du mark dont on veut la liste de tags
	* @perms     Pour avoir les tags privés, il faut posséder le mark
	*/
	function getTags( $id )
	{
		$mark =& Element_Factory::makeElement( 'Bm_Marks' );

		if ( ! $mark->get ('id', $id) )
			return Blogmarks::raiseError( "Le mark [$id] n'existe pas", 404 );

		if ( $mark->isPrivate() )
		{
			$user =& $this->_slots['auth']->getConnectedUser();
		    if ( Blogmarks::isError($user) ) return $user;

			if ( ! $user->owns($mark) )
				return Blogmarks::raiseError( "Permission denied", 401 );
			else
				$private = true;
		} else
		{
			if ( $this->userIsAuthenticated() )
			{
				$user =& $this->_slots['auth']->getConnectedUser();
				if ( Blogmarks::isError($user) ) return $user;
				if ( ! $user->owns($mark) )
					$private = false;
				else
					$private = true;
			} else
				$private = false;
		}

		// Construit la liste des tags

		$arr = array();
		
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->bm_Marks_id = $id;
        $assocs->find();

        while ( $assocs->fetch() ) 
		{
			$tag =& Element_Factory::makeElement( 'Bm_Tags' );
			$tag->id = $assocs->bm_Tags_id;
			$tag->find();

			while ( $tag->fetch() )
			{
				if ( $tag->author == NULL || $private == true)
					$arr[] = $tag->id;
			}
		}

        return $arr;
	}
		
# ------- MARKSLISTS


    /** Récupération d'une liste de Marks en fonction des critères passés en paramètre.
     * La méthode attend un tableau associatif définissant les critères de sélection : 
     *         - user_login    => recherche au sein des marks d'un utilisateur en particulier (sinon recherche globale)
     *         - date_in       => date au format mysql. On ne recherche que les marks créés ultérieurement à cette date
     *         - date_out      => date au format mysql. On ne recherche que les marks créés antérieurement à cette date
     *         - exclude_tags  => tableau de tags. Les marks décrits par ces tags ne seront pas sélectionnés
     *         - include_tags  => tableau de tags. Seuls les marks décrits par ces tags seront sélectionnés
     *         - select_priv   => booléen. Si vrai, recherche aussi au sein des marks privés 
     *                            (si niveau de permission suffisant).
     *         - order_by      => array( string champs ou array(champs1, champs2, ...), string ASC / DESC )
     * 
     * La méthode accepte aussi des indexes nommés comme ceux que renvoie la méthode Bm_Marks::getSearchFields().
     * Il sera effectuée une recherche sur le contenu de ces champs. Le paramètre prend la forme suivante :
     *         - 'nomchamps' => array( '%%', 'LIKE' ) ou
     *         - 'nomchamps' => array( '.*', 'REGEX')
     *
     * Pour la syntaxe de regex, ce référer à la documentation de MySQL : {@link http://dev.mysql.com/doc/mysql/en/Regexp.html}
     *
     * @param      array      $cond      Tableau associatif définissant les critère de sélection des Marks
     *                                     
     *                                    
     * @return     DB_DataObject ou Blogmarks_Exception en cas d'erreur.
     */
    function getMarksList( $cond ) {

        $now = date( "Ymd His" );        
        $marks =& Element_Factory::makeElement( 'Bm_Marks' );

		$cur_user =& $this->_slots['auth']->getConnectedUser();

        // Recherche au sein des Marks d'un utilisateur donné
        if ( isset($cond['user_login']) ) {

            // On vérifie que l'utilisateur existe
            $user =& Element_Factory::makeElement( 'Bm_Users' );
            if ( ! $user->get( 'login', $cond['user_login'] ) ) 
                return Blogmarks::raiseError( "L'utilisateur [". $cond['user_login'] ."] n'existe pas", 404 );

			if ( $user->isAuthenticated() )
				$cond['select_priv'] = true;
			else
				$cond['select_priv'] = false;

            $marks->author = $user->login;
        }

        else {
            // -- HACK: Les recherches globales ne se font qu'au sein des marks publics.
            $cond['select_priv'] = false;
        }

        // Recherche au sein d'une plage de dates donnée
        if ( isset($cond['date_in']) )  $marks->whereAdd( "created >= ". $cond['date_in'] );
        if ( isset($cond['date_out']) ) $marks->whereAdd( "created <= ". $cond['date_out'] );

        // INNER JOIN
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->joinAdd( $marks );        

        // Sélection des Marks à exclure
        if ( isset($cond['exclude_tags']) && is_array($cond['exclude_tags']) && count($cond['exclude_tags']) ) {

            // Debug info
            $assocs->debug( 'Excluding '. count($cond['exclude_tags']) .' Tags...', __FUNCTION__, 1 );

            // Constitution de la clause WHERE de la requête, à partir de la liste des Tags à ignorer
            foreach ( $cond['exclude_tags'] as $tag_title ) 
			{
				if ( ereg ('^private:(.+)$', $tag_title, $regs) )
				{
					if ( Blogmarks::isError ($cur_user) )
						return $cur_user;
					$tag_id = $this->getTagId ($regs[1], $cur_user->login);
				} else
					$tag_id = $this->getTagId ($tag_title, null);

				$assocs->whereAdd( "bm_Tags_id = '$tag_id'", 'AND' );
			}

            if ( $assocs->find() ) {
                while ( $assocs->fetch() ) { 
                    $excluded_marks[] = $assocs->bm_Marks_id;

                    // Dédoublonnage des résultats
                    $excluded_marks = array_unique( $excluded_marks );
                }
            }
        }

        // Reset
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->joinAdd();
        $marks->joinAdd();
        $marks->whereAdd();

        // LEFT JOIN (sinon les Marks non décrits par des Tags ne sont pas sélectionné)
        $marks->joinAdd( $assocs, 'LEFT' );

        // -- Sélection des Marks à inclure
        // Selon un Tag les décrivant
        if ( isset($cond['include_tags']) && is_array($cond['include_tags']) ) {
            foreach ( $cond['include_tags'] as $tag_title )
			{
				if ( ereg ('^private:(.+)$', $tag_title, $regs) )
				{
					if ( Blogmarks::isError ($cur_user) )
						return $cur_user;
					$tag_id = $this->getTagId ($regs[1], $cur_user->login );
				} else
					$tag_id = $this->getTagId ($tag_title, null);

				$marks->whereAdd( "$assocs->__table.bm_Tags_id = '$tag_id'", 'OR' );
			}
		}

        // On ne sélectionne pas les Marks dont le Tag est exclu
        if ( isset($excluded_marks) ) {
            foreach ( $excluded_marks as $mark_id )
				$marks->whereAdd( "$assocs->__table.bm_Marks_id != '$mark_id'", 'AND' );
		}
        // Ajout des clauses de recherche
        foreach ( $marks->getSearchFields() as $f ) {
            // On doit rechercher sur un des champs
            if ( isset($cond[$f]) && is_array($cond[$f]) ) {

                /*
                // Préfixage des champs de tables externes
                if ( count(array_keys($marks->getLinksFields(), $f)) ) { 
                    $links =& Element_Factory::makeElement( 'Bm_Links' );
                    $field =  $links->__table .'.'. $f;
                }
                */

                // Constitution du WHERE
                // "nomchamps LIKE / REGEXP pattern"
                $q = "$f ". $cond[$f][1] ." '". $marks->escape($cond[$f][0]). "'";

                // Le champs sur lequel on effectue la recherche se trouve dans une autre table
                // TODO -- faire fonctionner le bouzin
                if ( count(array_keys($marks->getLinksFields(), $f)) ) {
                    $marks->joinAdd( $links, 'LEFT' );
                    $marks->whereAdd( $q, 'AND' );
                }

                // Recherche simple
                else $marks->whereAdd( $q, 'OR' );
            }
        }

        
        // Par défaut, on ne sélectionne que les Marks publics
        if ( ! $cond['select_priv'] ) {
            $marks->whereAdd( "issued > 0",  'AND' );
            $marks->whereAdd( "issued <= '$now'", 'AND' );
        }


        // Tri des résultats
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

        // -- HACK: permet de ne pas avoir de doublons
        $marks->groupBy( 'id' );

        return ( $marks->find() > 0 ? $marks : Blogmarks::raiseError( 'Aucun Mark disponible avec ces critères.', 444 ) );

                   
    }


# ------- USERS

    /** Création d'un utilisateur.
     * @param       array      $props      Tableau associatif des propriétés de l'utilisateur à créer (login, pwd, email)
     * @return      mixed      true ou Blogmarks_Exception en cas d'erreur
     *
     * @todo        Checks des permissions dans les méthodes *User
     */
    function createUser( $props ) {
        
        $user =& Element_Factory::makeElement( 'Bm_Users' );

        // On vérifie qu'un utilisateur avec un pseudo identique n'existe pas déjà
        $user->login = $props['login'];
        if ( $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$user->login] existe déjà.", 470 );

        // Les mots de passe sont stockés en md5
        $user->pwd = md5( $props['pwd'] );

        // Email
        $user->email = $props['email'];
        
        // Insertion dans la base de données
        $res =& $user->insert();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        return true;
        
    }

    
    /** Mise à jour des propriétés d'un utilisateur.
     * On ne peut pas mettre à jour le login.
     * @param      string     $login      Le login de l'utilisateur à mettre à jour
     * @param      array      $props      Tableau associatif des propriétés à mettre à jour (pwd, email)
     * @return     mixed      true ou Blogmarks_Exception en cas d'erreur
     */
    function updateUser( $login, $props ) {
        
        $user =& Element_Factory::makeElement( 'Bm_Users' );

        // On vérifie que l'utilisateur existe
        $user->login = $login;
        if ( ! $user->find(true) ) return Blogmarks::raiseError( "L'utilisateur [$user->login] n'existe pas.", 404 );

        // Mise à jour des propriétés
        $user->pwd   = isset($props['pwd'])   ? md5($props['pwd']) : $user->pwd;
        $user->email = isset($props['email']) ? $props['email']    : $user->email;

        // Mise à jour
        $res =& $user->update();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );
    }


    /** Suppression d'un utilisateur
     * @param      string      $login      Le login de l'utilisateur.
     * @return     mixed       true ou Blogmarks_Exception en cas d'erreur
     */
    function deleteUser( $login ) {

        $user =& Element_Factory::makeElement( 'Bm_Users' );

        // On vérifie que l'utilisateur existe
        $user->login = $login;
        if ( ! $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$user->login] n'existe pas.", 404 );

        // Suppression
        $res =& $user->delete();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        return true;
    }


    /** Permet de savoir si l'utilisateur est authentifié.
     * @return      bool       true ou false
     */
    function userIsAuthenticated() {
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return false;
        return $user->isAuthenticated();
        
    }


    /** Renvoie des informations à propos de l'utilisateur connecté.
     * Renvoie un tableau associatif si la méthode est appelée sans paramètre, ou la valeur 
     * du champs ($field) passé en paramètre.
     *
     * @param      string      $field      (optionnal) 
     * @return     mixed       array ou string
     */
    function getUserInfo( $field = null ) {
        $user =& $this->_slots['auth']->getConnectedUser();
        if ( Blogmarks::isError($user) ) return $user;

        // Champs dont on a le droit de récupérer la valeurs
        $info_fields = $user->getInfoFields();

        $ret = null;

        // Un seul champs est demandé
        if ( $field ) $ret = $user->$field;

        // Renvoi de toutes les infos
        else foreach ( $info_fields as $field ) $ret[$field] = $user->$field;

        return $ret;
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

    /** Déconnexion de l'utilisateur en cours.
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

            // On ne recrée l'objet que si nécessaire
            if ( ! isset($this->_slots[$slot_name]) ) {
                
                // Inclusion de la déclaration de la classe
                require_once $class_info[1];
                
                // Instanciation
                $obj =& new $class_info[0];
                
                $this->_slots[$slot_name] = $obj;
            }
        }
        
        return true;
        
    }


    /** Nettoyage de Tags à associer à un Mark.
     * Actions effectuées:
     *  - trim
     *  - supression des chaînes vides
     *
     * @param      string      $tags
     */
    function _cleanTags( &$tags ) {
        for ( $i = 0; $i < count($tags); $i++ ) {
            // Trim
            $tags[$i] = trim($tags[$i]);
            
            // Supression des Tags vides
            if ( empty($tags[$i]) ) unset( $tags[$i] );
        }
        return;
    }

    /** Normalisation d'une URL. La modification se fait en place.
     * Actions effectuées :
     *  - Strip de index.xxx / default.xxx
     *  - Ajout d'un / final
     *
     * @param      string      $url
     */
    function _normalizeUrl( & $url ) {
        
    }


}
?>