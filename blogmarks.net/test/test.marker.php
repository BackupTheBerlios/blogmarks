<pre>
<?php
/** Description du fichier.
 * Détails.
 *
 * $Id: test.marker.php,v 1.4 2004/06/25 15:57:25 benfle Exp $
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
$user = 'mbertier';
$pwd = 'moustache';
$digest = makeDigest( $pwd, $nonce, $time );

include_once 'Blogmarks/Marker.php';

$marker =& Blogmarks_Marker::singleton();

$auth = $marker->authenticate( $user, $digest, $nonce, $time, false );

// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

    $params = array(  
                    'user_login' => 'znarf', 
                    'order_by' => array('created', 'DESC'), 
                    //'include_tags' => array( 'blog') , 
                    'select_priv' => true );

    $e = $marker->createMark( array( 'title'  => 'StandBlog',
                                     'tags'   => array('standards', 'blog', 'fr'),
                                     'href'   => 'http://www.standblog.com',
                                     'via'    => 'http://dev.upian.com/hotlinks',
                                     'source' => 'http://upian.net/znarf/carnet') );

 
    if ( Blogmarks::isError($e) ) echo( $e->getMessage() . "\n\n");
    else echo "Mark [$e->id] ajouté avec succès :)\n"; 


    $list =& $marker->getMarksList( $params );
                                          
    if ( Blogmarks::isError($list) ) echo $list->getMessage() . "\n\n";

    echo "Récupération des Marks de znarf : \n";
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