<?php

include_once "includes/functions.inc.php";
include "includes/start.inc.php";

echo $_POST['id'];
// AUTH OK :)
if ( ! Blogmarks::isError( $auth ) ) {

    echo "Connexion OK!\n";

	$array_tags = explode( " " , $_POST['tags'] );

    $result =& $marker->deleteMark( $_GET['id'] );

    if ( Blogmarks::isError($uri) ) echo "<p><b>Erreur !!</b><br>code : ".$uri->getCode()."<br>message : ". $uri->getMessage() ."</p>\n";
    else {
		echo "DELETE OK:";
		print_r( $result) ;
	}
}

// WRONG AUTH :(
else {
    echo "*** Erreur : " . $auth->getMessage() . "\n";
}

if ( $_GET['from'] == 'popupjs' ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

}



?>

<script type="text/javascript">

window.focus();

</script>