<?php

define('BM_SERVER', 'benfle.dyndns.org');
define('BM_POST_URI', '/blogmarks/servers/atom/users/benoit');

ini_set( 'include_path', ini_get('include_path') . ':/home/benoit/dev/' ); 

require_once('Blogmarks/Client/request.php');

include('Blogmarks/Client/header.php');
?>

<h1>BlogMarks Client Atom</h1>

<div id="menu">
<ul>
<li><a href="?action=new_mark">Ajouter Mark</a></li>
<li><a href="?action=new_tag">Ajouter Tag</a></li>
<li><a href="?action=list_marks">Liste de Marks</a></li>
<li><a href="?action=list_tags">Liste de Tags</a></li>
<li><a href="?action=search">Rechercher</a></li>
</ul>
</div>

<div id="nav">
<?php
switch ($_GET['action']) {
  case 'new_mark':
   include 'new_mark.php';
   break;
  case 'post_mark':
   include 'post_mark.php';
   break;
  case 'post_tag':
   include 'post_tag.php';
   break;
  case 'new_tag':
   include 'new_tag.php';
   break;
  case 'list_tags.php':
   include 'list_tags.php';
   break;
  default:
   /* affichage de la liste des marks persos */
   include 'list_marks.php';
}
?>
</div>
<div style="clear:both;"></div>

<?php include('Blogmarks/Client/footer.php'); ?>