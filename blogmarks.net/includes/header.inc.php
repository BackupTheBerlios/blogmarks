<div id="header">

	<div id="top">

		<h1>
        
        <a href="index.php">BlogMarks.net</a>

        <?php if( isset ( $_GET['section'] ) ) echo ' : ' . $_GET['section'] ?>
        
        </h1>

		<!--
        <h2>Stop bookmarking. Start blogmarking !</h2>
        -->

		<form id="search" method="GET" >
            
            <table>
            
            <tr>
			    <td>Search</td>
                <td><input type="text" name="q" size="40" value="<?php if ( isset($_GET['q']) )  echo $_GET['q'] ?>" /></td>
            </tr>

			<tr>
                <td>Tags</td>
                <td><input type="text" name="include_tags" size="40" value="<?php if ( isset($_GET['include_tags']) )  echo $_GET['include_tags'] ?>" /></td>
            </tr>
            <tr>
                <td align="center" colspan="2">

			<input  style="vertical-align:-60%" type="submit" value="Search" />  <a class="smaller" href="?section=AdvancedSearch" title="To specify more parameters">Advanced Search</a>

                </td>
            </tr>
            </table>
		
			<input type="hidden" name="section" value="<?php if ( isset ( $_GET['section'] ) ) echo $_GET['section']; else echo 'PublicMarks'; ?>" /> 

		</form>

		

	</div> <!-- /#top -->

	<div id="login">

		<?php

		//include_once "includes/functions.inc.php";

		if ( $marker->userIsAuthenticated() ) {
			
			$user = $marker->getUserInfo('login');
			
            $userIsAuthenticated = TRUE; //hack;
			echo $user;
			echo '<p><strong>Auth OK</strong></p>';
			echo '<p><a href="?disconnect=1">Disconnect</a></p>';

		} else {

            $userIsAuthenticated = FALSE; //hack;

			if ( isset( $auth_error ) ) 
				echo '<p class="error">' . $auth_error . '</p>';
			?>

			<form method="POST" action="?connect=1">

				<label>Username :</label>
				<input type="text" name="login">
				<label>Password : </label>
				<input type="password" name="pwd">

				<input type="submit" value="Sign in" />

				<a class="smaller" href="create_account.php" title="Create a new account on Blogmarks.net">New User ?</a> <a class="smaller"  href="#">Forgot Password ?</a>

			</form>

			

	<?php }
    
    ?>

	</div> <!-- /#login -->

    <div id="menu">
		<ul>
			<li><a href="index.php?section=PublicMarks" title="List of recent marks">PublicMarks</a></li>
            <?php if ( $userIsAuthenticated ) : ?>
			<li><a href="index.php?section=MyMarks" title="Manage your marks">MyMarks</a></li>
            <?php endif ?>
			<!-- <li><a href="index.php?section=MyHotlinks" title="Aggregation">MyHotlinks</a></li> !-->
		</ul>
	</div> <!-- /#menu -->

</div> <!-- /#header -->