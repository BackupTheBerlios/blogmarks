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

</head>

<?php

if ( isset( $_GET['mini'] ) ) {
	echo '<body onload="window.focus()" class="mini">';
} else {
	echo '<body>';
}

?>

<div id="conteneur">

<?php include 'includes/header.inc.php' ?>

<div id="content">

<h3>Insert a bookmark</h3>

<?php

if ( $marker->userIsAuthenticated() ) {

	$array_tags = explode( " " , trim($_POST['tags']) );

	$params = array(
					 'related'	=> trim( $_POST['url']		) ,
                     'title'	=> trim( $_POST['title']	) ,
					 'summary'	=> $_POST['description']	  ,
					 'via'		=> trim( $_POST['via'] )	  ,
					 'tags'		=> $array_tags				  ,
					 'public'	=> true
					);

	print_r( $params );

    $result =& $marker->createMark( $params );

	if ( Blogmarks::isError($result) ) die( $result->getMessage() );
	if ( DB::isError($result) ) die( $result->getMessage() );
	
	echo '<p>Mark Sucessfully inserted !</p>' . "\n";

	echo '<p>URI:<br />' . print_r( $result ) . '</p>' . "\n";

} else echo "**Error : Pas connecté - to be customized";


if ( isset( $_GET['mini'] ) ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

} else {
	
	echo '<a href="index.php">Return Home</a>';

}

?>

</div> <!-- # content -->

<?php include 'includes/footer.inc.php' ?>

</div> <!-- # conteneur -->

</body></html>