<?php
/** D�claration de la classe Blogmarks_Auth
 * @version    $Id: Auth.php,v 1.2 2004/03/12 16:49:30 mbertier Exp $
 */

require_once 'blogmarks/Blogmarks.php';
require_once 'blogmarks/Element/Factory.php';


/** Classe d�di�e � la gestion des droits au sein de Blogmarks.
 * @package    Blogmarks
 * @todo       Cryptage des mots de passe dans la base
 */
class Blogmarks_Auth {

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
     *
     * @return     mixed       True en cas de validation  ou Blogmarks_Exception en cas d'erreur.
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
        if ( $digest !== $cli_digest ) {
            return Blogmarks::raiseError( 'Wrong credentials.', 401 );
        }

        // D�marrage de la session
        session_start();

        // On lie la session � l'utilisateur
        $_SESSION['_BM']['user_id'] = $user->id;

# --
        /* Apparemment le update est effectu� avant l'insertion de la session
         * dans la base. (alors que normalement c'est session_start qui fait cela.)
         **
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );
        $sess->get( session_id() );
        $sess->user_id = $user->id;
        $sess->update();  */
# --

        return true;
        
    }
    
    /** Renvoie l'utilisateur en cours. 
     * @return     object Element_Bm_Users
    */
    function getConnectedUser() {
        
        // Si aucun utilisateur n'est connect�
        if ( ! isset($_SESSION) ) return false;

        // R�cup�ration de l'uid de l'utilisateur connect�
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );
        $uid = $_SESSION['_BM']['user_id'];

        // Renvoi de l'objet correspondant � l'utilisateur
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        if ( $user->get($uid) ) return $user;
        else return Blogmarks::raiseError( "Aucun utilisateur connect�.", 404 );
       
    }

# ----------------------- #
# -- METHODES PRIVEES  -- #
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
        
        // Cr�ation d'une session
        if ( ! $sess->get( $sess_id ) ) {
            $q = "INSERT INTO bm_Sessions VALUES ('$sess_id', '', '', '$sess_data');";
            $sess->query( $q );
        }

        // Mise � jour d'une session existante
        else {
            // On n'update que si les donn�es ont chang�.
            if ( $sess->data !== $sess_data ) {
                $sess->data = $sess_data;
                $sess->update();
            }
        }
        
    }

    
    /** Destruction de la session */
    function _sessDestroy( $sess_id ) {
        $sess =& Element_Factory::makeElement( 'bm_Sessions' );
        $sess->get( $sess_id );
        $sess->delete();
    }


    /** Garbage collection */
    function _sessGC() { return true; }
    
}
?>

