<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";



// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

	$array_tags = explode( " " , trim($_POST['tags']) );
	
	//include_once 'Snapbot.php';
	
	//$sn =& new Snapbot;
	//$sn->setSavePath('../SnapBot/images/');
	//$img_path = $sn->capture( array('url' => trim($_POST['url']) , 'width' => 1024 , 'height' => 768 , 'ratio' => 7 ) );
	
	//echo $img_path;
	

    echo "Connexion OK!\n";

	$params = array( 'href'		=> trim( $_POST['url'] ) ,
                     'title'	=> trim( $_POST['title'] ) ,
					 'summary'	=> $_POST['description'],
					 'via'		=> trim( $_POST['via'] ),
					 'tags'		=> $array_tags );
	//print_r( $params );
    $uri =& $marker->createMark( $params );

    if ( Blogmarks::isError($uri) ) {
		
		echo "<p><b>Erreur !!</b><br>code : ".$uri->getCode()."<br>message : ". $uri->getMessage() ."</p>\n";
	}
    else echo "Mark URI: $uri\n";
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
}

if ( $_POST['from'] == 'popupbookmarklet' ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

}

?>