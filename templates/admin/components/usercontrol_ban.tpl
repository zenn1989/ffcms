<p>{$lang::admin_component_usercontrol_ban_text_p_info}</p>
{$notify}
<h5>{$lang::admin_component_usercontrol_ban_h5_reg_title}</h5>
<p>{$lang::admin_component_usercontrol_ban_reg_desc}</p>
<form method="post" action="" class="form-horizontal">
	<fieldset>
		<div class="control-group">
		<label class="control-label">{$lang::admin_component_usercontrol_ban_label_userdata}</label>
		<div class="controls">
			<input type="text" class="input-large" name="userdata" placeholder="27" />
			<p class="help-block">{$lang::admin_component_usercontrol_ban_help_loginid}</p>
		</div>
		</div>
		<div class="control-group">
		<div class="controls">
			<input type="submit" name="idorloginblock" value="{$lang::admin_component_usercontrol_ban_regsearch_button}" class="btn btn-success" />
		</div>
		</div>
	</fieldset>
</form>
{$continue_block}
<h5>{$lang::admin_component_usercontrol_ban_h5_ip_info}</h5>
<p>{$lang::admin_component_usercontrol_ban_ip_desc}</p>
<form method="post" action="" class="form-horizontal">
	<fieldset>
		<div class="control-group">
		<label class="control-label">{$lang::admin_component_usercontrol_ban_label_userdata}</label>
		<div class="controls">
			<input type="text" class="input-large" name="userip" placeholder="127.0.0.1" />
			<p class="help-block">{$lang::admin_component_usercontrol_ban_help_ip}</p>
		</div>
		</div>
		<div class="control-group">
		<label class="control-label">{$lang::admin_component_usercontrol_ban_label_endtime}</label>
		<div class="controls">
			<input type="text" class="input-large" name="enddate" placeholder="2020-10-25" />
			<p class="help-block">{$lang::admin_component_usercontrol_ban_help_endtime}</p>
		</div>
		</div>
		<div class="control-group">
		<div class="controls">
			<input type="submit" name="ipblock" value="{$lang::admin_component_usercontrol_ban_button_red}" class="btn btn-danger" />
		</div>
		</div>
	</fieldset>
</form>