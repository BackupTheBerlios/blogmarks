<?php
/** Déclaration des différents controlleurs et de leur Factory.
 * @version    $Id: Controller.php,v 1.2 2004/03/10 17:58:54 benfle Exp $
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

  function execute ($args) {

    $marker = new BlogMarks_marker;

    switch ($args['method']) {

    case 'GET':
      // renvoit un mark
      return $marker->getMark($args['id'], $args['user'], $args['auth_str']);

    case 'PUT':
      // met a jour un mark
      $mark_array = $this->parseAtomMark($args['content']);
      return $marker->updateMark($args['id'], $args['user'],
				 $mark_array, $args['auth_str']);

    case 'DELETE':
      // supprimme un mark
      return $marker->deleteMark($args['id'], $args['user'], 
				 $args['auth_str']);

    case 'POST':
      // crée un mark
      $mark_array = $this->parseAtomMark($args['content']);
      return $marker->createMark($args['user'], $mark_array, 
				 $args['auth_str']);     
    }
  }

}

/** Controlleur sur un Tag.
 * @package    Atom
 */
class TagController {

  function execute () {
 
    $marker = new BlogMarks_marker;
    
    switch ($args['method']) {

    case 'GET':

      if ( isset($args['user']) )

	// renvoit un tag privé
	return $marker->getTag($args['tag'], $args['user'], $args['auth_str']);

      else

	// renvoit un tag publique
	return $marker->getTag($args['tag']);

    case 'PUT':

      $tag_array = $this->parseAtomTag($args['content']);

      if ( isset($args['user']) )

	// met à jour un tag privé
	return $marker->updateTag($args['tag'], $tag_array,
				  $args['user'], $args['auth_str']);

      else

	// met ç jour un tag publique
	return $marker->updateTag($args['tag'], $tag_array);     

    case 'DELETE':

      if ( isset($args['user']) )

	// supprimme un tag privé
	return $marker->deleteTag($args['tag'], $args['user'], 
				  $args['auth_str']);

      else

	// supprimme un tag publique
	return $marker->deleteTag($args['tag']);	

    case 'POST':

      $tag_array = $this->parseAtomTag($args['content']);

      if ( isset($args['user']) )

	// crée un tag privé
    	return $marker->createTag($args['tag'], $tag_array,
				  $args['user'], $args['auth_str']);

      else

	// crée un tag publique
	return $marker->createTag($args['tag'], $tag_array);    
    }
  }
}

/** Controlleur sur une liste de Marks.
 * @package    Atom
 */
class MarksListController {

  function execute () {

    $marker = new BlogMarks_marker;

    if ( isset($args['user']) ) {

      if ( isset($args['tag']) )

	// liste des Marks d'un utilisateur avec un tag
	return $marker->getMarksListOfUserOfTag($args['user'], $args['tag'],
						$args['auth_str']);
      
      else

	// liste des Marks d'un utilisateur
	return $marker->getMarksListOfUser($args['user'], $args['auth_str']);
    }
    
    // sinon c'est une liste de tags publics
    return $marker->getMarksListOfTag($args['tag'], $args['authstr']);
  }
}

/** Controlleur sur une liste de Tags.
 * @package    Atom
 */
class TagsListController {

  function execute () {

    $marker = new BlogMarks_marker;

    if ( isset($args['user']) )

      // liste de tags privés
      return $marker->getTagsListOfTag($args['tag']);

    else

      // liste de tags publiques
      return $marker->getTagsListOfUserOfTag($args['tag']);
  }
}