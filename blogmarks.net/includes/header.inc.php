<div id="header">

<div id="top">

	<h1>BlogMarks.net</h1>

	<h2>Stop bookmarking. Start blogmarking !</h2>

	<form id="search">

		<input type="text" name="q" size="50" value="<?php if ( isset($_GET['q']) )  echo $_GET['q'] ?>" />

		<input type="submit" value="Search" />

		<br />

		<b>In</b> : 

		<input class="checkbox" type="radio" name="checkSearch" value="0"> Title
		<input class="checkbox" type="radio" name="checkSearch" value="1"> Summary
		<input class="checkbox" type="radio" name="checkSearch" value="2" checked="checked"> Both

	</form>

</div> <!-- /#top -->

<div id="login">

<?php

include_once "includes/functions.inc.php";
include_once "includes/config.inc.php";

if ( $marker->userIsAuthenticated() ) {

	echo $marker->getUserInfo('login') ;

	echo '<p><strong>Auth OK</strong></p>';

	echo '<p><a href="?disconnect=1">Se déconnecter</a></p>';

} else { ?>

	<form method="POST">

		<label>Username :</label>
		<input type="text" name="login">
		<label>Password : <span style="font-size:9px">(<a href="#">forgot?</a>)</span></label>
		<input type="password" name="pwd">

		<input type="hidden" name="signin" value="1">

	<!-- 	<br /> -->

		<input type="submit" value="Sign in" />

<!-- 		<br /> -->

		<a href="create_account.php">New User ?</a>

	</form>

<?php } ?>

</div> <!-- /#login -->

</div> <!-- /#header -->