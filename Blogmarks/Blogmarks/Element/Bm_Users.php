<?php
/**
 * Table Definition for bm_Users
 */
require_once 'Blogmarks/Element.php';

/** Utilisateur.
 * @package     Elements
 */
class Element_Bm_Users extends Blogmarks_Element 
{

    /** Champs dont on peut renvoyer les valeurs sans danger.
     * @var array */
    var $_info_fields = array( 'login', 'email', 'permlevel' );


    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Users';                        // table name
    var $id;                              // int(11)  not_null primary_key auto_increment
    var $login;                           // string(255)  
    var $pwd;                             // string(255)  
    var $email;                           // string(255)  
    var $permlevel;                       // string(1)  not_null enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


# ------ AUTH

    /** Permet de savoir si un utilisateur est administrateur. 
     * @return     bool
     */
    function isAdmin() { return ( $this->permlevel == 2 ) ? true : false; }


    /** Permet de savoir si un utilisateur est authentifié. 
     * @return     bool
     */
    function isAuthenticated() { return ( $this->permlevel > 0 ) ? true : false; }


    /** Permet de savoir si l'élément passé en paramètre appartient à l'utilisateur.
     * La méthode peut traiter Tags et Marks. On décide de l'appartenance de l'objet en se
     * basant sur la valeur de la propriété bm_Users_id de l'objet.
     * 
     * @param      object Bm_Element
     * @return     bool
     */
    function owns( &$element ) { return $this->id == $element->bm_Users_id; }        


    /** Renvoie la liste des champs dont on peut renvoyer les valeurs sans danger.
     * @return      array
     */
    function getInfoFields() { return $this->_info_fields; }

}
?>