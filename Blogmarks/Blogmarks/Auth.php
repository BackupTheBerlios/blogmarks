<?php
/** Déclaration de la classe Blogmarks_Auth
 * @version    $Id: Auth.php,v 1.1 2004/03/31 14:57:45 mbertier Exp $
 */

require_once 'Blogmarks/Blogmarks.php';
require_once 'Blogmarks/Element/Factory.php';


/** Classe dédiée à la gestion des droits au sein de Blogmarks.
 * @package    Blogmarks
 * @subpackage Auth
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

        // Redéfinition des handlers de session
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
     * @param      string      $cli_digest   Le digest du client, qui sera comparé au digest server.
     * @param      string      $nonce        Chaîne aléatoire utilisée par le client pour créer le digest.
     * @param      string      $timestamp    Utilisé par le client pour générer le digest.
     * @param      bool        $make_session Créer une session (défaut: false)
     *
     * @return     mixed       True en cas de validation  ou Blogmarks_Exception en cas d'erreur.
     */
    function authenticate( $login, $cli_digest, $nonce, $timestamp, $make_session = false ) {

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
        if ( $digest !== $cli_digest ) {
            return Blogmarks::raiseError( 'Wrong credentials.', 401 );
        }

        // Création de session à la demande
        if ( $make_session ) {

            // Démarrage de la session
            session_start();
            
            // On lie la session à l'utilisateur
            $_SESSION['_BM']['user_id'] = $user->id;
        } 

        // On se contente de stocker l'id utilisateur dans une propriété
        else $this->_connectedUserId = $user->id;

# --
        /* Apparemment le update est effectué avant l'insertion de la session
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
     * Cette information peut etre stockée soit en session, soit dans les propriétés de l'objet. 
     * Si les deux locations sont renseignées, on donne priorité aux informations de session.
     *
     * @return     object Element_Bm_Users
     */
    function getConnectedUser() {

        // Recherche de l'identifiant de l'utilisateur connecté
        $uid = ( isset($_SESSION['_BM']['user_id']) ? $_SESSION['_BM']['user_id'] : null );
        if ( ! $uid ) 
            $uid = ( isset($this->_connectedUserId) ? $this->_connectedUserId : null ); 

           // Si aucun utilisateur n'est connecté
        if ( ! $uid ) return Blogmarks::raiseError( 'Aucun utilisateur connecté', 404 );

        // Renvoi de l'objet correspondant à l'utilisateur
        $user =& Element_Factory::makeElement( 'Bm_Users' );
        if ( $user->get($uid) ) return $user;
        else return Blogmarks::raiseError( "Aucun utilisateur connecté.", 404 );
       
    }

# ----------------------- #
# -- METHODES PRIVEES  -- #
# ----------------------- #


# --- CRYPT

    /***/
    function _hex2bin( $data ) {
        $len = strlen( $data );
        return pack( "H" . $len, $data );
    }


    /** Génération d'un digest de password.
     * @param      string      $pwd         Le mot de passe
     * @param      string      $nonce       Une chaine aléatoire
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

    
    /** Lecture des données de la session */
    function _sessRead( $sess_id ) {
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );
        $sess->get( $sess_id );

        return $sess->data;
    }

    
    /** Ecriture de données de session (+ création de session) */
    function _sessWrite( $sess_id, $sess_data ) {
        $sess =& Element_Factory::makeElement( 'Bm_Sessions' );
        
        // Création d'une session
        if ( ! $sess->get( $sess_id ) ) {
            $q = "INSERT INTO bm_Sessions VALUES ('$sess_id', '', '', '$sess_data');";
            $sess->query( $q );
        }

        // Mise à jour d'une session existante
        else {
            // On n'update que si les données ont changé.
            if ( $sess->data !== $sess_data ) {
                $sess->data = $sess_data;
                $sess->update();
            }
        }

        return true;
        
    }

    
    /** Destruction de la session */
    function _sessDestroy( $sess_id ) {
        $sess =& Element_Factory::makeElement( 'bm_Sessions' );
        if ( $sess->get( $sess_id ) ) {
            $sess->delete();
            return true;
        }
        
        return false;
        
    }


    /** Garbage collection */
    function _sessGC() { return true; }
    
}
?>

