<pre>
<?php
/** Description du fichier.
 * Détails.
 *
 * $Id: test.marker.php,v 1.2 2004/04/28 15:39:40 cortexfh Exp $
 */
//ini_set( 'include_path', '/home/mbertier/dev/PEAR_OVERLAY/Blogmarks' . ':'. ini_get('include_path') . ':/home/mbertier/dev/PEAR_OVERLAY' );  
ini_set( 'error_reporting', E_ALL );

require_once 'PEAR.php';

/*
$config = parse_ini_file('/home/mbertier/dev/PEAR_OVERLAY/Blogmarks/Blogmarks/config.ini', true);
foreach($config as $class=>$values) {
    $options = &PEAR::getStaticProperty($class,'options');
    $options = $values;
}
*/


# -- CONFIGURATION
$nonce = microtime() / rand();
$time = date( "YMDHMS" );
$user = 'benoit';
$pwd = '170381';
$digest = makeDigest( $pwd, $nonce, $time );

include_once 'Blogmarks/Marker.php';

$marker =& Blogmarks_Marker::singleton();

$auth = $marker->authenticate( $user, $digest, $nonce, $time, false );

// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

    $list =& $marker->getMarksList( array('user_login'    => 'znarf' , 'select_priv' => true ) );
                                          
    

    if ( Blogmarks::isError($list) ) die( $list->getMessage() );

    if ( DB::isError($list) ) die( $list->getMessage() );

    echo "Récupération des Marks de Znarf : \n";
    while ( $list->fetch() ) {
        echo "\n ** $list->title \n";
        foreach ( $list->getTags() as $tag) echo " [$tag] ";
        echo "\n";
    }
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
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
</pre>