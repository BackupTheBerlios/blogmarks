<?php

/** Description du fichier.
 * Détails.
 *
 * $Id: test.marker.php,v 1.1 2004/04/06 17:40:33 cortexfh Exp $
 */
//ini_set( 'error_reporting', E_ALL );

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
    $uri =& $marker->createMark( array( 'href'  => 'http://dev.upian.com/hotlinks/',
                                        'title' => 'HotLinks',
                                        'tags' => array('tool', 'rss') ));

    if ( Blogmarks::isError($uri) ) echo "*** Erreur: ". $uri->getMessage() ."\n";
    else echo "Mark URI: $uri\n";
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
}


//$list =& $marker->getMarksListOfUser( 'benoit', array('fr'), array('linux') );
$list =& $marker->getMarksListOfUser( 'znarf', array('rss'), array('tool') );
if ( Blogmarks::isError($list) ) echo "*** Erreur: ". $list->getMessage() . "\n";
else {
    while ( $list->fetch() ) {
        echo $list->title . "\t|>\t\t" . $list->summary . "\t" ;
        
        echo "[ ";
        foreach ( $list->getTags() as $tag ) echo "$tag ";
        echo "]\n";
    }
}




# -------------------------------------------------------------------------------

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
?>
