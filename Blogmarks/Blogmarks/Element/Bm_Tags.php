<?php
/**
 * Table Definition for bm_Tags
 */
require_once 'Blogmarks/Element.php';

/** Tag
 * @package     Elements
 */
class Element_Bm_Tags extends Blogmarks_Element 
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	var $__table = 'bm_tags';                         // table name
	var $id;                              // int(11)  not_null primary_key auto_increment
	var $title;                           // string(255)  not_null
	var $author;                          // string(255)  not_null multiple_key
	var $issued;                          // datetime(19)  
	var $modified;                        // datetime(19)  not_null
	var $summary;                         // blob(65535)  blob
	var $lang;                            // string(10)  
	var $ico;                             // string(255)  


	/* ZE2 compatibility trick*/
	function __clone() { return $this;}

	/* Static get */
	function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_tags',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE

    /** Permet de savoir si le Tag est associé à un Mark donné. 
     * @param      int       $mark_id
     * @return     bool
     *
     * @uses       Element_Factory
     */
    function isAssociatedToMark( $mark_id ) {
        require_once 'Blogmarks/Element/Factory.php';

        $assoc_def = Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assoc_def->bm_Marks_id = $mark_id;
        $assoc_def->bm_Tags_id = $this->id;

        if ( $assoc_def->find() ) return true;
        else return false;
        
    }


    /** Permet de savoir si le Tag est public.
     * Comportement :
     *   - author != NULL -> privé
	 *   - author = NULL  -> public
     *
     * @return       bool
     */
    function isPublic() {
        return ( $this->author == null );
    }

    /** Permet de savoir si un Tag est privé.
     * @return      bool
     */
    function isPrivate() { return ! $this->isPublic(); }


    /** Surcharge de Blogmarks_Element::populateProps(). */
    function populateProps( $props = array() ) {

        $this->id          = isset($props['id'])            ? $props['id']          : null;
		$this->title       = isset($props['title'])         ? $props['title']       : null;
        $this->author      = isset($props['author'])        ? $props['author']      : null;
		$this->issued      = isset($props['issued'])        ? $props['issued']      : null;
        $this->modified    = isset($props['modified'])      ? $props['modified']    : null;
        $this->summary     = isset($props['summary'])       ? $props['summary']     : null;
        $this->lang        = isset($props['lang'])          ? $props['lang']        : null;
        $this->ico         = isset($props['ico'])           ? $props['ico']         : null;
    }
}
?>