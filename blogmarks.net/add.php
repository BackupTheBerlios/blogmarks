<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";

// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

	$array_tags = explode( " " , $_POST['tags'] );

    echo "Connexion OK!\n";
    $uri =& $marker->createMark( array( 'href'		=> $_POST['url'] ,
                                        'title'		=> $_POST['title'] ,
										'summary'	=> $_POST['description'],
										'via'		=> $_POST['via'],
										'tags'		=> $array_tags
								      )
								);

    if ( Blogmarks::isError($uri) ) {
		
		echo "<p><b>Erreur !!</b><br>code : ".$uri->getCode()."<br>message : ". $uri->getMessage() ."</p>\n";
	}
    else echo "Mark URI: $uri\n";
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
}



?>