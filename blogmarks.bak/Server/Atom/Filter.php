<?php
/** Déclaration des filtres, de la chaîne des filtres et des méthodes
 *   d'exécution.
 * @version    $Id: Filter.php,v 1.2 2004/03/11 15:49:29 benfle Exp $
 */


/** Classe racine représentant la chaîne des filtres à exécuter.
 * @package    Servers
 * @subpackage Atom
 */
class FilterChainRoot
{

  /** Pointeur sur le premier filtre de la chaîne.
   * @var array $_filters */
  var $_filters;
  
  /**
   * Constructeur.
   * @param array $filters Tableau d'instances de filtres. */
  function FilterChainRoot($filters)
  {
    $this->_filters =& $filters;
    $count = count($this->_filters);
    $index = 0;
    while ($index < ($count)):
      if ($index > 0)
	{
	  $tmp =& $this->_filters[$index - 1];
	  $tmp->setChildFilter(&$filters[$index]);
	}
    $index++;
    endwhile;
  }
  
  /**
   * Exécute le premier filtre de la chaîne.
   * @param array $args Tableau d'arguments à passer au premier filtre. */
  function execute($args)
  {
    $tmp = $this->_filters[0];
    return $tmp->execute($args);
  }
}

/**
 * Classe de base pour les objets filtres */
class InterceptingFilter
{
  /** Pointeur vers le filtre suivant.
   *  @var object $_childFilter */
  var $_childFilter;
  
  /** Indique si il y a un filtre après.
   * @var boolean $_hasChild */
  var $_hasChild = false;
  
  /** Enregistre le pointeur vers le filtre suivant.
   * @param object $filter filtre suivant */
  function setChildFilter($filter)
  {
    $this->_hasChild = true;
    $this->_childFilter =& $filter;
  }

  /** Renvoit un pointeur sur le filtre suivant. */
  function getChildFilter()
  {
    return $this->_childFilter;
  }
  
}

############################################
# Implémentation des différents filtres    #
############################################


/**
 * Classe du filtre constructeur de contexte.
 * construit le contexte d'exécution du controller
 * à partir de la requête HTTP. */
class ContextBuilderFilter extends InterceptingFilter {

  /** Arguments :
   * - method : méthode HTTP
   * - uri : URI relative de la requête
   * - content : contenu si c'est un POST ou un PUT */   
  function execute($arg) {
    
    // filtre l'URI pour déterminer l'objet de la requête
    $uri = $arg['uri'];
    unset($arg['uri']); // on n'en a plus besoin dans les arguments
    
    // get, put ou delete un Mark
    if ( ereg('^/users/([^/]+)/\?mark_id=([0-9]+)$', $uri, $regs) ) {
      $arg['user']   = $regs[1];
      $arg['object'] = 'Mark';
      $arg['id']     = $regs[2];

    } else if ( ereg('^/users/([^/]+)/?$', $uri, $regs) ) {
      $arg['user'] = $regs[1];

      // poste un Mark
      if ( $arg['method'] == 'POST' ) 
	$arg['object'] = 'Mark';

      // get une liste de Marks prives
      if ( $arg['method'] == 'GET' ) 
	$arg['object'] = 'MarksList';

      // get une liste de Marks prives avec un tag
    } else if ( ereg('^/users/([^/]+)/tags/([^/]+)/?$', $uri, $regs) ) {
      $arg['user']   = $regs[1];
      $arg['object'] = 'MarksList';
      $arg['tag']    = $regs[2];

      // get une liste de Marks avec un tag
    } else if ( ereg('^/tags/([^/]+)/?$', $uri, $regs) ) {
      $arg['object'] = 'MarksList';
      $arg['tag']    = $regs[1];
    
      // get une liste de tags publiques
    } else if ( ereg('^/tags/([^/]+)*/?\?service=feed', $uri, $regs) ) {
      $arg['object'] = 'TagsList';
      $arg['tag']    = $regs[1];

      // get une liste de tags privés
    } else if ( ereg('^/users/([^/]+)/tags/([^/]+)*/?\?service=feed', 
		     $uri, $regs) ) {
      $arg['user']   = $regs[1];
      $arg['object'] = 'TagsList';
      $arg['tag']    = $regs[2];
    
      // get, put ou delete un tag privé
    } else if ( ereg('^/users/([^/]+)/tags/\?tag_id=(.+)$)', $uri, $regs) ) {
      $arg['user']   = $regs[1];
      $arg['object'] = 'Tag';
      $arg['tag']    = $resg[2];

      // get, put ou delete un tag publique
    } else if ( ereg('^/tags/\?tag_id=(.+)$)', $uri, $regs) ) {
      $arg['object'] = 'Tag';
      $arg['tag']    = $resg[1];

      // poste un tag publique
    } else if ( ereg('^/tags/?$', $uri) ) {
      $arg['object'] = 'Tag';
      
      // poste un tag prive
    } else if ( ereg('^/users/([^/]+)/tags/?$', $uri, $regs) ) {
      $arg['object'] = 'Tag';
      $arg['user']   = $resg[1];

    } else
      return BlogMarks::raise_Error('URI incorrecte', 403);
    
    // passe au filtre suivant ou renvoit le contexte
    if ($this->_hasChild) {
      $tmp =& $this->_childFilter;
      return $tmp->execute($arg);
    }
  }
}

/**
 * Classe du filtre d'authentification. 
 * Enregistre la chaine d'authentification dans le contexte. */
class AuthenticateFilter extends InterceptingFilter {
  
  function execute($arg) {
    
    // Récupère la ligne X-WSSE du header
    $headers   = apache_request_headers();
    $auth_line = '';
    foreach ($headers as $header => $value) {
      if ($header == "X-WSSE") {
	$auth_line = $value;
      }
    }

    if ($auth_line != '') {
      
      // on récupère les informations du header
      $pattern  = 'Username="(.+)", PasswordDigest="(.+)",';
      $pattern .= ' Nonce="(.+)", Created="(.+)"';
      if (ereg($pattern, $value, $regs)) {
	$marker = new BlogMarks_Marker;
	$auth_str = $marker->autenticate($regs[1], $regs[2], 
					 $regs[3], $regs[4]);
	$arg['auth_str'] = $auth_str;
      } else
	return BlogMarks::raiseError('Erreur à la ligne <X-WSSE>', 400);
    }

    // passe au filtre suivant
    if ($this->_hasChild) {
      $tmp =& $this->_childFilter;
      return $tmp->execute($arg);
    }
  }
}