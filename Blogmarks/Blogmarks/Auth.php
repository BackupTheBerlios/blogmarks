<?php
/** D�claration de la classe Blogmarks_Auth
 * @version    $Id: Auth.php,v 1.8 2004/06/01 15:07:16 mbertier Exp $
 */

require_once 'Blogmarks.php';
require_once 'Blogmarks/Element/Factory.php';


/** Classe d�di�e � la gestion des droits au sein de Blogmarks.
 * @package    Blogmarks
 * @subpackage Auth
 * @uses       Blogmarks
 * @uses       Element_Factory
 */
class Blogmarks_Auth {

    /** Identifiant de l'utilisateur en cours.
     * @var string */
    var $_connectedUserId = null;


# ------------------------ #
# -- METHODES PUBLIQUES -- #
# ------------------------ #

    /** Constructeur. */
    function Blogmarks_Auth () {

        // Red�finition des handlers de session
        session_set_save_handler( array( & $this, '_sessOpen'    ), 
                                  array( & $this, '_sessClose'   ), 
                                  array( & $this, '_sessRead'    ), 
                                  array( & $this, '_sessWrite'   ), 
                                  array( & $this, '_sessDestroy' ), 
                                  array( & $this, '_sessGC'      ) );
    }
    
    
    /** Authentification d'un utilisateur.
     * 
     * @param      string      $login        Le login de l'utilisateur.
     * @param      string      $cli_digest   Le digest du client, qui sera compar� au digest server.
     * @param      string      $nonce        Cha�ne al�atoire utilis�e par le client pour cr�er le digest.
     * @param      string      $timestamp    Utilis� par le client pour g�n�rer le digest.
     * @param      bool        $make_session Cr�er une session (d�faut: false)
     *
     * @return     mixed       True en cas de validation  ou Blogmarks_Exception en cas d'erreur.
     */
    function authenticate( $login, $cli_digest, $nonce, $timestamp, $make_session = false ) {
        // Recherche de l'utilisateur correpondant � $login
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        $user->login = $login;

        // Si l'utilisateur n'existe pas -> erreur 404
        if ( ! $user->find() ) return Blogmarks::raiseError( "L'utilisateur [$login] est inconnu.", 404 );

        // R�cup�ration du mot de passe de l'utilisateur
        $res = $user->fetch();
        if ( DB::isError($res) ) return Blogmarks::raiseError( $res->getMessage(), $res->getCode() );

        $pwd = $user->pwd;

        // Constitution d'un digest
        $digest = $this->_makeDigest( $pwd, $nonce, $timestamp );

        // Si le mot de passe fourni est incorrect -> erreur 401
        if ( $digest !== $cli_digest ) {
            return Blogmarks::raiseError( 'Wrong credentials.', 401 );
        }

        // Cr�ation de session � la demande
        if ( $make_session ) {
            // On lie la session � l'utilisateur
            $_SESSION['_BM']['user_id'] = $user->id;
        } 

        // On se contente de stocker l'id utilisateur dans l'objet
        else $this->_connectedUserId = $user->id;

        return true;
        
    }
    

    /** Renvoie l'utilisateur en cours. 
     * Cette information peut etre stock�e soit en session, soit dans les propri�t�s de l'objet. 
     * Si les deux locations sont renseign�es, on donne priorit� aux informations de session.
     *
     * @return     object Element_Bm_Users
     */
    function getConnectedUser() {

        // Recherche de l'identifiant de l'utilisateur connect�
        $uid = ( isset($_SESSION['_BM']['user_id']) ? $_SESSION['_BM']['user_id'] : null );
        if ( ! $uid ) 
            $uid = ( isset($this->_connectedUserId) ? $this->_connectedUserId : null ); 

        // Si aucun utilisateur n'est connect�
        if ( ! $uid ) return Blogmarks::raiseError( 'Aucun utilisateur connect�', 404 );

        // Renvoi de l'objet correspondant � l'utilisateur
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        if ( $user->get($uid) ) return $user;
        else return Blogmarks::raiseError( "Aucun utilisateur connect�.", 404 );
       
    }


    /** Suppression de la session de l'utilisateur. 
     * @return      bool      */
    function disconnectUser() { return session_destroy(); }


# ----------------------- #
# -- METHODES PRIVEES  -- #
# ----------------------- #


# --- CRYPT

    /***/
    function _hex2bin( $data ) {
        $len = strlen( $data );
        return pack( "H" . $len, $data );
    }


    /** G�n�ration d'un digest de password.
     * @param      string      $pwd         Le mot de passe
     * @param      string      $nonce       Une chaine al�atoire
     * @param      string      $timestamp   Un timestamp (sic)
     * @return     string      Le digest
     */
    function _makeDigest( $pwd, $nonce, $timestamp  ) {
        $txt = sha1( $nonce . $timestamp . $pwd );
        $digest = base64_encode( $this->_hex2bin($txt) );

        return $digest;
    }


# --- SESSION
    
    /** Ouverture de la session */
    function _sessOpen() { return true; }
    
    
    /** Fermeture de la session */
    function _sessClose() { return true; }

    
    /** Lecture des donn�es de la session */
    function _sessRead( $sess_id ) {
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );
        $sess->get( $sess_id );

        return $sess->data;
    }

    
    /** Ecriture de donn�es de session (+ cr�ation de session) */
    function _sessWrite( $sess_id, $sess_data ) {
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );

        $sess_data = addslashes( $sess_data );
        $time = time();

        // Cr�ation d'une session
        if ( ! $sess->get($sess_id) ) {

            $sess->id          = $sess_id;
            $sess->data        = $sess_data;
            $sess->last_update = $time;

            $sess->insert();
        }

        // Mise � jour d'une session existante
        else {
            // On n'update que si les donn�es ont chang�.
            if ( $sess->data !== $sess_data ) {

                $sess->data        = $sess_data;
                $sess->last_update = $time;

                $sess->update();
            }
        }

        return true;
        
    }

    
    /** Destruction de la session */
    function _sessDestroy( $sess_id ) {
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );
        if ( $sess->get( $sess_id ) ) {
            $sess->delete();
            return true;
        }
        
        return false;
        
    }


    /** Garbage collection */
    function _sessGC( $max_lifetime ) { return true; }
    
}
?>