<?php

include_once "includes/functions.inc.php";
include "includes/start.inc.php";

//echo $_POST['id'];

// AUTH OK :)
if ( $marker->userIsAuthenticated() ) {

    echo "Connexion OK!\n";

	//$array_tags = explode( " " , $_POST['tags'] );

    $result =& $marker->deleteMark( $_GET['id'] );

	if ( Blogmarks::isError($result) ) die( $result->getMessage() );
	if ( DB::isError($result) ) die( $result->getMessage() );

}

// WRONG AUTH :(
else {
    echo "Pas connecté\n";
}

if ( $_GET['from'] == 'popupjs' ) {

	echo '<a onclick="window.close()" href="#">[close]</a>';

}



?>

<script type="text/javascript">

window.focus();

</script>