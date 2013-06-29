<h3>{$lang::usercontrol_profile_photochange_title}</h3>
<hr/>
{$notify_message}
<p>{$lang::usercontrol_profile_photochange_text}</p>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="avatarupload"/><br/>
    <input type="submit" name="loadavatar" class="btn btn-success" value="{$lang::global_send_button}"/>
</form>