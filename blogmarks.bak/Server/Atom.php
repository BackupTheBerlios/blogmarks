<?php
/** Déclaration de la classe Server_Atom
 * @version    $Id: Atom.php,v 1.5 2004/03/15 11:08:49 benfle Exp $
 */

require_once 'PEAR.php';
require_once '../Blogmarks.php';
require_once 'Atom/Filter.php';
require_once 'Atom/Controller.php';

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

    // racine du serveur a enregistrer qq part (pour l'instant ici)
    $root = 'http://localhost/blogmarks/servers/atom';

    // On construit le tableau d'arguments pour les filtres
    $args   = array();
    // on extrait l'URI relative
    $uri = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $uri = ereg_replace($root, '', $uri);
    $args['uri'] = $uri;
    $args['method'] = $_SERVER['REQUEST_METHOD'];
    $args['content'] = $_GLOBALS['HTTP_RAW_POST_DATA'];

    // On filtre la requête
    $filter = new FilterChainRoot(array(new ContextBuilderFilter(), 
					new AuthenticateFilter()));
    $ret = $filter->execute(&$args);
    if ( BlogMarks::isError ($ret) ) {
      
      // erreur de filtre
      echo $ret->getMessage();
      return;
    }

    // On construit le controlleur selon le type d'objet de la requête
    $ctrlerFactory = new ControllerFactory();
    $ctrler        = $ctrlerFactory->createController($args['object']);

    if ( BlogMarks::isError ($ctrler) ) {
      echo $ctrler->getMessage();
      exit(1);
    }

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