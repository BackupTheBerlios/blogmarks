<?php
/** Déclaration de la classe Server_Atom
 * @version    $Id: Atom.php,v 1.4 2004/03/12 10:38:42 benfle Exp $
 */

require_once 'PEAR.php';

/** Classe du serveur Atom de BlogMarks.
 * @package    Servers
 * @subpackage Atom
 */
class Server_Atom {

  /** Constructeur. */
  function Server_Atom () {
  }

  /** Lance le serveur */
  function run () {

    // On filtre la requête
    $args   = array();
    $filter = new FilterChainRoot(array(new ContextBuilderFilter(), 
					new AuthenticateFilter()));
    if ( BlogMarks_isError ( $filter->execute(&$args) ) ) {
      
      // erreur de filtre
      return;
    }

    // On construit le controlleur selon le type d'objet de la requête
    $ctrlerFactory = new ControllerFactory();
    $ctrler        = $ctrlerFactory->createController($args['object']);

    // On lance le controlleur pour l'objet de la requête
    $response = $ctrler->execute($args);
    if ( BlogMarks_isError($response) ) {
      
      // erreur du controlleur de requête
      return;
    }

    // On applique le renderer atom a la reponse et on la renvoit
    $rendererFactory = new rendererFactory();
    $renderer = $rendererFactory->createRenderer($args['object']);
    $response->accept($renderer);
    return $renderer->render();
  }
}

/* code principal du serveur */
$server = new Server_Atom('http://localhost/blogmarks/atom/server');
$server->run();
?>