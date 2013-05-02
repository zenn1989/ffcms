<div class="span2 centered">
<a href="{$url}/user/id{$target_user_id}"><strong>{$nick}</strong></a><br />
<img src="{$url}/upload/user/avatar/small/{$avatar}" /><br />
<form action="" method="post">
<input type="hidden" name="target_id" value="{$target_user_id}" />
<input type="submit" name="acceptfriend" value="{$lang::global_accept_button}" class="btn btn-success btn-small" />
<input type="submit" name="cancelfriend" value="{$lang::global_reject_button}" class="btn btn-danger btn-small" />
</form>
</div>