<h2>AdvancedSearch</h2>

	<form id="advanced_earch" method="GET">
		Search in : 
		<select name="search_in" size="1">
			<option value="PublicMarks">PublicMarks</option>
			<option value="MyMarks">MyMarks</option>
			<option value="MyHotlinks">MyHotlinks</option>
		</select><br/>
		Search : <input type="text" name="q" size="40" value="<?php if ( isset($_GET['q']) )  echo $_GET['q'] ?>" /><br/>
		Blogmarker : <input type="text" name="author"/><br/>
		Include Tags : <input type="text" name="include_tags" size="40" value="<?php if ( isset($_GET['include_tags']) )  echo $_GET['include_tags'] ?>" /><br/>
		Exclude Tags : <input type="text" name="exclude_tags" size="40" value="<?php if ( isset($_GET['exclude_tags']) )  echo $_GET['exclude_tags'] ?>" /><br/>
		Created before : <input type="text" name="date_in"/><br/>
		Created after : <input type="text" name="date_out"/><br/>
		language : 
		<select name="lang" size="1">
			<option value="fr">fr</option>
			<option value="en">en</option>
		</select><br/>
		<br/>
		Order by : 
		<select name="order_by" size="1">
			<option value="user">user</option>
			<option value="issued">issued</option>
			<option value="created">created</option>
			<option value="modified">modified</option>
			<option value="lang">lang</option>
		</select><br/>
		Order type :
		<select name="order_type" size="1">
			<option value="asc">asc</option>
			<option value="desc">desc</option>
		</select><br/>
		<input type="submit" value="search"/>
	</form>