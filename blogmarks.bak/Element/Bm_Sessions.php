<?php
/** Table Definition for bm_Sessions
 * @version  $Id: Bm_Sessions.php,v 1.3 2004/03/17 15:07:15 mbertier Exp $
 */

/***/
require_once 'Blogmarks/Element.php';

/** Couche d'acc�s aux donn�es de session.
 * @package    Blogmarks
 * @subpackage Auth
 */
class Element_Bm_Sessions extends Blogmarks_Element 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Sessions';                     // table name
    var $id;                              // string(255)  not_null primary_key
    var $user_id;                         // int(11)  not_null
    var $expire;                          // timestamp(14)  not_null unsigned zerofill timestamp
    var $data;                            // blob(16777215)  not_null blob

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Sessions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>