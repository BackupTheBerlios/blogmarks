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
    function isAdmin() { return ( $this->permlevel == 2 ) ? 'true' : 'false'; }


    /** Permet de savoir si un utilisateur est authentifié. 
     * @return     bool
     */
    function isAuthenticated() { return ( $this->permlevel > 0 ) ? 'true' : 'false'; }


    /** Permet de savoir si l'élément passé en paramètre appartient à l'utilisateur.
     * La méthode peut traiter Tags et Marks. On décide de l'appartenance de l'objet en se
     * basant sur la valeur de la propriété bm_Users_id de l'objet.
     * 
     * @param      object Bm_Element
     * @return     bool
     */
    function owns( &$element ) { return ( $this->id == $element->bm_Users_id ) ? true : false; }        


# ------ MARKS

    /** Renvoie la liste des Marks de l'utilisateur. 
     * @param      array       $include_tags    Ids de Tags, seuls les Marks correspondants aux Tags listés ici seront sélectionnés
     * @param      array       $exclude_tags    Ids de Tags, les Marks correspondants à ces Tags ne seront pas sélectionnés
     * @param      bool        $private         Si true, recherche aussi parmi les Tags privés
     * @return     mixed       DB_DataObject_Result ou Blogmarks_Exception en cas d'erreur
     */
    function getMarksList( $include_tags = null, $exclude_tags = null, $private = false) {

        $now = date( "Ymd His" );

        // ---- Sélection simple
        if ( ! $include_tags && ! $exclude_tags ) {
            $mark   =& Element_Factory::makeElement( 'Bm_Marks' );
            $mark->bm_Users_id = $this->id;
            
            // Par défaut, on ne récupère que les Tags publics
            if ( ! $private ) { 
                $mark->whereAdd( "issued != 0 " );
                $mark->whereAdd( "issued < '$now'" );
            }

            return ( $mark->find() > 0 ? $mark : Blogmarks::raiseError( "Aucun Mark disponible pour l'utilisateur [$this->login]." ) );
        }
        
        // ---- Sélection conditionnelle
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );

        // INNER JOIN entre bm_Marks_has_Tags et Bm_Marks
        $assocs->joinAdd( $mark );

        if ( is_array($exclude_tags) && count($exclude_tags) ) {
            // -- Constitution de la liste des Marks à exclure
            foreach ( $exclude_tags as $tag_id ) $assocs->whereAdd( "bm_Tags_id = '$tag_id'", 'OR' );
            $assocs->find();
            while ( $assocs->fetch() ) { 
                $excluded_marks[] = $assocs->bm_Marks_id;
                
                // Dédoublonnage des résultats
                $excluded_marks = array_unique( $excluded_marks );
            }
        }


        // Reset de $assocs
        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->joinAdd( $mark );


        // -- Marks à inclure
        // Selon un Tag les décrivant
        if ( is_array($include_tags) && count($include_tags) ) {
            foreach ( $include_tags as $tag_id ) $assocs->whereAdd( "bm_Tags_id = '$tag_id'", 'OR' );
        }

        // On ne sélectionne pas les Marks dont le Tag est exclu
        if ( is_array($excluded_marks) && count($excluded_marks) ) {
            foreach ( $excluded_marks as $mark_id ) $assocs->whereAdd( "bm_Marks_id != '$mark_id'", 'AND' );
        }
        
        // Par défaut, on ne sélectionne que les Marks publics
            // Par défaut, on ne récupère que les Tags publics
            if ( ! $private ) { 
                $mark->whereAdd( "issued != 0 ",  'AND' );
                $mark->whereAdd( "issued < '$now'", 'AND' );
            }        

        return ( $assocs->find() > 0 ? $assocs : Blogmarks::raiseError( 'Aucun Mark disponible avec ces critères.' ) );
    }
    

}
?>