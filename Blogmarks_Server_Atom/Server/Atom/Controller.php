<?php
/** Déclaration des différents controlleurs et de leur Factory.
 * @version    $Id: Controller.php,v 1.3 2004/05/19 14:14:42 benfle Exp $
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

    default:
      return BlogMarks::raiseError($object.' n\'est pas un objet blogmark', 
				   500);
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

    $marker = $args['marker'];

    switch ($args['method']) {

    case 'GET':
      // renvoit un mark
      return $marker->getMark($args['id']);

    case 'PUT':
      // met a jour un mark
      $mark_array = $this->parseAtom($args['content']);
      return $marker->updateMark($args['id'],
				 $mark_array);

    case 'DELETE':
      // supprimme un mark
      return $marker->deleteMark($args['id']);

    case 'POST':
      // crée un mark
      $mark_array = $this->parseAtom($args['content']);
      return $marker->createMark($mark_array);     
    }
  }

  /** Parse un document XML décrivant un Mark et renvoit un tableau de valeurs.
   */
  function parseAtom ($buffer) {

    $props = array ();

    // on construit le DOM à partir du flux XML
    if ( !$dom = domxml_open_mem($buffer) )
      return BlogMarks::raiseError('Impossible de créer le document DOM', 500);
    
    // récupère l'élément racine (blogmark)
    $root = $dom->document_element();
    
    // enregistre la langue du mark
    $props['lang'] = $root->get_attribute('lang');

    // enregistre le href du link
    $link = $root->get_elements_by_tagname('link');
    $props['href'] = $link[0]->get_attribute('href');

    // enregistre le nom de l'auteur du mark
    $author = $root->get_elements_by_tagname('author');
    $name = $author[0]->get_elements_by_tagname('name');
    $props['login'] = $name[0]->get_content();

    // enregistre le titre du mark
    $title = $root->get_elements_by_tagname('title');
    $props['title'] = $title[0]->get_content();

    // enregistre la description du mark
    $summary = $root->get_elements_by_tagname('summary');
    $props['summary'] = $summary[0]->get_content();

    // enregistre la date de publication
    $issued = $root->get_elements_by_tagname('issued');
    if ( !$issued = array() )
      $props['issued'] = $issued[0]->get_content();

    // enregistre le href de via
    $via = $root->get_elements_by_tagname('via');
    if ( $via != array() )
      $props['via'] = $via[0]->get_attribute('href');   
    
    // enregistre le tableau de tags
    $tags = $root->get_elements_by_tagname('tags');
    if ( $tags != array() ) {

      $props['tags'] = array();

      $tags = $tags->get_elements_by_tagname('tag');

      // on construit le tableau de chaque tag (juste title) 
      // puis on l'empile dans le tableau principal
      foreach ( $tags as $tag ) {

	$tag_props = array();

	// on récupère le nom du tag
	$title = $tag->get_elements_by_tagname('title');
	$tag_props['title'] = $title[0]->get_content();

	// on empile
	array_push($props['tags'], $tag_props);
      }
    }
    
    return $props;
  }
  
}

/** Controlleur sur un Tag.
 * @package    Atom
 */
class TagController {

  function execute ($args) {
 
    $marker = $args['marker'];
    
    switch ($args['method']) {

    case 'GET':

      if ( isset($args['user']) )

	// renvoit un tag privé
	return $marker->getTag($args['tag'], $args['auth_str']);

      else

	// renvoit un tag publique
	return $marker->getTag($args['tag']);

    case 'PUT':

      $tag_array = $this->parseAtom($args['content']);

      if ( isset($args['user']) )

	// met à jour un tag privé
	return $marker->updateTag($args['tag'], $tag_array,
				  $args['user'], $args['auth_str']);

      else

	// met à jour un tag publique
	return $marker->updateTag($args['tag'], $tag_array);     

    case 'DELETE':

      if ( isset($args['user']) )

	// supprimme un tag privé
	return $marker->deleteTag($args['tag'], 
				  $args['auth_str']);

      else

	// supprimme un tag publique
	return $marker->deleteTag($args['tag']);	

    case 'POST':

      $tag_array = $this->parseAtom($args['content']);

      if ( isset($args['user']) )

	// crée un tag privé
    	return $marker->createTag($args['tag'], $tag_array,
				  $args['user'], $args['auth_str']);

      else

	// crée un tag publique
	return $marker->createTag($args['tag'], $tag_array);    
    }
  }

  /** Parse un document XML décrivant un Tag et renvoit un tableau de valeurs.
   */
  function parseAtom ($buffer) {

    $props = array ();

    // on construit le DOM à partir du flux XML
    if ( !$dom = domxml_open_mem($buffer) )
      return BlogMarks::raiseError('Impossible de créer le document DOM', 500);
    
    // récupère l'élément racine (tag)
    $root = $dom->document_element();
    
    // enregistre la langue du tag
    $props['lang'] = $root->get_attribute('lang');

    // enregistre le titre du tag
    $title = $root->get_elements_by_tagname('title');
    $props['title'] = $title[0]->get_content();

    // enregistre la description du tag
    $summary = $root->get_elements_by_tagname('summary');
    $props['summary'] = $summary[0]->get_content();

    // enregistre le nom du tag parent
    $subTagOf = $root->get_elements_by_tagname('subTagOf');
    if ( !$subTagOf = array() )
      $props['subTagOf'] = $subTagOf[0]->get_content();
    
    return $props;
  } 
}

/** Controlleur sur une liste de Marks.
 * @package    Atom
 */
class MarksListController {

  function execute ($args) {

    $marker = $args['marker'];

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
    return $marker->getMarksListOfTag($args['tag'], $args['auth_str']);
  }
}

/** Controlleur sur une liste de Tags.
 * @package    Atom
 */
class TagsListController {

  function execute ($args) {

    $marker = $args['marker'];

    if ( isset($args['user']) )

      // liste de tags privés
      return $marker->getTagsListOfTag($args['tag']);

    else

      // liste de tags publiques
      return $marker->getTagsListOfUserOfTag($args['tag']);
  }
}