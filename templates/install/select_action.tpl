<div style="text-align: center;"><form action="" class="form-inline"><input type="hidden" name="action" value="lang" /> {$lang::install_switch_language}: <select name="lang">{$language_options}</select> <input type="submit" class="btn" value="{$lang::install_submit}" /></form></div>
<a href="?action=install" class="btn btn-danger btn-block">{$lang::install_mainahref}</a> <br />
<a href="?action=update" class="btn btn-warning btn-block">{$lang::install_updatehref}</a><br />
<p>{$lang::install_switch_desc}: </p>
<ul>
    <li>{$lang::install_switch_install}</li>
    <li>{$lang::install_switch_update}</li>
</ul>