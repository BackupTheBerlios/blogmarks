<?php
/**
 * Table Definition for bm_Marks
 */
require_once 'Blogmarks/Element.php';
require_once 'Blogmarks/Element/Factory.php';

/** Mark.
 * @package     Elements
 * @uses        Blogmarks_Element
 * @uses        Element_Factory
 */
class Element_Bm_Marks extends Blogmarks_Element 
{

    /** Champs correpondants à des Links.
     * @var      array */
    var $_links_fields = array( 'related', 'via' );

    /** Champs sur lesquels on peut effectuer une recherche
     * @var      array */
    var $_search_fields = array( 'title', 'summary' );
	
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */
    var $__table = 'bm_marks';                        // table name
	var $id;                              // int(11)  not_null primary_key auto_increment
	var $title;                           // string(255)  
	var $issued;                          // datetime(19)  
	var $created;                         // datetime(19)  
	var $modified;                        // datetime(19)  not_null
	var $related;                         // int(11)  not_null multiple_key
	var $via;                             // int(11)  
	var $screenshot;                      // string(255)  
	var $author;                          // string(255)  not_null multiple_key
	var $summary;                         // blob(65535)  blob
	var $lang;                            // string(10)
	
	/* ZE2 compatibility trick*/
	function __clone() { return $this;}
	
	/* Static get */
	function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_marks',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE

# ------ AUTH
    
    /** Permet de savoir si le Mark est public.
     * Comportement :
     *   - issued < date(now)   -> public
     *   - issued == 0          -> private
     *   - issued > date(now)   -> private (pour publication programmée)
     *
     * @return       bool
     */
    function isPublic() {
        $now = date("Y-m-d H:i:s");
        if ( $this->issued && $this->issued < $now ) return true;
        else return false;
    }

    /** Permet de savoir si un Mark est privé.
     * @return      bool
     */
    function isPrivate() { return ! $this->isPublic(); }


    /** Renvoie le login du posseseur du Mark.
     * @return      string      Le login du possesseur du mark
     */
    function getOwner() {
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        $user->get( $this->author );

        return $user->login;
    }


# ------ TAGS
    
    /** Associe le tag au Mark.
     * @param      string      $tag_id
     * @return     mixed       true ou Blogmarks_Exception en cas d'erreur
     */
    function addTagAssoc( $tag_id ) {

        // Définition de l'association
        $assoc_def =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assoc_def->bm_Marks_id = $this->id;
        $assoc_def->bm_Tags_id  = $tag_id;

        // Insertion dans la base de données
        if ( ! $assoc_def->find() ) { 
            $this->debug( "Insertion de l' association [$this->id / $tag_id].", __FUNCTION__, 1 );
            $assoc_def->insert();
        }
        else return Blogmarks::raiseError( "Le Tag [$tag_id] est déjà associé au Mark [$this->id].", 500 );

        return true;

    }


    /** Supprime l'association entre le Tag et le Mark. 
     * @param      string      $tag_id
     * @return     mixed       true ou Blogmarks_Exception en cas d'erreur
     */
    function remTagAssoc( $tag_id ) {
        $assoc_def =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        
        $assoc_def->bm_Marks_id = $this->id;
        $assoc_def->bm_Tags_id  = $tag_id;

        if ( $assoc_def->find(true) ) {
			$this->debug( "Suppression de l' association [$this->id / $tag_id].", __FUNCTION__, 1 );
            $assoc_def->delete();
            return true;
        }

        else return Blogmarks::raiseError( "Le Tag [$tag_id] n'est pas associé au Mark [$this->id]", 404 );
    }

    /** Renvoie la valeur d'un tag.
     * @param      int         id       Identifiant du tag
     * @return     mixed       String ou Blogmarks_Exception en cas d'erreur
     */
    function getTag( $id ) {

        $tag =& Element_Factory::makeElement( 'Bm_Tags' );

        // Récupération du tag
        if ( ! $tag->get('id', $id) )
            return Blogmarks::raiseError( "Aucun tag avec l'id [$id] n'existe.", 404 );
        
        return $tag;
    }

# ------ LINKS

    /** Renvoie la liste des champs de la base de données qui correpondent à des Links
     * @return      array
     */
    function getLinksFields() { return $this->_links_fields; }


    /** Renvoie la valeur du Link lié passé en paramètre.
     * @param      string      field_name      Un nom de champs lié (renvoyé par Bm_Marks::getLinksFields())
     * @return     mixed       String ou Blogmarks_Exception en cas d'erreur
     */
    function getLink( $field ) {

        // On vérifie que le champs passé en paramètre est bien un Link lié
        if ( ! count(in_array($field, $this->_links_fields)) ) 
            return Blogmarks::raiseError( "Aucun Link lié nommé [$field].", 404 );

        $link =& Element_Factory::makeElement( 'Bm_Links' );

        // Récupération du lien
        if ( ! $link->get('id', $this->$field) )
            return Blogmarks::raiseError( "Aucun Link [$field] associé au Mark [$this->id].", 404 );
        
        return $link;
    }


# ------

    /** Renvoie la liste des champs sur lesquels il est possible d'effectuer une recherche (Blogmarks_Marker::getMarksList())
     * @return      array
     */
    function getSearchFields() { return $this->_search_fields; }

}
?>