<?php $date = date('Y-m-d').'T'.date('H:i:s+01:00'); ?>
<form method="GET" action="?">
<input type="hidden" name="action" value="post_tag"/>

<div class="col">
<label title="titre">titre :</label>
<input type="text" name="title" size="30"/>
<label title="description">description :</label>
<textarea name="summary"></textarea>
<label title="langue">langue :</label>
<input type="text" name="lang" value="fr" size="30"/>
<label title="tag parent">tag parent :</label>
<input type="text" name="subTagOf" size="30"/>
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