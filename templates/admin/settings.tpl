{% import 'macro/notify.tpl' as notify_macro %}
<h1>{{ language.admin_settings_title }}<small>{{ language.admin_settings_list_desc }}</small></h1>
<hr />
{% if notify.saved %}
    {{ notify_macro.success(language.admin_settings_saved) }}
{% endif %}
<form method="post" action="" class="form-horizontal" role="form">
<fieldset>
<h2>{{ language.admin_settings_list_main_block }}</h2>
<hr/>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_url_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text" name="cfgmain:url" value="{{ config.url }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_url_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_tpldir_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:tpl_dir" value="{{ config.tpl_dir }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_tpldir_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_tplname_title }}</label>

    <div class="col-lg-9">
        <select name="cfgmain:tpl_name" class="form-control">
            {% for availabletpl in config.addon.availableThemes %}
                <option value="{{ availabletpl }}"{% if availabletpl == config.tpl_name %} selected{% endif %}>{{ availabletpl }}</option>
            {% endfor %}
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_tplname_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_lang_title }}</label>

    <div class="col-lg-9">
        <select name="cfgmain:lang" class="form-control">
            {% for availablelang in config.addon.availableLang %}
                <option value="{{ availablelang }}"{% if availablelang == config.lang %} selected{% endif %}>{{ availablelang }}</option>
            {% endfor %}
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_lang_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_timezone_title }}</label>
    <div class="col-lg-9">
        <select name="cfgmain:time_zone" class="form-control">
            {% for availablezone in config.addon.availableZones %}
                <option value="{{ availablezone }}"{% if availablezone == config.time_zone %} selected{% endif %}>{{ availablezone }}</option>
            {% endfor %}
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_timezone_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_debug_title }}</label>

    <div class="col-lg-9">
        <select name="cfgmain:debug" class="form-control">
            <option value="0"{% if config.debug == 0 %} selected{% endif %}>{{ language.admin_settings_isoff }}</option>
            <option value="1"{% if config.debug == 1 %} selected{% endif %}>{{ language.admin_settings_ison }}</option>
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_debug_desc }}</p>
    </div>
</div>

<h2>{{ language.admin_settings_list_seo_block }}</h2>
<hr/>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_title_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:seo_title" value="{{ config.seo_title }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_title_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_desc_title }}</label>

    <div class="col-lg-9">
        <textarea name="cfgmain:seo_description" class="form-control">{{ config.seo_description }}</textarea>
        <p class="help-block">{{ language.admin_settings_list_label_desc_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_keywords_title }}</label>

    <div class="col-lg-9">
        <textarea name="cfgmain:seo_keywords" class="form-control">{{ config.seo_keywords }}</textarea>
        <p class="help-block">{{ language.admin_settings_list_label_keywords_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_multititle_title }}</label>

    <div class="col-lg-9">
        <select name="cfgmain:multi_title" class="form-control">
            <option value="0"{% if config.multi_title == 0 %} selected{% endif %}>{{ language.admin_settings_isoff }}</option>
            <option value="1"{% if config.multi_title == 1 %} selected{% endif %}>{{ language.admin_settings_ison }}</option>
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_multititle_desc }}</p>
    </div>
</div>
<h2>{{ language.admin_settings_list_token_block }}</h2>
<hr/>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_cache_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:cache_interval" value="{{ config.cache_interval }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_cache_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_token_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:token_time" value="{{ config.token_time }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_token_desc }}</p>
    </div>
</div>
<h2>{{ language.admin_settings_list_mail_block }}</h2>
<hr/>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_from_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:mail_from" value="{{ config.mail_from }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_from_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_nick_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:mail_ownername" value="{{ config.mail_ownername }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_nick_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_smtpuse_title }}</label>

    <div class="col-lg-9">
        <select name="cfgmain:mail_smtp_use" class="form-control">
            <option value="0"{% if config.mail_smtp_use == 0 %} selected{% endif %}>{{ language.admin_settings_isoff }}</option>
            <option value="1"{% if config.mail_smtp_use == 1 %} selected{% endif %}>{{ language.admin_settings_ison }}</option>
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_smtpuse_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_smtphost_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:mail_smtp_host" value="{{ config.mail_smtp_host }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_smtphost_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_smtpport_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:mail_smtp_port" value="{{ config.mail_smtp_port }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_smtpport_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_smtpauth_title }}</label>

    <div class="col-lg-9">
        <select name="cfgmain:mail_smtp_auth" class="form-control">
            <option value="0"{% if config.mail_smtp_auth == 0 %} selected{% endif %}>{{ language.admin_settings_isoff }}</option>
            <option value="1"{% if config.mail_smtp_auth == 1 %} selected{% endif %}>{{ language.admin_settings_ison }}</option>
        </select>
        <p class="help-block">{{ language.admin_settings_list_label_smtpauth_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_smtplogin_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:mail_smtp_login" value="{{ config.mail_smtp_login }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_smtplogin_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_smtppass_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:mail_smtp_password" value="{{ config.mail_smtp_password }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_smtppass_desc }}</p>
    </div>
</div>
<h2>{{ language.admin_settings_list_db_block }}</h2>
<hr/>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_db_host_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:db_host" value="{{ config.db_host }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_db_host_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_db_user_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:db_user" value="{{ config.db_user }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_db_user_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_db_pass_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:db_pass" value="{{ config.db_pass }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_db_pass_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_db_name_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:db_name" value="{{ config.db_name }}"/>
        <p class="help-block">{{ language.admin_settings_list_label_db_name_desc }}</p>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_db_prefix_title }}</label>

    <div class="col-lg-9">
        <input class="form-control" type="text"  name="cfgmain:db_prefix" value="{{ config.db_prefix }}"/>

        <p class="help-block">{{ language.admin_settings_list_label_db_prefix_desc }}</p>
    </div>
</div>
</fieldset>
<input type="hidden" name="cfgmain:password_salt" value="{{ config.password_salt }}" />
<input type="submit" name="submit" value="{{ language.admin_settings_list_button_save }}" class="btn btn-success"/>
</form>
