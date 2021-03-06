<?php

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
  }

$time_start = getmicrotime();

/* ------------------------------- */

session_start();

//error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );

require_once 'PEAR.php';
include_once 'Blogmarks/Marker.php';

$marker =& Blogmarks_Marker::singleton();

//echo "<p>Exec. time : " . round( ( getmicrotime() - $time_start ) , 3 ) . " s</p>";

if ( isset( $_GET['connect'] ) AND ( $_GET['connect'] == 1 ) ) {

	$nonce = microtime() / rand();
	$time = date( "YMDHMS" );

	$digest = makeDigest( trim( $_POST['pwd'] ) , $nonce, $time );

	$auth = $marker->authenticate( trim( $_POST['login'] ) , $digest, $nonce, $time, TRUE );

	if ( Blogmarks::isError( $auth ) )
		$auth_error = $auth->getMessage();
	elseif ( DB::isError( $auth ) )
		$auth_error = $auth->getMessage();
	
	//header("Location: index.php");
	header("Location: " . str_replace( "connect=1", "" , $_SERVER["REQUEST_URI"] ) );

}

if ( isset( $_GET['disconnect'] ) AND ( $_GET['disconnect'] == 1 ) ) {

	$marker->disconnectUser();

	header("Location: index.php");
	//header("Location: " . $_SERVER["REQUEST_URI"] );

}

if ( !isset ($_GET['section']) )
	$_GET['section'] = 'PublicMarks';

?>