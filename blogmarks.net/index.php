<?php

include_once "includes/functions.inc.php";
include "includes/start.inc.php";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Blogmarks.net</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" title="default" type="text/css" href="style.css" media="all"  />
<script type="text/javascript" src="behavior.js"></script>

</head>

<body>

<div id="conteneur">

<?php

// Inclusion du Header
include "includes/header.inc.php";

?>

<div id="content">

<?php

if ( !isset ($_GET['section']) )
	$_GET['section'] = 'PublicMarks';

if ( $_GET['section'] == 'AdvancedSearch' ) {

    include 'includes/advanced_search.inc.php';
	
} else {

	/*

	On remplit le tableau a passer a getMarksList 

	array(	'user'          => $userlogin,
   	    	'date_in'       => 'mysqldate'
       		'date_out       => 'mysqldate',
       		'exclude_tags'  => array(tagsàexclure),
       		'include_tags'  => array(tagsàinclure),
       		'select_priv'   => bool,
       		'order_by'      => array('field' ou array('fields'), DESC/ASC) );
	*/

	switch ( $_GET['section'] )
	{
		case 'MyMarks':
			//echo '<h2>MyMarks</h2>';

			/* On construit le tableau de parametres a envoyer a getMarksList */

			$login = $marker->getUserInfo('login');
			if ( Blogmarks::isError ( $login ) )
			{
				echo $login->getMessage();
			} else
			{
				$params['user_login']	=  $login;
				if ( !isset ( $uri ) )
					$uri = '?user_login=' . $login;
				else
					$uri .= '&user_login=' . $login;
			}

			$params['select_priv']	=  TRUE ;
			if ( !isset ( $uri ) )
				$uri = '?select_priv=1';
			else
				$uri .= '&select_priv=1';

			$params['order_by']		=  array('created','DESC') ;
			if ( !isset ( $uri ) )
				$uri = '?order_by=created&order_type=desc';
			else
				$uri .= '&order_by=created&order_type=desc';
			break;
	
		case 'PublicMarks':
			//echo '<h2>PublicMarks</h2>';

			$params['order_by']		=  array('created','DESC') ;
			if ( !isset ( $uri ) )
				$uri = '?order_by=created&order_type=desc';
			else
				$uri .= '&order_by=created&order_type=desc';
	}

	/* On ajoute des parametres de recherche si nécessaire */

	/* recherche de texte parmis le titre et la description du mark */

	if ( isset( $_GET['q'] ) AND strlen( $_GET['q'] ) ) 
	{
		$params['title']	= array( '%'.$_GET['q'].'%', 'LIKE' );
		$params['summary']	= array( '%'.$_GET['q'].'%', 'LIKE' );
	}

	/* inclure des tags */

	if ( isset( $_GET['include_tags'] ) AND strlen( $_GET['include_tags'] ) )
	{
		$params['include_tags'] = explode( " " , $_GET['include_tags'] );

		if ( !isset ( $uri ) )
			$uri = '?include_tags=' . $_GET['include_tags'];
		else
			$uri .= '&include_tags=' . $_GET['include_tags'];
	}

	//print_r( $params );


	/* On effectue la recherche */

	$list =& $marker->getMarksList( $params );


	/* On affiche le résultat */

	if ( Blogmarks::isError($list) ) {

		echo '<p>' . $list->getMessage() . '</p>';

		if ( $list->getCode() == '444' ) {

			if ( !isset( $_GET['q'] ) )
				echo "<p>Your BlogMark account seems to be empty :-)</p>";
		}

		//die(  $list->getCode() . ' ' .  $list->getMessage() );

	}
	elseif ( DB::isError($list) )
	{
		die( $list->getMessage() );
	}
	else 
	{
		// To clean
		$i = 0;
		$string_date_prev = '';
		$max = 25;
		//

		while ( $list->fetch() && $i <= $max ) 
		{
			// Date handling

			$timestamp = dcdate2php( $list->created );
	
			$string_date = date( "j/m/Y" ,  $timestamp );

			if ($string_date_prev != $string_date) {
				if ( $i != 0 ) echo '</ul>' . "\r\n";
				echo "\r\n".'<h3>' . $string_date . '</h3>'."\r\n\r\n";
				echo '<ul>'."\r\n";
			}

			$string_date_prev = $string_date;
		
		
			echo '<li>';

			$owner = $list->getOwner();

			if ( $_GET['section'] == 'PublicMarks' )
				echo $owner . " - ";

			$link = $list->getLink( 'related' );

			echo '<a href="' .  $link->href . '">' . $list->title . '</a>' ;
		
			if ( strlen($list->summary) ) echo ' : ' . $list->summary;

			//	echo ' (' . dcdate2php( $list->created ) . ')';
        
			if ( isset( $login ) AND ( $owner == $login ) )
				$private = true;
			else
				$private = false;


			$tags_id = $marker->getTags($list->id);

			if ( Blogmarks::isError ($tags_id) )
			{
				echo $tags_id->getMessage();
			} else
			{
				foreach ( $marker->getTags($list->id) as $tag_id ) {
					$tag = $marker->getTag ($tag_id);
					if ( $tag->author != NULL )
						// tag privé
						$link = ' <a class="private_tag" href="?include_tags=private:'. $tag->title . '"';
					else
						// tag public
						$link = ' <a class="public_tag" href="?include_tags='. $tag->title . '"';

					if ( isset ( $tag->summary ) && $tag->summary != '' )
						$link .= ' title="'. $tag->summary .'"';
					$link .= '>[' . $tag->title . ']</a> ';
					echo $link;
				}
			}
			//echo ' <a href="infos.php?id=' . $list->id . '">infos</a>';

			if ( isset( $login ) AND ( $owner == $login ) ) 
			{
				echo ' <a onclick="return Edit(this.href)"  href="edit.php?id=' . $list->id . '">edit</a>';
				echo ' <a onclick="return Delete(this.href)" href="delete.php?id=' . $list->id . '">delete</a>';
			}
            
			echo '</li>'."\r\n";

            

			$i ++;
		}

	} // fin de si pas d'erreur

} // fin de si pas Advanced Search

?>

</ul>

</div> <!-- /# content -->

<?php

include 'includes/footer.inc.php'

?>

</div> <!-- /#conteneur -->

</body></html>