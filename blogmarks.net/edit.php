<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Blogmarks.net</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" title="default" type="text/css" href="style2.css" media="all"  />

</head>

<body>

<div id="conteneur">

<div id="nav">

<a href="index.php">Home</a> 
				| <a href="/tags">Tags</a> 
				| <a href="/invite">Invite</a> 
				| <a href="/friends">Friends</a> 
				| <a href="/search">Search</a> 
				| <a href="/help">Help</a> 
				| <a href="/logout">Log Out</a>

</div>

<h1>BlogMarks.net</h1>

<h2>Stop bookmarking. Start blogmarking !</h2>

<h3>Edit a bookmark</h3>

<form method="POST" action="update.php">

<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";


$mark =& $marker->getMark( $_GET['id'] );


$string_tags = implode(' ', $mark->getTags() ); 

//$href = $mark->getHref();

//print_r( $mark );

?>
	<input name="id" type="hidden" value="<?php echo $_GET['id'] ?>" />
	<label>title</label>
	<input value="<?php echo $mark->title ?>" style="display:block" type="text"  name="title" size="50"  maxlength="255"  />
	<label>url</label>
	<input value="<?php echo $mark->getHref() ?>" style="display:block" type="text"  name="url" size="50"  maxlength="255"  />
	<label>description</label>
	<input value="<?php echo $mark->summary ?>" style="display:block" type="text"  name="description" size="50"  maxlength="255"  />
	<label>tags</label>
	<input value="<?php echo implode(" ", $mark->getTags() ) ?>" style="display:block" type="text"  name="tags" size="50"  maxlength="255"  />
	<label>via</label>
	<input value="<?php echo $mark->via ?>" style="display:block" type="text"  name="via" size="50"  maxlength="255"  />
	<input type="submit" value="Update" />

</form>

<hr />

<p><strong>Powered by</strong> : Mbertier / Benfle / Znarf</p>

</div>

</body></html>