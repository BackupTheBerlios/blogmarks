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

</div> <!-- /#nav -->

<h1>BlogMarks.net</h1>

<h2>Stop bookmarking. Start blogmarking !</h2>

<form id="search">
<input type="text"  name="q" size="50" />
<input type="submit" value="Search" />
</form>



<?php

include "includes/functions.inc.php";
include "includes/config.inc.php";
/*
array( 'user' => $userlogin,
���������'date_in' => 'mysqldate'
����������'date_out => 'mysqldate',
����������'exclude_tags' => array(tags�exclure),
����������'include_tags' �=> array(tags�inclure),
����������'select_priv' �����=> bool,
����������'order_by' ���������=> array('field' ou array('fields'), DESC/ASC) );
*/


$params = array( 
			 'user_login' => 'znarf',
			 'order_by' => array('created', 'DESC'),
	//		'include_tags' => array( 'blog') ,
				 'select_priv' => true );

if ( isset( $_GET['include_tags'] ))  $params['include_tags'] = array( $_GET['include_tags'] );

$list =& $marker->getMarksList( $params );

if ( Blogmarks::isError($list) ) die( $list->getMessage() );

if ( DB::isError($list) ) die( $list->getMessage() );

$i = 0;

$string_date_prev = '';

while ( $list->fetch() ) {


		$timestamp = dcdate2php( $list->created );

		$string_date = date( "j/m/Y" ,  $timestamp );

		if ($string_date_prev != $string_date) {
			if ( $i != 0 ) echo "</ul>";
			echo "<h3>" . $string_date . "</h3><ul>";
		}
		$string_date_prev = $string_date;
		
		echo '<li>';

	//	print_r( $list );

	//		print_r( $list->getHref() );

		echo '<a href="' .  $list->getHref() . '">' . $list->title . '</a>' . ' : ' . $list->summary;
       // echo $list->title . "\t|>\t\t" . $list->summary . "\t" ;

	//	echo ' (' . dcdate2php( $list->created ) . ')';
        
        echo " [ ";
        foreach ( $list->getTags() as $tag ) echo "$tag ";
        echo "]\n";

		echo '<a onclick="return Edit(this.href)"  href="edit.php?id=' . $list->id . '">edit</a>';
		echo ' ';
		echo '<a onclick="return confirmDelete(this.href)" href="delete.php?id=' . $list->id . '">delete</a>';

		echo '</li>';

		$i ++;
    }

?>

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

</div> <!-- /#conteneur -->

</body></html>