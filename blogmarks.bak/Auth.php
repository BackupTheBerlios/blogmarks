<?php
/** Déclaration de la classe Blogmarks_Auth
 * @version    $Id: Auth.php,v 1.1 2004/03/09 16:58:10 mbertier Exp $
 */

require_once 'blogmarks/Blogmarks.php';
require_once 'blogmarks/Element/Factory.php';


/** Classe dédiée à la gestion des droits au sein de Blogmarks.
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
     * @param      string      $cli_digest   Le digest du client, qui sera comparé au digest server.
     * @param      string      $nonce        Chaîne aléatoire utilisée par le client pour créer le digest.
     * @param      string      $timestamp    Utilisé par le client pour générer le digest.
     *
     * @return     mixed       Une chaîne identifiant la session de l'utilisateur ou Blogmarks_Exception en cas d'erreur.
     */
    function authenticate( $login, $cli_digest, $nonce, $timestamp ) {
        
        // Recherche de l'utilisateur correpondant à $login
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        $user->login = $login;

        // Si l'utilisateur n'existe pas -> erreur 404
        if ( ! $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$login] est inconnu.", 404 );

        // Récupération du mot de passe de l'utilisateur
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


    /** Génération d'un digest de password.
     * @param      string      $pwd         Le mot de passe
     * @param      string      $nonce       Une chaine aléatoire
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

