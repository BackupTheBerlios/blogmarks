<?php
/** Déclaration des différents controlleurs et de leur Factory.
 * @version    $Id: Controller.php,v 1.1 2004/03/10 16:59:15 benfle Exp $
 */


/** Classe parente des différents controlleurs.
 * @package    Atom
 */
class Controller {
  
  function Controller () {}
  
  function execute () {}
}

/** Factory de controlleurs.
 * @package    Atom
 */
class ControllerFactory {

  function createController($object)
  {
    $controller = null;

    switch($object){
    case ('Mark'):
      $controller = new MarkController();
      break;
    case ('Tag'):
      $controller = new TagController();
      break;
    case ('MarksList'):
      $controller = new MarksListController();
      break;
    case ('TagsList'):
      $controller = new TagsListController();
      break;
    }
    
    return $controller;
  }
}

####################################
# Les différents controlleurs      #
####################################

/** Controlleur sur un Mark.
 * @package    Atom
 */
class MarkController {

  function execute () {}

}

/** Controlleur sur un Tag.
 * @package    Atom
 */
class TagController {

  function execute () {}

}

/** Controlleur sur une liste de Marks.
 * @package    Atom
 */
class MarksListController {

  function execute () {}

}

/** Controlleur sur une liste de Tags.
 * @package    Atom
 */
class TagsListController {

  function execute () {}

}