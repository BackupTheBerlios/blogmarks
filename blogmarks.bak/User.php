<?php
/** Déclaration de la classe BlogMarks_User
 * @version    $Id: User.php,v 1.1 2004/03/01 13:19:45 benfle Exp $
 */

include_once 'DB/DataObject.php';

/** Classe de base pour les utilisateurs BlogMarks.
 * @package    BlogMarks
 */
class BlogMarks_User extends DB_DataObject {

    /**  Le nom (login) de l'utilisateur.
     * @var string */
    var $href = '';

    /**  L'email de l'utilisateur.
     * @var string */
    var $email = '';

    /** Le mot de passe de l'utilisateur (md5).
     * @var string */
    var $pwd = '';   

# ----------------------- #
# -- METHODES PUBLIQUES --#
# ----------------------- #

    /** Constructeur. */
    function BlogMarks_User () {}
    
    
# ----------------------- #
# -- METHODES PRIVEES   --#
# ----------------------- #
    
    
}
?>