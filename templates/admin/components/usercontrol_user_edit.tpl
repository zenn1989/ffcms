<p>{$lang::admin_component_usercontrol_edit_text_prev}</p>
{$notify}
<form method="post" action="" class="form-horizontal">
	<fieldset>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_main}</label>
	<div class="controls">
	<div class="input-prepend">
	  <span class="add-on">id</span>
	  <input type="text" class="input input-small" value="{$target_user_id}" disabled> 
	</div>
	<div class="input-prepend">
	  <span class="add-on">login</span>
	  <input type="text" class="input input-small" value="{$target_user_login}" disabled>
	</div>
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_nick}</label>
	<div class="controls">
		<input type="text" name="nick" value="{$target_user_nick}" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_sex}</label>
	<div class="controls">
		<input type="text" name="sex" value="{$target_user_sex}" />
		<p class="help-block">{$lang::admin_component_usercontrol_edit_desc_sex}</p>
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_phone}</label>
	<div class="controls">
		<input type="text" name="phone" value="{$target_user_phone}" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_url}</label>
	<div class="controls">
		<input type="text" name="webpage" value="{$target_user_webpage}" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_birthday}</label>
	<div class="controls">
		<input type="text" name="birthday" value="{$target_user_birthday}" />
		<p class="help-block">{$lang::admin_component_usercontrol_edit_desc_birthday}</p>
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_status}</label>
	<div class="controls">
		<input type="text" name="status" value="{$target_user_status}" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_group}</label>
	<div class="controls">
		<select name="groupid">
			{$option_group_prepare}
		</select>
	</div>
</div>
<div class="control-group">
	<label class="control-label">{$lang::admin_component_usercontrol_edit_label_newpwd}</label>
	<div class="controls">
		<input type="text" name="newpass" value="" />
		<p class="help-block">{$lang::admin_component_usercontrol_edit_desc_newpwd}</p>
	</div>
</div>
<div class="alert alert-warning">{$lang::admin_component_usercontrol_edit_warning_notify}</div>
<input type="submit" name="submit" class="btn btn-success" value="{$lang::admin_component_usercontrol_edit_button_success}" /> <input type="submit" name="submit" class="btn btn-info" value="{$lang::admin_component_usercontrol_edit_button_cancel}" />
</fieldset>
</form>