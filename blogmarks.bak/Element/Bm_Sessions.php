<?php
/**
 * Table Definition for bm_Sessions
 */
require_once 'blogmarks/Element.php';

class Element_Bm_Sessions extends Blogmarks_Element 
{

    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Sessions';                     // table name
    var $id;                              // string(255)  not_null
    var $expire;                          // timestamp(14)  not_null unsigned zerofill timestamp

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Sessions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>