<?php
/** Déclaration des renderers pour le serveur Atom et de la factory
 * @version    $Id: Renderer.php,v 1.3 2004/05/19 14:14:42 benfle Exp $
 */

/** Factory de renderers Atom.
 * @package    Atom
 */
class RendererFactory {

  function createRenderer($object)
  {
    $renderer = null;

    switch($object){

    case ('Mark'):
      $renderer = new Renderer_Atom_Mark();
      break;

    case ('Tag'):
      $renderer = new Renderer_Atom_Tag();
      break;

    case ('MarksList'):
      $renderer = new Renderer_Atom_MarksList();
      break;

    case ('TagsList'):
      $renderer = new Renderer_Atom_TagsList();
      break;
    }
    
    return $renderer;
  }
}

/** Renderer d'un Mark.
 * @package    Atom
 */
class Renderer_Atom_Mark extends BlogMarks_Renderer {
  
  /**
   * Renvoit la représentation XML Atom d'un Mark
   */
  function render () {
    echo $this->_decoration;
  }

  /**
   * Construit l'arbre DOM d'un Mark.
   * @param object Mark
   */
  function visit ($mark) {

    // on crée le document XML
    $doc = domxml_new_doc("1.0");

    // on ajoute la racine blogmark
    $root = $doc->create_element('blogmark');
    $root = $doc->append_child($root);

    // on ajoute les éléments du Mark

    // via
    $via = $doc->create_element('via');
    $via = $root->append_child($via);
    $via->set_attribute('rel', $mark->via->rel);
    $via->set_attribute('type', $mark->via->type);
    $via->set_attribute('href', $mark->via->href);
    $via->set_attribute('title', $mark->via->title);
    $via->set_attribute('lang', $mark->via->lang);
    
    // link
    $link = $doc->create_element('link');
    $link = $root->append_child($link);
    $link->set_attribute('rel', $mark->link->rel);
    $link->set_attribute('type', $mark->link->type);
    $link->set_attribute('href', $mark->link->href);
    $link->set_attribute('title', $mark->link->title);
    $link->set_attribute('lang', $mark->link->lang);
    
    // author
    $author = $doc->create_element('author');
    $author = $root->append_child($author);
    $name   = $doc->create_element('name');
    $name   = $author->append_child($name);
    $text   = $doc->create_text_node($mark->author->login);
    $text   = $name->append_child($text);
    $email  = $doc->create_element('email');
    $email  = $author->append_child($email);
    $text   = $doc->create_text_node($mark->author->email);
    $text   = $email->append_child($text);    
    
    // pour les items simples, on fait tjrs la meme chose
    $simple_items = array ('title', 'summary', 'screenshot', 'issued', 
			  'created', 'modified');

    foreach ($simple_items as $item) {

      // vérifie que la variable a une valeur
      if ( isset ($mark->$item) ) {

	// crée l'élément
	$$item = $doc->create_element($item);

	// l'ajoute à la racine
	$$item = $root->append_child($$item);

	// crée son contenu
	$text  = $doc->create_text_node($mark->$item);

	// l'ajoute à l'élément
	$text  = $$item->append_child($text);
      }

    }

    // la liste des tags

    $tags = $doc->create_element('tags');
    $tags = $doc->append_child($tags);
    
    // on ajoute chaque tag
    if ( $tagslist != NULL )
      {
	while (!$tagslist->end()) {
     
	  $tag = $tagslist;

	  // A COPIER COLLER

	  // passe au tag suivant
	  $tagslist->next();
	}
      }
    $this->decoration = $doc->dump_mem(true);
  }

}

/** Renderer d'un Tag.
 * @package    Atom
 */
class Renderer_Atom_Tag extends BlogMarks_Renderer {
  
  /**
   * Renvoit la représentation XML Atom d'un Tag
   */
  function render () {
    echo $this->_decoration->dump_mem(true);
  }

  /**
   * Construit l'arbre DOM d'un Tag.
   * @param object Tag
   */
  function visit ($tag) {

    // on crée le document XML
    $doc = domxml_new_doc("1.0");

    // on ajoute la racine tag
    $root = $doc->create_element('tag');
    $root = $doc->append_child($root);
    
    // langue du tag
    $root->set_attribute('lang', $tag->lang);

    // ajoute les 3 éléments simples
    $items = array ('title', 'summary', 'subTagOf');
    foreach ($items as $item) {
      $$item = $doc->create_element($item);
      $$item = $root->append_child($$item);
      $text  = $doc->create_text_node($tag->$item);
      $text  = $$item->append_child($text);
    }

    $this->_decoration = $doc;
  }

}

/** Renderer d'une liste de Tags.
 * @package    Atom
 */
class Renderer_Atom_TagsList extends BlogMarks_Renderer {
  
  /**
   * Renvoit la représentation XML Atom d'une liste de Tags
   */
  function render () {
    echo $this->_decoration->dump_mem(true);
  }

  function visit ($tagslist) {

    // on crée le document XML
    $doc = domxml_new_doc("1.0");

    // on ajoute la racine tag
    $root = $doc->create_element('feed');
    $root = $doc->append_child($root);
    
    // on ajoute chaque tag
    if ( $tagslist != NULL )
      {
	while (!$tagslist->end()) {
     
	  $tag = $tagslist;

	  // A COPIER COLLER

	  // passe au tag suivant
	  $tagslist->next();
	}
      }
    $this->_decoration = $doc;
  }
}

/** Renderer d'une liste de Marks.
 * @package    Atom
 */
class Renderer_Atom_MarksList extends BlogMarks_Renderer {
  
  /**
   * Renvoit la représentation XML Atom d'une liste de Marks
   */
  function render () {
    echo $this->_decoration->dump_mem(true);
  }

  function visit ($markslist) {

    // on crée le document XML
    $doc = domxml_new_doc("1.0");

    // on ajoute la racine tag
    $root = $doc->create_element('feed');
    $root = $doc->append_child($root);
    
    // on ajoute chaque mark
    while (!$markslist->end()) {
     
      $mark = $markslist;

      // A COPIER COLLER

      // passe au mark suivant
      $markslist->next();
    }
    $this->_decoration = $doc;
  }
}