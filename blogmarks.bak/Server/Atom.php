<?php
/** D�claration de la classe Server_Atom
 * @version    $Id: Atom.php,v 1.2 2004/03/10 16:40:52 benfle Exp $
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

    // On filtre la requ�te
    $args   = array();
    $filter = new FilterChainRoot(array(new ContextBuilderFilter(), 
					new AuthenticateFilter()));
    $filter->execute(&$args);

    // On construit le controlleur selon le type d'objet de la requ�te
    $ctrlerFactory = new controllerFactory();
    $ctrler        = $ctrlerFactory->createController($args['object']);

    // On lance le controlleur pour l'objet de la requ�te
    $response = $ctrler->execute($args);
    
    // On applique le renderer atom a la reponse et on la renvoit
    $renderer = new atom_renderer();
    return $renderer->render($response);
  }
}

/* code principal du serveur */
$server = new Server_Atom('http://localhost/blogmarks/atom/server');
$server->run();
?>