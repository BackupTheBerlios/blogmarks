<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Blogmarks.net</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" title="default" type="text/css" href="style2.css" media="all"  />
<script type="text/javascript" src="lib.js"></script>

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

	//	print_r( $list->getHref() );

		echo '<a href="' .  $list->getHref() . '">' . $list->title . '</a>' . ' : ' . $list->summary;
       // echo $list->title . "\t|>\t\t" . $list->summary . "\t" ;

	   //echo ' (' . $list->created . ')';
        
        echo " [ ";
        foreach ( $list->getTags() as $tag ) echo "$tag ";
        echo "]\n";

		echo '<a onclick="return Edit(this.href)"  href="edit.php?id=' . $list->id . '">edit</a>';
		echo ' ';
		echo '<a onclick="return confirmDelete(this.href)" href="delete.php?id=' . $list->id . '">delete</a>';

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

<form method="POST" action="add.php">

	<label>title</label>
	<input style="display:block" type="text"  name="title" size="50"  maxlength="255"  />
	<label>url</label>
	<input style="display:block" type="text"  name="url" size="50"  maxlength="255"  />
	<label>description</label>
	<input style="display:block" type="text"  name="description" size="50"  maxlength="255"  />
	<label>tags</label>
	<input style="display:block" type="text"  name="tags" size="50"  maxlength="255"  />
	<label>via</label>
	<input style="display:block" type="text"  name="via" size="50"  maxlength="255"  />
	<input type="submit" value="Add" />

</form>

<hr />

<p><strong>Powered by</strong> : Mbertier / Benfle / Znarf</p>

<p><a href="javascript:Q='';docref='';if (document.all) Q = document.selection.createRange().text;else Q=window.getSelection();if (document.referrer) docref=escape(document.referrer);if (typeof(_ref)!= 'undefined') docref=escape(_ref);void(btw=window.open('http://localhost/bm/blogmarks.net/new_popup.php?&summary='+escape(Q)+'&url='+escape(location.href)+'&title='+escape(document.title)+'&via='+docref+'&mini=1','BlogTHIS','location=no,toolbar=no,scrollbars=yes,width=350,height=375,left=75,top=175,status=no'));">Advanced fuckin bookmarklet</a></p>

<p><a href="javascript:url=location.href;title=document.title;void( open('http://localhost/bm/blogmarks.net/new_popup.php?url='+escape(url)+'&title='+escape(title),'BlogMarks', 'location=no,toolbar=no,scrollbars=yes,width=350,height=375,left=75,top=175,status=no'));">Simple fuckin bookmarklet</a></p>



</div>

</body></html>