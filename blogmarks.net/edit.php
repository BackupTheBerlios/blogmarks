<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";

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

<h3>Edit a bookmark</h3>

<form method="POST" action="update.php">

<?php

$mark =& $marker->getMark( $_GET['id'] );

//print_r( $mark );

$string_tags = implode(' ', $mark->getTags() ); 

$link = $mark->getLink( 'href' );
$via  = $mark->getLink( 'via' );

//$href = $mark->getHref();

//print_r( $mark );

?>
	<input name="id" type="hidden" value="<?php echo $_GET['id'] ?>" />
	<label>title</label>
	<input value="<?php echo $mark->title ?>" type="text"  name="title" size="65"  maxlength="255"  />
	<label>url</label>
	<input value="<?php echo $link->href ?>" type="text"  name="url" size="65"  maxlength="255"  />
	<label>description</label>
	<textarea name="description" rows="2" cols="61"><?php echo $mark->summary ?></textarea>
<!-- 	<input value="<?php echo $mark->summary ?>" type="text"  name="description" size="65"  maxlength="255"  /> -->
	<label>tags</label>
	<input value="<?php echo implode(" ", $mark->getTags() ) ?>" type="text"  name="tags" size="65"  maxlength="255"  />
	<label>via</label>
	<input value="<?php echo $via->href ?>" type="text"  name="via" size="65"  maxlength="255"  />

	<input value="<?php echo $_GET['mini'] ?>" type="hidden"  name="mini"  />
	
	<input class="submit" type="submit" value="Update" />

</form>

<?php include 'includes/footer.inc.php' ?>

</div> <!-- # conteneur -->

</body></html>