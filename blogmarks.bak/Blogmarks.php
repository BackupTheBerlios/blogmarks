<?php
/** Déclaration de la classe Blogmarks
 * @version    $Id: Blogmarks.php,v 1.1 2004/03/05 11:40:27 mbertier Exp $
 */

# --
require_once 'Exception.php';

# --
define( BM_ERROR_CLASS, 'Blogmarks_Exception' );

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
     * @static
     */
    function raiseError( $message = null, $code = null ) {
        $error = new Blogmarks_Exception( $message, $code );
        return $error;
    }

    /** Teste si la variable passée en paramètre est une erreur.
     * @param    mixed    $var    La variable à tester.
     * @returns   bool
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

