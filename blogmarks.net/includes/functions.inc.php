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

?>