<?php
/**
 * Table Definition for bm_Links
 */
require_once 'blogmarks/Element.php';

class Element_Bm_Links extends Blogmarks_Element 
{

    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Links';                        // table name
    var $id;                              // int(11)  not_null primary_key auto_increment
    var $lang;                            // string(255)  
    var $type_2;                          // string(255)  
    var $href;                            // string(255)  
    var $title;                           // blob(65535)  blob

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Links',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>