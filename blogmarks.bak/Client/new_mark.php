<?php $date = date('Y-m-d').'T'.date('H:i:s+01:00'); ?>
<form method="GET" action="?">
<input type="hidden" name="action" value="post_mark"/>

<div class="col">
<label title="URI du lien">URI du lien :</label>
<input type="text" name="link" size="30"/>
<label title="via">via :</label>
<input type="text" name="via" size="30"/>
<label title="tags publics">tags publics :</label>
<input type="text" name="publicTags" size="30"/>
<label title="tags privés">tags privés :</label>
<input type="text" name="privateTags" size="30"/>
</div>

<div class="col">
<label title="titre">titre :</label>
<input type="text" name="title" size="30"/>
<label title="description">description :</label>
<textarea name="summary"></textarea>
<label title="date de création">date de création :</label>
<input type="text" name="created" value="<?php echo $date; ?>" size="30"/>
<label title="date de publication">date de publication :</label>
<input type="text" name="issued" value="<?php echo $date; ?>" size="30"/>
<label title="langue">langue :</label>
<input type="text" name="lang" value="fr" size="30"/>
</div>

<div class="col">
<label title="login">login :</label>
<input type="text" name="name" size="30"/>
<label title="email">email :</label>
<input type="text" name="email" size="30"/>
<label title="mot de passe">mot de passe :</label>
<input type="password" name="pass" size="30"/>
</div>

<input type="submit" value="Poster" />
</form>

