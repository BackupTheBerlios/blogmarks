<?php
/**
 * Table Definition for bm_Users
 */
require_once 'blogmarks/Element.php';

class Element_Bm_Users extends Blogmarks_Element 
{

    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Users';                        // table name
    var $id;                              // int(11)  not_null primary_key auto_increment
    var $login;                           // string(255)  
    var $pwd;                             // string(255)  
    var $email;                           // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>