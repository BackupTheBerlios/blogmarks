<?php
/** D�claration de la classe Blogmarks_Auth
 * @version    $Id: Auth.php,v 1.1 2004/03/09 16:58:10 mbertier Exp $
 */

require_once 'blogmarks/Blogmarks.php';
require_once 'blogmarks/Element/Factory.php';


/** Classe d�di�e � la gestion des droits au sein de Blogmarks.
 * @package    Blogmarks
 * @todo       Cryptage des mots de passe dans la base
 */
class Blogmarks_Auth {

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function Blogmarks_Auth () {}
    
    
    /** Authentification d'un utilisateur.
     * 
     * @param      string      $login        Le login de l'utilisateur.
     * @param      string      $cli_digest   Le digest du client, qui sera compar� au digest server.
     * @param      string      $nonce        Cha�ne al�atoire utilis�e par le client pour cr�er le digest.
     * @param      string      $timestamp    Utilis� par le client pour g�n�rer le digest.
     *
     * @return     mixed       Une cha�ne identifiant la session de l'utilisateur ou Blogmarks_Exception en cas d'erreur.
     */
    function authenticate( $login, $cli_digest, $nonce, $timestamp ) {
        
        // Recherche de l'utilisateur correpondant � $login
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        $user->login = $login;

        // Si l'utilisateur n'existe pas -> erreur 404
        if ( ! $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$login] est inconnu.", 404 );

        // R�cup�ration du mot de passe de l'utilisateur
        $user->fetch();
        $pwd = $user->pwd;

        // Constitution d'un digest
        $digest = $this->_makeDigest( $pwd, $nonce, $timestamp );
        
        // Si le mot de passe fourni est incorrect -> erreur 401
        if ( $digest !== $cli_digest ) return Blogmarks::raiseError( 'Wrong credentials.', 401 );


        return $digest;
        
    }
    

# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    /***/
    function _hex2bin($data) {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }


    /** G�n�ration d'un digest de password.
     * @param      string      $pwd         Le mot de passe
     * @param      string      $nonce       Une chaine al�atoire
     * @param      string      $timestamp   Un timestamp (sic)
     * @return     string      Le digest
     */
    function _makeDigest( $pwd, $nonce, $timestamp  ) {
        
        $txt = sha1($nonce.$timestamp.$pwd);
        $digest = base64_encode( $this->_hex2bin($txt) );

        return $digest;
        
    }    
}
?>

