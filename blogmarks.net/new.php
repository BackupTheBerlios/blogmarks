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

	echo '<body class="mini">';

else

	echo '<body>';

?>


<div id="conteneur">

<?php

include "includes/header.inc.php";

?>

<h3>Add a bookmark</h3>

<form method="POST" action="add.php<?php if ( isset( $_GET['mini'] ) ) echo '?mini=1' ?>">
	
	
	<label>title</label>
	<input value="<?php if ( isset( $_GET['title'] ) ) echo utf8_encode( $_GET['title'] ) ?>" style="display:block" type="text"  name="title" size="65"  maxlength="255"  />
	<label>url</label>
	<input value="<?php if ( isset( $_GET['url'] ) ) echo $_GET['url'] ?>"  style="display:block" type="text"  name="url" size="65"  maxlength="255"  />
	<label>description</label>
	<textarea name="description" rows="2" cols="60"><?php if ( isset( $_GET['summary'] ) ) echo utf8_encode( $_GET['summary'] ) ?></textarea>
	<label>tags</label>
	<input style="display:block" type="text"  name="tags" size="65"  maxlength="255"  />
	<label>via</label>
	<input value="<?php if ( isset( $_GET['via'] ) ) echo $_GET['via'] ?>" style="display:block" type="text"  name="via" size="65"  maxlength="255"  />

<!-- 	<input type="hidden" name="from" value="popupbookmarklet" /> -->
	<input type="submit" value="Add" />

</form>

<?php include 'includes/footer.inc.php' ?>

<script type="text/javascript">

window.focus();

</script>

</div>  <!-- # conteneur -->

</body></html>