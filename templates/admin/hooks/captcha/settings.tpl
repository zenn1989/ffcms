{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_hook_captcha_settings }}</small></h1>
<hr />
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form method="post" action="" class="form-horizontal">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_hook_captcha_config_type_title }}</label>

            <div class="col-lg-9">
                <select name="cfg:captcha_type" class="form-control">
                    <option value="ccaptcha"{% if config.captcha_type == 'ccaptcha' %} selected{% endif %}>ccaptcha</option>
                    <option value="recaptcha"{% if config.captcha_type == 'recaptcha' %} selected{% endif %}>recaptcha</option>
                </select>
                <p class="help-block">{{ language.admin_hook_captcha_config_type_desc }}</p>
            </div>
        </div>
        {{ settingstpl.textgroup('captcha_publickey', config.captcha_publickey, language.admin_hook_captcha_config_publickey_title, language.admin_hook_captcha_config_publickey_desc ) }}
        {{ settingstpl.textgroup('captcha_privatekey', config.captcha_privatekey, language.admin_hook_captcha_config_privatekey_title, language.admin_hook_captcha_config_privatekey_desc ) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>