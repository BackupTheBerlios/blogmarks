<?php

/***/
function hex2bin($data) {
    $len = strlen($data);
    return pack("H" . $len, $data);
}


/***/
function makeDigest( $pwd, $nonce, $timestamp  ) {
    
    $txt = sha1($nonce.$timestamp.md5($pwd));
    $digest = base64_encode( hex2bin($txt) );
    
    return $digest;
    
}    


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

// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

    echo "Connexion OK!\n";
    $uri =& $marker->createMark( array( 'href'		=> $_POST['url'] ,
                                        'title'		=> $_POST['title'] ,
										'summary'	=> $_POST['description']
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