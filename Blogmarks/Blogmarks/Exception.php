<?php
/** D�claration de la classe Blogmarks_Exception
 * @version    $Id: Exception.php,v 1.2 2004/06/02 13:33:50 mbertier Exp $
 */

/** Objet d�di� � la remont�e d'erreurs
 * @package    Blogmarks
 */
class Blogmarks_Exception {
    
    /** Code d'erreur.
     * @var int */
    var $code;

    /** Message d�crivant l'erreur.
     * @var string */
    var $message;

# ------------------------ #
# -- METHODES PUBLIQUES -- #
# ------------------------ #

    /** Constructeur. */
    function Blogmarks_Exception ( $msg, $code ) {
        $this->setMessage( $msg );
        $this->setCode( $code );
    }

# --------------- #
# -- GET / SET -- #
# --------------- #

    /** D�finition du code d'erreur
     * @param    int    $code    Le code d'erreur
     */
    function setCode( $code ) {
        $this->code = $code;
    }

    /** D�finition du message d'erreur.
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
     * @return   string
     */
    function getMessage() {
        return $this->message;
    }
   
# ------------------------ #
# -- METHODES PRIVEES   -- #
# ------------------------ #
    
    
}
?>