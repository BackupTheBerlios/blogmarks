<?php

session_start();

error_reporting( E_ALL ^ E_NOTICE );

require_once 'PEAR.php';
include_once 'Blogmarks/Marker.php';

$marker =& Blogmarks_Marker::singleton();

//print_r( $_POST );
if ( $_POST['signin'] == 1 ) {

	//echo "tentative auth";

	# -- CONFIGURATION
	$nonce = microtime() / rand();
	$time = date( "YMDHMS" );
	//$user = 'znarf';
	//$pwd = 'cuix84ds';

	$user	= $_POST['login'];
	$pwd	= $_POST['pwd'];

	$digest = makeDigest( $pwd, $nonce, $time );

	$auth = $marker->authenticate( $user, $digest, $nonce, $time, TRUE );

}

if ( isset( $_GET['disconnect'] ) AND ( $_GET['disconnect'] == 1 ) ) {

	$marker->disconnectUser();

	header("Location: index.php");

}

?>