<?php

include_once "includes/functions.inc.php";
include "includes/start.inc.php";


// AUTH OK :)

//if ( Blogmarks::isError( $auth ) ) die( $mark->getMessage() );
//if ( DB::isError( $auth ) ) die( $mark->getMessage() );

if ( $marker->userIsAuthenticated() ) {

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
    $result =& $marker->createMark( $params );

	if ( Blogmarks::isError($result) ) die( $result->getMessage() );
	if ( DB::isError($result) ) die( $result->getMessage() );
	
	echo "Mark URI: $result\n";

} else echo "pas conecté";

//if ( $_POST['from'] == 'popupbookmarklet' ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

//}

?>