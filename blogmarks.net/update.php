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

<h3>Update a bookmark</h3>

<?php

if ( $marker->userIsAuthenticated() ) {

		$array_tags = explode( " " , trim( $_POST['tags'] ) );
	
		$params['related']  = trim( $_POST['url'] );
		$params['title']	= trim( $_POST['title'] );
		$params['summary']	= trim( $_POST['description'] );
		$params['via']		= trim( $_POST['via'] );

		//if ( isset( $array_tags ) )
		$params['tags']		= $array_tags; 

    $result =& $marker->updateMark( $_POST['id'], $params );


	if ( Blogmarks::isError($result) ) die( $result->getMessage() );
	if ( DB::isError($result) ) die( $result->getMessage() );
	
	echo '<p>Mark Sucessfully updated !</p>' . "\n";

	echo '<p>ID: ' . $result->id . '</p>' . "\n";
}

else echo "**error : Pas connecté";


if ( isset( $_GET['mini'] ) ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

} else {
	
	echo '<a href="index.php">Return Home</a>';

}

?>

<?php include 'includes/footer.inc.php' ?>

</div> <!-- # conteneur -->

</body></html>