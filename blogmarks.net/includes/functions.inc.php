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


function dcdate2php($dcdate,$format='j/m/Y @ H:i') {

	$year = substr( $dcdate , 0 , 4 );
	$month = substr( $dcdate , 5 , 2 );
	$day = substr( $dcdate , 8 , 2 );

	$hour = substr( $dcdate , 11 , 2 );
	$minutes = substr( $dcdate , 14 , 2 );
	$seconds = substr( $dcdate , 17 , 2 );

    $plusmoins = substr( $dcdate , 20 , 2 );

    if ( strlen( $plusmoins ) ) {
        
        $timezone = substr( $dcdate , 20 , 2 );
        
        if ( $plusmoins == "+" )
                $hour -= $timezone;
        elseif ( $plusmoins == "-" )
                $hour += $timezone;

    }

	$timestamp = mktime( $hour , $minutes , $seconds , $month , $day , $year );

	$time = date( $format , $timestamp);

	return $timestamp;

}

/** Ecris ds un fichier. */
if (!function_exists('file_put_contents')) {
    define('FILE_APPEND', 1);
    function file_put_contents($filename, $content, $flags = 0) {
        if (!($file = fopen($filename, ($flags & FILE_APPEND) ? 'a' : 'w')))
            return false;
        $n = fwrite($file, $content);
        fclose($file);
        return $n ? $n : false;
    }
 }


?>