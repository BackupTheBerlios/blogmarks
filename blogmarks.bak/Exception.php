<?php
/** Déclaration de la classe Blogmarks_Exception
 * @version    $Id: Exception.php,v 1.2 2004/03/05 11:40:27 mbertier Exp $
 */

/** Objet dédié à la remontée d'erreurs
 * @package    Blogmarks
 */
class Blogmarks_Exception {
    
    /** Code d'erreur.
     * @var int */
    var $code;

    /** Message décrivant l'erreur.
     * @var string */
    var $message;

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function Blogmarks_Exception ( $msg, $code ) {
        $this->setMessage( $msg );
        $this->setCode( $code );
    }

# --------------- #
# -- GET / SET -- #
# --------------- #

    /** Définition du code d'erreur
     * @param    int    $code    Le code d'erreur
     */
    function setCode( $code ) {
        $this->code = $code;
    }

    /** Définition du message d'erreur.
     * @param    string     Le message
     */
    function setMessage( $msg ) {
        $this->message = $msg;
    }

    /** Retourne le code d'erreur.
     * @return  int
     */
    function getCode() {
        return $this->code;
    }
    
    /** Retourne le message d'erreur.
     * @returns   string
     */
    function getMessage() {
        return $this->message;
    }
   
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>

