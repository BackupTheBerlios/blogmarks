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



if ( $marker->userIsAuthenticated() AND ( $_GET['all'] != 1 ) ) {

	echo '<h2><a style="font-size:smaller" href="?all=1">All last public marks</a> / Your last marks</h2>';

	$params['user_login']	=  $marker->getUserInfo('login') ;

} elseif ( $_GET['all'] == 1 ) {
	
	echo '<h2>All last public marks / <a style="font-size:smaller" href="?private=1">Your last marks</a></h2>';

} else {
	
	echo "<h2>All last public marks</h2>";

}

$params['order_by']		=  array('created','DESC') ;
$params['select_priv']	=  TRUE ;

if ( isset( $_GET['include_tags'] ))  {
	
	$params['include_tags'] = explode( ";" , $_GET['include_tags'] );

	echo '<h4>Tags : ' .  $_GET['include_tags']  . ' <span class="smaller">(<a href="index.php">reset</a>)</span> </h4>'."\r\n\r\n";

}

if ( isset( $_GET['q'] ) ) {

	if ( ( $_GET['checkSearch'] == 0 ) OR ( $_GET['checkSearch'] == 2 )  )
		$params['title']	= array( '%'.$_GET['q'].'%', 'LIKE' );

	if ( ( $_GET['checkSearch'] == 1 ) OR ( $_GET['checkSearch'] == 1 )   )
		$params['summary']	= array( '%'.$_GET['q'].'%', 'LIKE' );

}

$list =& $marker->getMarksList( $params );

if ( Blogmarks::isError($list) ) die( $list->getMessage() );

if ( DB::isError($list) ) die( $list->getMessage() );

$i = 0;

$string_date_prev = '';

while ( $list->fetch() ) {
		
		// Date handling

		$timestamp = dcdate2php( $list->created );
	
		$string_date = date( "j/m/Y" ,  $timestamp );

		if ($string_date_prev != $string_date) {
			if ( $i != 0 ) echo "</ul>"."\r\n";
			echo "\r\n".'<h3>' . $string_date . '</h3>'."\r\n\r\n";
			echo '<ul>'."\r\n";
		}

		$string_date_prev = $string_date;
		
		
		echo '<li>';

		$link = $list->getLink( 'href' );

		echo '<a href="' .  $link->href . '">' . $list->title . '</a>' ;
		
		if ( strlen($list->summary) ) echo ' : ' . $list->summary;

		//	echo ' (' . dcdate2php( $list->created ) . ')';
        
        foreach ( $list->getTags() as $tag ) {
			echo ' <a class="tag" href="?include_tags='. $tag .'">[' . $tag . ']</a> ';
		}

		echo ' <a href="infos.php?id=' . $list->id . '">infos</a>';

		echo ' <a onclick="return Edit(this.href)"  href="edit.php?id=' . $list->id . '">edit</a>';

		echo ' <a onclick="return confirmDelete(this.href)" href="delete.php?id=' . $list->id . '">delete</a>';

		echo '</li>'."\r\n";

		$i ++;
    }

?>

</ul>

<?php include 'includes/footer.inc.php' ?>

</div> <!-- /#conteneur -->

</body></html>

