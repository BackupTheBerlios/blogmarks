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
<script type="text/javascript" src="behavior.js"></script>

</head>

<body>

<div id="conteneur">

<?php include "includes/header.inc.php" ?>

<?php

if ( isset($_POST['create']) AND $_POST['create'] == 1 ) {
	
	$_POST['login']	= trim( $_POST['login'] );
	$_POST['pwd1']	= trim( $_POST['pwd1'] );
	$_POST['pwd2']	= trim( $_POST['pwd2'] );

	if ( strlen( $_POST['login'] ) < 4 ) {
		echo '<p class="error">Login must be minimum 5 characters</p>';
	}
	elseif ( strlen( $_POST['pwd1'] ) < 4 ) {
		echo '<p class="error">Pwd must be minimum 5 characters</p>';
	}
	elseif ( $_POST['pwd1'] != $_POST['pwd2'] ) {
		echo '<p class="error">Password are not identics</p>';
	} else {

		$params = array(
			'login'	=> $_POST['login'] ,
			'pwd'	=> $_POST['pwd1']  ,
			'email'	=> $_POST['email']
		);

		//print_r( $params );
		$result =& $marker->createUser( $params );

		if ( Blogmarks::isError($result) ) die( $result->getMessage() );
		if ( DB::isError($result) ) die( $result->getMessage() );

		echo '<p>Account successfully <b>' . $_POST['login']  . '</b>  created</p>';

		echo '<p><a	href="index.php">Return home</a></p>';

	}


} else {

?>

<form method="POST">

<label>Login</label>
<input type="text" name="login" />
<label>Password</label>
<input type="password" name="pwd1" />
<label>Repeat password</label>
<input type="password" name="pwd2" />

<label>E-mail adress</label>
<input type="text" name="email" />

<input type="hidden" name="create" value="1" />

<input type="submit" value="Create account" />

</form>

<?php } ?>

<?php include "includes/footer.inc.php" ?>

</div> <!-- /#conteneur -->

</body></html>