<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";

echo $_POST['id'];
// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

    echo "Connexion OK!\n";

	$array_tags = explode( " " , $_POST['tags'] );

    $uri =& $marker->updateMark( $_POST['id'], 
								 
								array(  'href'		=> $_POST['url'] ,
                                        'title'		=> $_POST['title'] ,
										'summary'	=> $_POST['description'],
										'via'		=> $_POST['via'],
										'tags'		=> $array_tags
								      )
								);

    if ( Blogmarks::isError($uri) ) echo "*** Erreur: ". $uri->getMessage() ."\n";
    else echo "Mark URI: $uri\n";
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
}



?>