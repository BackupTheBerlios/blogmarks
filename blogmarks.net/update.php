<?php

include_once "includes/functions.inc.php";
include "includes/start.inc.php";

echo $_POST['id'];
// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

    echo "Connexion OK!\n";
	
	//if ( strlen( trim( $_POST['tags'] ) ) )
		$array_tags = explode( " " , trim( $_POST['tags'] ) );
	
		$params['href']		= trim( $_POST['url'] );
		$params['title']	= trim( $_POST['title'] );
		$params['summary']	= trim( $_POST['description'] );
		$params['via']		= trim( $_POST['via'] );

	//if ( isset( $array_tags ) )
		$params['tags']		= $array_tags; 

    $uri =& $marker->updateMark( $_POST['id'], $params );

    if ( Blogmarks::isError($uri) ) echo "<p><b>Erreur !!</b><br>code : ".$uri->getCode()."<br>message : ". $uri->getMessage() ."</p>\n";
    else echo "Mark URI: $uri\n";
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
}


if ( $_POST['mini'] == '1' ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

}


?>