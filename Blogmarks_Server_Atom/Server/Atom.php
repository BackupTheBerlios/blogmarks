<?php

/** Déclaration de la classe Server_Atom
 * @version    $Id: Atom.php,v 1.2 2004/05/19 13:39:46 benfle Exp $
 */

require_once 'PEAR.php';
require_once 'Atom/Filter.php';
require_once 'Atom/Controller.php';
require_once 'Atom/Renderer.php';

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

    $root = '/blogmarks.atom';

    // On construit le tableau d'arguments pour les filtres

    $args            = array();

    $uri             = $_SERVER['REQUEST_URI'];

    $uri             = ereg_replace($root, '', $uri);

    $args['uri']     = $uri;

    $args['method']  = $_SERVER['REQUEST_METHOD'];

    $args['content'] = $_GLOBALS['HTTP_RAW_POST_DATA'];

    $args['marker']  =& new BlogMarks_Marker;

    // On filtre la requête

    $filter = new FilterChainRoot ( array ( 
					   new ContextBuilderFilter(), 
					   new AuthenticateFilter() 
					   ) 
				    );

    $ret = $filter->execute(&$args);

    if ( BlogMarks::isError ($ret) ) {
      
      // erreur de filtre
      echo "Filter Error : " . $ret->getMessage() . "\n";

      exit (1);

    }

    // **** DEBUG
    echo "objet : ".$args['object']."<br/>";
    echo "method : ".$args['method']."<br/>";
    echo "tag : ".$args['tag']."<br/>";
    echo "user : ".$args['user']."<br/>";
    echo "id : ".$args['id']."<br/>";
    // **********

    // On construit le controlleur selon le type d'objet de la requête

    $ctrlerFactory = new ControllerFactory();

    $ctrler        = $ctrlerFactory->createController($args['object']);

    if ( BlogMarks::isError ($ctrler) ) {

      // GERER LA REPONSE HTTP

      echo "ControllerFactory error : " . $ctrler->getMessage() . "\n";

      exit(1);
    }
   
    // On lance le controlleur pour l'objet de la requête

    $response = $ctrler->execute($args);
   
    if ( BlogMarks::isError($response) ) {
      
      // GERER LA REPONSE HTTP
      echo "Controller error : " . $response->getMessage() . "\n";

      exit (1);
    }

    // On applique le renderer atom a la reponse et on la renvoit

    $rendererFactory = new rendererFactory();

    $renderer =& $rendererFactory->createRenderer($args['object']);

    $response->accept( $renderer );

    // GERER LA REPONSE HTTP

    echo "HTTP/1.1 200 Ok\n";
    
    echo $renderer->render();

  }

}
?>