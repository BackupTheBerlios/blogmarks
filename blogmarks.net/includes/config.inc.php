<?php

error_reporting(E_ALL ^ E_NOTICE);

require_once 'PEAR.php';

# -- CONFIGURATION
$nonce = microtime() / rand();
$time = date( "YMDHMS" );
$user = 'znarf';
$pwd = 'cuix84ds';

$digest = makeDigest( $pwd, $nonce, $time );

include_once 'Blogmarks/Marker.php';


$marker =& Blogmarks_Marker::singleton();

$auth = $marker->authenticate( $user, $digest, $nonce, $time, false );

?>