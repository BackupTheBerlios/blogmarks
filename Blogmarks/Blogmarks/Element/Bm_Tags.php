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

    var $__table = 'bm_Tags';                         // table name
    var $id;                              // string(255)  not_null primary_key
    var $subTagOf;                        // int(11)  
    var $bm_Users_id;                     // int(11)  not_null multiple_key
    var $summary;                         // blob(65535)  blob
    var $lang;                            // int(10)  unsigned
    var $status;                          // string(14)  set

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Tags',$k,$v); }

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


    /** @return bool */
    function isPublic() { return ( $this->status == 'public' ) ? true : false; }

    /** @return bool */
    function isPrivate() { return ( $this->status == 'private' ) ? true : false; }


    /** Surcharge de Blogmarks_Element::populateProps(). */
    function populateProps( $props = array() ) {

        $this->id          = isset($props['id'])            ? $props['id']          : null;
        $this->subTagOf    = isset($props['subTagOf'])      ? $props['subTagOf']    : null;
        $this->bm_Users_id = isset($props['bm_Users_id'])   ? $props['bm_Users_id'] : null;
        $this->status      = isset($props['status'])        ? $props['status']      : null;
        $this->summary     = isset($props['summary'])       ? $props['summary']     : null;
        $this->lang        = isset($props['lang'])          ? $props['lang']        : null;

    }

}
?>