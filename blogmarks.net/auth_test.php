<?php

require_once 'PEAR.php';
include_once 'Blogmarks/Marker.php';

include "includes/functions.inc.php";

$marker =& Blogmarks_Marker::singleton();

if ( isset( $_POST['login'] ) AND strlen( $_POST['login'] ) ) {

	$nonce = microtime() / rand();
	$time = date( "YMDHMS" );

	$digest = makeDigest( $_POST['pwd'] , $nonce, $time );

	$auth = $marker->authenticate( $_POST['login'] , $digest, $nonce, $time, TRUE );

} 

if ( isset( $_GET['disconnect'] ) AND ( $_GET['disconnect'] == 1 ) ) {

	$marker->disconnectUser();

}


if ( $marker->userIsAuthenticated() ) {

	echo '<p>Vous êtes authentifiés</p>';

	echo '<p><a href="?disconnect=1">Se déconnecter</a></p>';


} else {

	echo "<p>Vous n'êtes pas du tout authentifiés</p>";
	
	echo '<FORM METHOD="POST" ACTION="">';

	echo '<INPUT TYPE="text" NAME="login">';
	echo '<INPUT TYPE="password" NAME="pwd">';
	echo '<INPUT TYPE="submit">';

	echo '</FORM>';
}

?>