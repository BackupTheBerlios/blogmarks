<?php
/**
 * Table Definition for bm_Marks
 */
require_once 'Blogmarks/Element.php';

/** Mark.
 * @package     Elements
 */
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
    var $via;                             // int(11)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Marks',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


# ------ AUTH
    
    /** Permet de savoir si le Mark est public.
     * Comportement :
     *   - issued < date(now)   -> public
     *   - issued == 0          -> private
     *   - issued > date(now)   -> private (pour publication programmée)
     *
     * @return       bool
     */
    function isPublic() {
        $now = date("Y-m-d H:i:s");
        if ( $this->issued != 0 && $this->issued < $now ) return true;
        else return false;
    }

    /** Permet de savoir si un Mark est privé.
     * @return      bool
     */
    function isPrivate() { return ! $this->isPublic(); }



# ------ TAGS
    
    /** Associe le tag au Mark.
     * @param      string      $tag_id
     * @return     mixed       true ou Blogmarks_Exception en cas d'erreur
     */
    function addTagAssoc( $tag_id ) {

        // Définition de l'association
        $assoc_def =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assoc_def->bm_Marks_id = $this->id;
        $assoc_def->bm_Tags_id  = $tag_id;

        // Insertion dans la base de données
        if ( ! $assoc_def->find() ) $assoc_def->insert();
        else return Blogmarks::raiseError( "Le Tag [$tag_id] est déjà associé au Mark [$this->id].", 500 );

        return true;

    }


    /** Supprime l'association entre le Tag et le Mark. 
     * @param      string      $tag_id
     * @return     mixed       true ou Blogmarks_Exception en cas d'erreur
     */
    function remTagAssoc( $tag_id ) {
        $assoc_def =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        
        $assoc_def->bm_Marks_id = $this->id;
        $assoc_def->bm_Tags_id  = $tag_id;

        if ( $assoc_def->find(true) ) {
            $assoc_def->delete();
            return true;
        }

        else return Blogmarks::raiseError( "Le Tag [$tag_id] n'est pas associé au Mark [$this->id]", 404 );
    }


    /** Renvoie la liste des Tags associés au Mark.
     * @return      array 
     * 
     * @todo     Devrait plutot renvoyer un itérateur, mais le retour d'array est utilisé ailleurs :
     *           <code>./Marker.php:244:            $deprec_tags = array_diff( $mark->getTags(), $tags );</code>
     */
    function getTags() {

        $arr = array();

        $assocs =& Element_Factory::makeElement( 'Bm_Marks_has_bm_Tags' );
        $assocs->bm_Marks_id = $this->id;
        $assocs->find();

        while ( $assocs->fetch() ) $arr[] = $assocs->bm_Tags_id;
        
        return $arr;
    }


    /** Renvoie le href associé au Mark. 
     * @return       mixed     Le href associé au Mark ou Blogmarks_Exception en cas d'erreur.
     */
    function getHref() {
        require_once 'Blogmarks/Element/Factory.php';

        $link =& Element_Factory::makeElement( 'Bm_Links' );
        
        if ( ! $link->get($this->bm_Links_id) ) 
            return Blogmarks::raiseError( "Aucun href n'est associé au Mark [$this->id].", 404 );

        return $link->href;
    }
}
?>