<?php
/**
 * Table Definition for bm_Links
 */
require_once 'Blogmarks/Element.php';


/** Lien.
 * @package     Elements
 */
class Element_Bm_Links extends Blogmarks_Element 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'bm_Links';                        // table name
    var $id;                              // int(11)  not_null primary_key auto_increment
    var $lang;                            // string(255)  
    var $type;                            // string(255)  
    var $href;                            // string(255)  multiple_key
    var $title;                           // blob(65535)  blob
    var $charset;                         // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Element_Bm_Links',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


    /** Récupèration d'informations à propos de la ressource désignée par $this->href.
     *
     * La récupération des infos se fait en cascade :
     *   1. the encoding given in the charset parameter of the Content-Type HTTP header, or
     *   2. the encoding given in the encoding attribute of the XML declaration within the document, or
     *   3. utf-8.
     *
     * @see      http://diveintomark.org/archives/2004/02/13/xml-media-types
     * @todo     Terminer la recherche d'infos
     */
    function fetchUrlInfo() {

        // Envoi d'une requete vers l'URL
        require_once 'HTTP/Request.php';
        $r = new HTTP_Request( $this->href );
        $r->sendRequest();

        // Récupération des informations contenues dans l'entête de la réponse.
        $url_info = $r->getResponseHeader();

        // --- Détermination du Content-Type et du Charset de la page --- //
        // 1. D'apres un entete http
        if ( isset($url_info['content-type']) ) {

            // eg. text/html; charset=utf8
            $pattern = '/^(\w+\/\w+)(;?.\w+=(.*))?/';
            if ( preg_match( $pattern, $url_info['content-type'], $matches ) ) {
                $content_type = isset($matches[1]) ? $matches[1] : null;
                $charset = isset($matches[3]) ? $matches[3] : null;
            }

        }

        // 2. D'apres la déclaration XML du document
        

        // 3. Par défaut : utf8
        else {
            $charset = 'utf8';
        }
        // Mise à jour des propriétés de l'objet en fonction des informations obtenues
        $this->type = $content_type;
        $this->charset = $charset;

        return true;

        
    }
}
?>