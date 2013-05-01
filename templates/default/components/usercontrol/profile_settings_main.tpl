{$notify_messages}
<form method="post" action="" class="form-horizontal">
<fieldset>
<h3>{$lang::usercontrol_profile_settings_title_public}</h3>
<p>{$lang::usercontrol_profile_settings_desc_public}</p>
<hr />
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_name_title}</label>
		<div class="controls">
			<input type="text" name="nickname" value="{$user_nickname}" />
			<p class="help-block">{$lang::usercontrol_profile_settings_label_name_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_phone_title}</label>
		<div class="controls">
			<input type="text" name="phone" value="{$user_phone}" />
			<p class="help-block">{$lang::usercontrol_profile_settings_label_phone_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_birthday_title}</label>
		<div class="controls">
			<select name="bitrhday[day]" class="input-small">
			{$option_day}
			</select>
			<select name="bitrhday[month]" class="input-small">
			{$option_month}
			</select>
			<select name="bitrhday[year]" class="input-small">
			{$option_year}
			</select>
			<p class="help-block">{$lang::usercontrol_profile_settings_label_birthday_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_sex_title}</label>
		<div class="controls">
			<select name="sex">{$option_sex}</select>
			<p class="help-block">{$lang::usercontrol_profile_settings_label_sex_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_website_title}</label>
		<div class="controls">
			<input type="text" name="website" value="{$user_website}" />
			<p class="help-block">{$lang::usercontrol_profile_settings_label_website_desc}</p>
		</div>
	</div>
<h3>{$lang::usercontrol_profile_settings_title_password}</h3>
<p>{$lang::usercontrol_profile_settings_desc_password}</p>
<hr />
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_oldpwd_title}</label>
		<div class="controls">
			<input type="password" name="oldpwd" value="" />
			<p class="help-block">{$lang::usercontrol_profile_settings_label_oldpwd_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_newpwd_title}</label>
		<div class="controls">
			<input type="password" name="newpwd" value="" />
			<p class="help-block">{$lang::usercontrol_profile_settings_label_newpwd_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{$lang::usercontrol_profile_settings_label_renewpwd_title}</label>
		<div class="controls">
			<input type="password" name="renewpwd" value="" />
			<p class="help-block">{$lang::usercontrol_profile_settings_label_renewpwd_desc}</p>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<input type="submit" name="saveprofile" value="{$lang::global_send_button}" class="btn btn-success" />
		</div>
	</div>
	</fieldset>
</form>