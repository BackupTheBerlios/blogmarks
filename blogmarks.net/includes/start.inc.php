<?php

session_start();

error_reporting( E_ALL ^ E_NOTICE );

require_once 'PEAR.php';
include_once 'Blogmarks/Marker.php';

$marker =& Blogmarks_Marker::singleton();

if ( $_GET['connect'] == 1 ) {

	$nonce = microtime() / rand();
	$time = date( "YMDHMS" );

	$digest = makeDigest( trim( $_POST['pwd'] ) , $nonce, $time );

	$auth = $marker->authenticate( trim( $_POST['login'] ) , $digest, $nonce, $time, TRUE );

	header("Location: index.php");

}

if ( isset( $_GET['disconnect'] ) AND ( $_GET['disconnect'] == 1 ) ) {

	$marker->disconnectUser();

	header("Location: index.php");

}

?>