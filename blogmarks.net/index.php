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

<a href="/">Home</a> 
				| <a href="/tags">Tags</a> 
				| <a href="/invite">Invite</a> 
				| <a href="/friends">Friends</a> 
				| <a href="/search">Search</a> 
				| <a href="/help">Help</a> 
				| <a href="/logout">Log Out</a>

</div>

<h1>BlogMarks.net</h1>

<h2>Stop bookmarking. Start blogmarking !</h2>

<form id="search">
<input type="text"  name="q" size="50" />
<input type="submit" value="Search" />
</form>

<ul>

<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";

$list =& $marker->getMarksListOfUser( "znarf" );

 while ( $list->fetch() ) {
		
		echo '<li>';

		echo '<a href="' . $list->bm_Links_id . '">' . $list->title . '</a>' . ' : ' . $list->summary;
       // echo $list->title . "\t|>\t\t" . $list->summary . "\t" ;

	   //echo ' (' . $list->created . ')';
        
        echo " [ ";
        foreach ( $list->getTags() as $tag ) echo "$tag ";
        echo "]\n";

		echo '<a href="EDIT">edit</a>';

		echo '</li>';
    }

?>

</ul>

<h3>1er avril 2004</h3>

<ul>
<li>D</li>
<li>fffd</li>
<li>fdfdfg</li>
<li>efdt</li>
<li></li>
</ul>

<h3>12 mars 2004</h3>

<ul>
<li></li>
<li></li>
<li></li>
<li></li>
<li></li>
</ul>

<hr />

<h3>Add a bookmark</h3>

<form method="POST" action="add/">

	<label>title</label>
	<input style="display:block" type="text"  name="title" size="50"  maxlength="255"  />
	<label>url</label>
	<input style="display:block" type="text"  name="url" size="50"  maxlength="255"  />
	<label>description</label>
	<input style="display:block" type="text"  name="description" size="50"  maxlength="255"  />
	<label>via</label>
	<input style="display:block" type="text"  name="via" size="50"  maxlength="255"  />
	<input type="submit" value="Add" />

</form>

<hr />

<p><strong>Powered by</strong> : Mbertier / Benfle / Znarf</p>

</div>

</body></html>