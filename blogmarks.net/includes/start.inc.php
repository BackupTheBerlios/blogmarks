<?php

session_start();

//error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );

require_once 'PEAR.php';
include_once 'Blogmarks/Marker.php';

$marker =& Blogmarks_Marker::singleton();

if ( isset( $_GET['connect'] ) AND ( $_GET['connect'] == 1 ) ) {

	$nonce = microtime() / rand();
	$time = date( "YMDHMS" );

	$digest = makeDigest( trim( $_POST['pwd'] ) , $nonce, $time );

	$auth = $marker->authenticate( trim( $_POST['login'] ) , $digest, $nonce, $time, TRUE );

	if ( Blogmarks::isError( $auth ) )
		$auth_error = $auth->getMessage();
	elseif ( DB::isError( $auth ) )
		$auth_error = $auth->getMessage();
	
	//echo $auth_error;
	//die();


	//header("Location: index.php");

}

if ( isset( $_GET['disconnect'] ) AND ( $_GET['disconnect'] == 1 ) ) {

	$marker->disconnectUser();

	header("Location: index.php");

}

?>