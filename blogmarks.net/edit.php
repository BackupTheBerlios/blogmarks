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

<div id="content">

<h3>Edit a bookmark</h3>

<form method="POST" action="update.php<?php if ( isset( $_GET['mini'] ) ) echo '?mini=1' ?>">

<?php

$mark =& $marker->getMark( $_GET['id'] );

if ( Blogmarks::isError($mark) ) die( $mark->getMessage() );

if ( DB::isError($mark) ) die( $mark->getMessage() );

//print_r( $mark );

$link = $mark->getLink( 'related' );
$via  = $mark->getLink( 'via' );

//$href = $mark->getHref();

//print_r( $mark );

?>
	<input name="id" type="hidden" value="<?php echo $_GET['id'] ?>" />

	<label>Title</label>
	<input value="<?php echo $mark->title ?>" type="text"  name="title" size="65"  maxlength="255"  />

	<label>URL</label>
	<input value="<?php echo $link->href ?>" type="text"  name="url" size="65"  maxlength="255"  />

	<label>Description</label>
	<textarea name="description" rows="2" cols="60"><?php echo $mark->summary ?></textarea>

	<label>Tags</label>
	<?php
	$tags_id = $marker->getTags($mark->id);
	$tags_title = array();
	foreach ( $tags_id as $tag_id )
	{
		$tag = $marker->getTag ($tag_id);
		if ( $tag->author != NULL )
			$tag->title = 'private:' . $tag->title;
		$tags_title[] = $tag->title;
	}
	?>
	<input value="<?php echo implode(" ", $tags_title) ?>" type="text"  name="tags" size="65"  maxlength="255"  />

	<label>Via</label>
	<input value="<?php if ( isset ( $via->href ) ) echo $via->href; ?>" type="text"  name="via" size="65"  maxlength="255"  />

	<!-- <input value="<?php echo $_GET['mini'] ?>" type="hidden"  name="mini"  /> -->
	
	<input class="submit" type="submit" value="Update" />

</form>

</div> <!-- # content -->

<?php include 'includes/footer.inc.php' ?>

</div> <!-- # conteneur -->

</body></html>