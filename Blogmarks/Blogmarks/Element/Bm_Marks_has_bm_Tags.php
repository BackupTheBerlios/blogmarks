<?php
/**
 * Table Definition for bm_Marks_has_bm_Tags
 */
require_once 'Blogmarks/Element.php';


/** Relation.
 * @package     Elements
 */
class Element_Bm_Marks_has_bm_Tags extends Blogmarks_Element 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Marks_has_bm_Tags';            // table name
    var $bm_Marks_id;                     // int(11)  not_null primary_key multiple_key
    var $bm_Tags_id;                      // string(255)  not_null primary_key

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Marks_has_bm_Tags',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

}
?>