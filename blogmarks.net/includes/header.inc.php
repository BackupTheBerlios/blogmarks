<div id="header">

	<div id="top">

		<h1>BlogMarks.net</h1>

		<h2>Stop bookmarking. Start blogmarking !</h2>

		<form id="search" method="GET" >

			Search : <input type="text" name="q" size="40" value="<?php if ( isset($_GET['q']) )  echo $_GET['q'] ?>" />
			<br />
			Tags : <input type="text" name="include_tags" size="40" value="<?php if ( isset($_GET['include_tags']) )  echo $_GET['include_tags'] ?>" />

			<input type="submit" value="Search" />
            
            <!-- 

			<br />

			<b>In</b> : 

			<input class="checkbox" type="radio" name="checkSearch" value="0" <?php if ( isset( $_GET['checkSearch'] ) AND  $_GET['checkSearch'] == 0 ) echo 'checked="checked"'; ?> /> Title
			<input class="checkbox" type="radio" name="checkSearch" value="1" <?php if ( isset( $_GET['checkSearch'] ) AND  $_GET['checkSearch'] == 1 ) echo 'checked="checked"'; ?> /> Summary
			<input class="checkbox" type="radio" name="checkSearch" value="2" <?php if ( !isset( $_GET['checkSearch'] ) OR ( isset( $_GET['checkSearch'] ) AND ( $_GET['checkSearch'] == 2 ) ) ) echo 'checked="checked"'; ?>  /> Both
            
            -->

            <input type="hidden" name="checkSearch" value="2" <?php if ( !isset( $_GET['checkSearch'] ) OR ( isset( $_GET['checkSearch'] ) AND ( $_GET['checkSearch'] == 2 ) ) ) echo 'checked="checked"'; ?>  />

			
			
			<?php

			if ( isset( $_GET['private'] ) )
				echo '<input type="hidden" name="private" value="' . $_GET['private'] . '" />';

			if ( isset( $_GET['all'] ) )
				echo '<input type="hidden" name="all" value="' . $_GET['all'] . '" />';

			?>

		</form>

	</div> <!-- /#top -->

	<div id="login">

	<?php

	include_once "includes/functions.inc.php";

   // echo "'".print_r( $marker->getuserinfo() ) . "'";

	if ( $marker->userIsAuthenticated() ) {

   // if ( 1 == 1 ) {

		$USER = $marker->getUserInfo('login');

		echo $USER ;

		echo '<p><strong>Auth OK</strong></p>';

		//echo '<p>Lorem<br />Ipsum</p>';

		echo '<p><a href="?disconnect=1">Disconnect</a></p>';

	} else {

	if ( isset( $auth_error ) ) echo '<p class="error">' . $auth_error . '</p>';
		
	?>

		<form method="POST" action="?connect=1">

			<label>Username :</label>
			<input type="text" name="login">
			<label>Password : <span style="font-size:9px">(<a href="#">forgot?</a>)</span></label>
			<input type="password" name="pwd">

			<input type="submit" value="Sign in" />

			<a href="create_account.php">New User ?</a>

		</form>

	<?php } ?>

	</div> <!-- /#login -->

</div> <!-- /#header -->