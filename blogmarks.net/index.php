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

<?php include "includes/header.inc.php" ?>

<?php





if ( !isset ($_GET['section']) )
	$_GET['section'] = 'PublicMarks';

if ( $_GET['section'] == 'AdvancedSearch' )
{
	echo '<h2>AdvancedSearch</h2>';
	?>
	<form id="advanced_earch" method="GET">
		Search in : 
		<select name="search_in" size="1">
			<option value="PublicMarks">PublicMarks</option>
			<option value="MyMarks">MyMarks</option>
			<option value="MyHotlinks">MyHotlinks</option>
		</select><br/>
		Search : <input type="text" name="q" size="40" value="<?php if ( isset($_GET['q']) )  echo $_GET['q'] ?>" /><br/>
		Blogmarker : <input type="text" name="author"/><br/>
		Include Tags : <input type="text" name="include_tags" size="40" value="<?php if ( isset($_GET['include_tags']) )  echo $_GET['include_tags'] ?>" /><br/>
		Exclude Tags : <input type="text" name="exclude_tags" size="40" value="<?php if ( isset($_GET['exclude_tags']) )  echo $_GET['exclude_tags'] ?>" /><br/>
		Created before : <input type="text" name="date_in"/><br/>
		Created after : <input type="text" name="date_out"/><br/>
		language : 
		<select name="lang" size="1">
			<option value="fr">fr</option>
			<option value="en">en</option>
		</select><br/>
		<br/>
		Order by : 
		<select name="order_by" size="1">
			<option value="user">user</option>
			<option value="issued">issued</option>
			<option value="created">created</option>
			<option value="modified">modified</option>
			<option value="lang">lang</option>
		</select><br/>
		Order type :
		<select name="order_type" size="1">
			<option value="asc">asc</option>
			<option value="desc">desc</option>
		</select><br/>
		<input type="submit" value="search"/>
	</form>
<?php
}
else
{

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
			echo '<h2>MyMarks</h2>';

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

		case 'MyHotlinks':
			echo '<h2>MyHotlinks</h2>';
			break;
	
		case 'PublicMarks':
			echo '<h2>PublicMarks</h2>';

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



/*
$uri = 'http://localhost/blogmarks.atom/search' . $uri;
echo '<a href="' . $uri . '">' . $uri . '</a><br/>';
*/
?>
<!--
<form name="add_hotlinks" method="GET">
<input type="hidden" name="href" value="<?php echo $uri; ?>"/>
<input type="submit" value="AddInMyHotlinks"/>
</form>
!-->

<?php
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
		//

		while ( $list->fetch() ) 
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
					$tag = $list->getTag ($tag_id);
					if ( $tag->author != NULL )
					{
						// tag privé
						echo ' <a class="private_tag" href="?include_tags=private:'. $tag->title . '">[' . $tag->title . ']</a> ';
					} else
					{
						// tag public
						echo ' <a class="public_tag" href="?include_tags='. $tag->title . '">[' . $tag->title . ']</a> ';
					}
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

<?php include 'includes/footer.inc.php' ?>

</div> <!-- /#conteneur -->

</body></html>

