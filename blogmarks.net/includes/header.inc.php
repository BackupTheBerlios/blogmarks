<div id="header">

	<div id="top">

		<h1><a href="http://www.blogmarks.net">BlogMarks.net</a></h1>

		<h2>Stop bookmarking. Start blogmarking !</h2>

		<form id="search" method="GET" >

			Search : <input type="text" name="q" size="40" value="<?php if ( isset($_GET['q']) )  echo $_GET['q'] ?>" />
			<br />
			Tags : <input type="text" name="include_tags" size="40" value="<?php if ( isset($_GET['include_tags']) )  echo $_GET['include_tags'] ?>" />

			<input type="submit" value="Search" />
		
			<input type="hidden" name="section" value="<?php if ( isset ( $_GET['section'] ) ) echo $_GET['section']; else echo 'PublicMarks'; ?>" />

		</form>

		<a href="?section=AdvancedSearch" title="To specify more parameters">Advanced Search</a>

	</div> <!-- /#top -->

	<div id="login">

		<?php
		include_once "includes/functions.inc.php";
		if ( $marker->userIsAuthenticated() ) {

			$user = $marker->getUserInfo('login');
			echo $user;
			echo '<p><strong>Auth OK</strong></p>';
			echo '<p><a href="?disconnect=1">Disconnect</a></p>';

		} else {

			if ( isset( $auth_error ) ) 
				echo '<p class="error">' . $auth_error . '</p>';
			?>

			<form method="POST" action="?connect=1">

				<label>Username :</label>
				<input type="text" name="login">
				<label>Password : </label>
				<input type="password" name="pwd">

				<input type="submit" value="Sign in" />

				<a href="create_account.php" title="Create a new account on Blogmarks.net">New User ?</a>

			</form>

			<a href="#">Forgot Password</a>

	<?php } ?>

	</div> <!-- /#login -->

	<div id="menu">
		<ul>
			<li><a href="index.php?section=PublicMarks" title="List of recent marks">PublicMarks</a></li>
			<li><a href="index.php?section=MyMarks" title="Manage your marks">MyMarks</a></li>
			<!-- <li><a href="index.php?section=MyHotlinks" title="Aggregation">MyHotlinks</a></li> !-->
		</ul>
	</div>

</div> <!-- /#header -->