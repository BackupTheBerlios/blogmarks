<?php
/** Déclaration de la classe Blogmarks
 * @version    $Id: Blogmarks.php,v 1.3 2004/06/25 12:14:26 benfle Exp $
 */

# --
require_once 'Blogmarks/Exception.php';

/** Nom de la classe utilisée pour la génération d'erreur. */
define( 'BM_ERROR_CLASS', 'Blogmarks_Exception' );

/** Description de la classe.
 * @package    Blogmarks
 * @uses       Blogmarks_Exception
 */
class Blogmarks {

    
# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function Blogmarks () {}
    
    /** Génère des objets d'erreur.
     * @param     string    $message    Message décrivant l'erreur.
     * @param     int       $code       Code correspondant à l'erreur.
     * @return   object    BM_ERROR_CLASS
     * @static
     */
    function raiseError( $message = null, $code = null ) {
        $error = new Blogmarks_Exception( $message, $code );
        return $error;
    }

    /** Teste si la variable passée en paramètre est une erreur.
     * @param    mixed    $var    La variable à tester.
     * @return   bool
     * @static
     */
    function isError( $var ) {
        if ( is_a($var, BM_ERROR_CLASS) ) { return true; }
        else { return false; }
    }
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>