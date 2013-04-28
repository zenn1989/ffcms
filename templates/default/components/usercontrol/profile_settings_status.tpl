{$notify_messages}
<form method="post" action="" class="form-horizontal">
<fieldset>
<h3>Настройка статуса</h3>
<p>В данном разделе вы можете обновить ваш пользовательский статус</p>
<hr />
<input type="text" class="input-block-level" name="newstatus" value="{$user_status}" /><br />
<input type="submit" name="updatestatus" class="btn btn-success pull-right" value="{$lang::global_send_button}" />
</fieldset>
</form>