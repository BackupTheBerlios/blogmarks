<?php
/**
 * Table Definition for bm_Tags
 */
require_once 'blogmarks/Element.php';

/** Tag
 * @package     Elements
 */
class Element_Bm_Tags extends Blogmarks_Element 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Tags';                         // table name
    var $id;                              // int(11)  not_null primary_key
    var $title;                           // string(255)  not_null
    var $subTagOf;                        // int(11)  not_null
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
}
?>