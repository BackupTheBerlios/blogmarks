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

/*
array( 'user' => $userlogin,
         'date_in' => 'mysqldate'
          'date_out => 'mysqldate',
          'exclude_tags' => array(tagsàexclure),
          'include_tags'  => array(tagsàinclure),
          'select_priv'      => bool,
          'order_by'          => array('field' ou array('fields'), DESC/ASC) );
*/



if ( $marker->userIsAuthenticated() AND !isset( $_GET['all'] ) ) {

	echo '<h2><a style="font-size:smaller" href="?all=1">Public marks</a> / Personal marks</h2>';

	$temp_flag = 0;

	$params['user_login']	=  $marker->getUserInfo('login') ;
	$params['select_priv']	=  TRUE ;

} elseif ( isset($_GET['all']) AND ( $_GET['all'] == 1 ) ) {

	$temp_flag = 1;
	
	echo '<h2>Public marks / <a style="font-size:smaller" href="?private=1">Personal marks</a></h2>';

//	$params['select_priv']	=  TRUE ;

} else {

	$temp_flag = 1;
	
	echo "<h2>Public marks</h2>";

//	$params['select_priv']	=  TRUE ;

}

$params['order_by']		=  array('created','DESC') ;


// To clean
if ( isset( $_GET['all']) AND ( $_GET['all'] == 1 ) )
	$complete_tag = "all=1";
else
	$complete_tag = "private=1";
//

if ( isset( $_GET['include_tags'] ) AND strlen( $_GET['include_tags'] ) )  {
	
	$params['include_tags'] = explode( " " , $_GET['include_tags'] );

	echo '<h4>Tags : ' .  $_GET['include_tags']  . ' <span class="smaller">(<a href="index.php?'.$complete_tag.'">reset</a>)</span> </h4>'."\r\n\r\n";

}

if ( isset( $_GET['q'] ) AND strlen( $_GET['q'] ) ) {

	if ( ( $_GET['checkSearch'] == 0 ) OR ( $_GET['checkSearch'] == 2 )  )
		$params['title']	= array( '%'.$_GET['q'].'%', 'LIKE' );

	if ( ( $_GET['checkSearch'] == 1 ) OR ( $_GET['checkSearch'] == 2 )   )
		$params['summary']	= array( '%'.$_GET['q'].'%', 'LIKE' );

}

print_r( $params );

$list =& $marker->getMarksList( $params );

if ( Blogmarks::isError($list) ) {

	echo '<p>' . $list->getMessage() . '</p>';

	if ( $list->getCode() == '444' ) {

		if ( !isset( $_GET['q'] ) )
			echo "<p>Your BlogMark account seems to be empty :-)</p>";
		else 
			echo "<p>Aucun BlogMark ne correspond aux termes de recherche spécifiés</p>";

	}

	//die(  $list->getCode() . ' ' .  $list->getMessage() );

}

elseif ( DB::isError($list) )
	
die( $list->getMessage() );

else {


// To clean
$i = 0;
$string_date_prev = '';
//

while ( $list->fetch() ) {
		
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

		if ( $temp_flag == 1 )
			echo $owner . " - ";

		$link = $list->getLink( 'href' );

		echo '<a href="' .  $link->href . '">' . $list->title . '</a>' ;
		
		if ( strlen($list->summary) ) echo ' : ' . $list->summary;

		//	echo ' (' . dcdate2php( $list->created ) . ')';
        
        foreach ( $list->getTags() as $tag ) {
			echo ' <a class="tag" href="?include_tags='. $tag . '&amp;' . $complete_tag.'">[' . $tag . ']</a> ';
		}

		//echo ' <a href="infos.php?id=' . $list->id . '">infos</a>';

		if ( isset( $USER ) AND ( $owner == $USER ) ) {

			echo ' <a onclick="return Edit(this.href)"  href="edit.php?id=' . $list->id . '">edit</a>';

			echo ' <a onclick="return Delete(this.href)" href="delete.php?id=' . $list->id . '">delete</a>';

		}

		echo '</li>'."\r\n";

		$i ++;
    }

} // fin de si pas d'erreur
?>

</ul>

<?php include 'includes/footer.inc.php' ?>

</div> <!-- /#conteneur -->

</body></html>

