<?php
/**
 * Table Definition for bm_Marks
 */
require_once 'blogmarks/Element.php';

class Element_Bm_Marks extends Blogmarks_Element 
{

    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Marks';                        // table name
    var $id;                              // int(11)  not_null primary_key auto_increment
    var $bm_Links_id;                     // int(11)  not_null multiple_key
    var $bm_Users_id;                     // int(11)  not_null multiple_key
    var $title;                           // string(255)  
    var $summary;                         // blob(65535)  blob
    var $screenshot;                      // string(255)  
    var $issued;                          // datetime(19)  
    var $created;                         // datetime(19)  
    var $modified;                        // datetime(19)  not_null
    var $lang;                            // string(255)  
    var $via;                             // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Marks',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>