<?php

include_once "includes/functions.inc.php";
include "includes/start.inc.php";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Blogmarks.net</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" title="default" type="text/css" href="style.css" media="all"  />

</head>

<?php

if ( isset( $_GET['mini'] ) ) 

	echo '<body onload="window.focus()" class="mini">';

else

	echo '<body>';

?>


<div id="conteneur">

<?php include 'includes/header.inc.php' ?>

<h3>Delete a bookmark</h3>

<?php

if ( $marker->userIsAuthenticated() ) {

	if ( isset( $_POST['confirm'] ) AND ( $_POST['confirm'] == 1 ) ) {

   // echo "Connexion OK!\n";

	//$array_tags = explode( " " , $_POST['tags'] );

		$result =& $marker->deleteMark( $_GET['id'] );

		if ( Blogmarks::isError($result) ) die( $result->getMessage() );
		if ( DB::isError($result) ) die( $result->getMessage() );

		echo '<p>Blogmark successfully deleted !</p>';

		if ( $_GET['mini'] == 1 ) {
			echo '<a onclick="window.close()" href="#">[close]</a>';
		}


	} else {

		if ( isset( $_GET['id'] ) ) {

			echo '<p>Click this button if you are sur to delete this blogmark</p>';

			$url = 'delete.php?id=' . $_GET['id'];

			if ( isset( $_GET['mini'] ) ) 
				$url .= '&amp;mini=1';

			echo '<form method="POST" action="">' . "\r\n";

			echo '<input type="hidden" name="confirm" value="1" />' . "\r\n";

			echo '<input type="submit" value="DELETE THIS" />' . "\r\n";

			echo '</form>';

			}
	}

}

// WRONG AUTH :(
else {
    echo "Pas connecté\n";
}

?>

<?php include 'includes/footer.inc.php' ?>

<script type="text/javascript">

window.focus();

</script>

</div> <!-- # conteneur -->

</body></html>