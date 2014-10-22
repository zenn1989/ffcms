<!-- selectize -->
<script src="{{ system.script_url }}/resource/selectize/0.11.2/js/standalone/selectize.js"></script>
<link rel="stylesheet" href="{{ system.script_url }}/resource/selectize/0.11.2/css/selectize.bootstrap3.css" />
<!-- switchable -->
<link rel="stylesheet" href="{{ system.script_url }}/resource/bootstrap-switch/3.0/css/bootstrap3/bootstrap-switch.css">
<script src="{{ system.script_url }}/resource/bootstrap-switch/3.0/js/bootstrap-switch.min.js"></script>
<script>
    (function( $ ) {
        "use strict";

        $( document ).ready(function() {
            $('.selectize-select').selectize({
                create: false,
                sortField: 'text'
            });
            $('.selectize-tags').selectize({
                plugins: ['remove_button'],
                delimiter: ',',
                persist: false,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
            $(".switchable").bootstrapSwitch({
                onColor: 'success',
                offColor: 'danger'
            });
        });

    })( jQuery );
</script>
{% import 'macro/notify.tpl' as notify_macro %}
<h1>{{ language.admin_settings_title }}<small>{{ language.admin_settings_list_desc }}</small></h1>
<hr />
{% if notify.saved %}
    {{ notify_macro.success(language.admin_settings_saved) }}
{% endif %}
<form method="post" action="" class="form-horizontal" role="form">
<input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#main" role="tab" data-toggle="tab">{{ language.admin_settings_list_main_block }}</a></li>
    <li><a href="#seo" role="tab" data-toggle="tab">{{ language.admin_settings_list_seo_block }}</a></li>
    <li><a href="#token" role="tab" data-toggle="tab">{{ language.admin_settings_list_token_block }}</a></li>
    <li><a href="#file" role="tab" data-toggle="tab">{{ language.admin_settings_list_file_block }}</a></li>
    <li><a href="#mail" role="tab" data-toggle="tab">{{ language.admin_settings_list_mail_block }}</a></li>
    <li><a href="#db" role="tab" data-toggle="tab">{{ language.admin_settings_list_db_block }}</a></li>
    <li><a href="#api" role="tab" data-toggle="tab">API</a></li>
</ul>
<fieldset>
<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane active" id="main">
        <h2>{{ language.admin_settings_list_main_block }}</h2>
        <hr/>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_url_title }}</label>

            <div class="col-lg-9">
                <input class="form-control" type="text" name="cfgmain:url" value="{{ config.source_url }}"/>
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
                <select name="cfgmain:time_zone" class="form-control selectize-select">
                    {% for tz_name,tz_utc in config.addon.availableZones %}
                        <option value="{{ tz_name }}"{% if tz_name == config.time_zone %} selected{% endif %}>{{ tz_name }}({{ tz_utc }})</option>
                    {% endfor %}
                </select>
                <p class="help-block">{{ language.admin_settings_list_label_timezone_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="cfg-debug">{{ language.admin_settings_list_label_debug_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:debug" value="0" />
                <input id="cfg-debug" type="checkbox" name="cfgmain:debug"{% if config.debug %} checked{% endif %} class="switchable" value="1" />
                <p class="help-block">{{ language.admin_settings_list_label_debug_desc }}</p>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-3 control-label" for="cfg-collect_statistic">{{ language.admin_settings_list_label_statisticuse_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:collect_statistic" value="0" />
                <input id="cfg-collect_statistic" type="checkbox" name="cfgmain:collect_statistic"{% if config.collect_statistic %} checked{% endif %} class="switchable" value="1" />
                <p class="help-block">{{ language.admin_settings_list_label_statisticuse_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="cfg-user_friendly_url">{{ language.admin_settings_list_label_friendurl_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:user_friendly_url" value="0" />
                <input id="cfg-user_friendly_url" type="checkbox" name="cfgmain:user_friendly_url"{% if config.user_friendly_url %} checked{% endif %} class="switchable" value="1" />
                <p class="help-block">{{ language.admin_settings_list_label_friendurl_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="cfg-use_multi_language">{{ language.admin_settings_list_label_multilang_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:use_multi_language" value="0" />
                <input id="cfg-use_multi_language" type="checkbox" name="cfgmain:use_multi_language"{% if config.use_multi_language %} checked{% endif %} class="switchable" value="1" />
                <p class="help-block">{{ language.admin_settings_list_label_multilang_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="cfg-maintenance">{{ language.admin_settings_list_label_maintenance_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:maintenance" value="0" />
                <input id="cfg-maintenance" type="checkbox" name="cfgmain:maintenance"{% if config.maintenance %} checked{% endif %} class="switchable" value="1" />
                <p class="help-block">{{ language.admin_settings_list_label_maintenance_desc }}</p>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="seo">
        <h2>{{ language.admin_settings_list_seo_block }}</h2>
        <hr/>
        <div class="tabbable" id="contentTab">
            <ul class="nav nav-tabs">
                {% for itemlang in system.languages %}
                    <li{% if itemlang == system.lang %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
                {% endfor %}
            </ul>
            <br />
            <div class="tab-content">
                {% for itemlang in system.languages %}
                    <div class="tab-pane fade{% if itemlang == system.lang %} in active{% endif %}" id="{{ itemlang }}">
                        <div class="form-group">
                            <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_title_title }}[{{ itemlang }}]</label>

                            <div class="col-lg-9">
                                <input class="form-control" type="text"  name="cfgmain:seo_title[{{ itemlang }}]" value="{{ config.seo_title[itemlang] }}"/>
                                <p class="help-block">{{ language.admin_settings_list_label_title_desc }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_desc_title }}[{{ itemlang }}]</label>

                            <div class="col-lg-9">
                                <textarea name="cfgmain:seo_description[{{ itemlang }}]" class="form-control">{{ config.seo_description[itemlang] }}</textarea>
                                <p class="help-block">{{ language.admin_settings_list_label_desc_desc }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_keywords_title }}[{{ itemlang }}]</label>

                            <div class="col-lg-9">
                                <textarea name="cfgmain:seo_keywords[{{ itemlang }}]" class="form-control selectize-tags">{{ config.seo_keywords[itemlang] }}</textarea>
                                <p class="help-block">{{ language.admin_settings_list_label_keywords_desc }}</p>
                            </div>
                        </div>
                    </div>
                {% endfor %}
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
    </div>
    <div class="tab-pane" id="token">
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
    </div>
    <div class="tab-pane" id="file">
        <h2>{{ language.admin_settings_list_file_block }}</h2>
        <hr />
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_settings_list_file_imgsize_title }}</label>

            <div class="col-lg-9">
                <input class="form-control" type="text"  name="cfgmain:upload_img_max_size" value="{{ config.upload_img_max_size }}"/>
                <p class="help-block">{{ language.admin_settings_list_file_imgsize_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_settings_list_file_typeallow_title }}</label>

            <div class="col-lg-9">
                <input class="form-control" type="text"  name="cfgmain:upload_allowed_ext" value="{{ config.upload_allowed_ext }}"/>
                <p class="help-block">{{ language.admin_settings_list_file_typeallow_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_settings_list_file_othersize_title }}</label>

            <div class="col-lg-9">
                <input class="form-control" type="text"  name="cfgmain:upload_other_max_size" value="{{ config.upload_other_max_size }}"/>
                <p class="help-block">{{ language.admin_settings_list_file_othersize_desc }}</p>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="mail">
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
            <label class="col-lg-3 control-label" for="cfg-mail_smtp_use">{{ language.admin_settings_list_label_smtpuse_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:mail_smtp_use" value="0" />
                <input id="cfg-mail_smtp_use" type="checkbox" name="cfgmain:mail_smtp_use"{% if config.mail_smtp_use %} checked{% endif %} class="switchable" value="1" />
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
            <label class="col-lg-3 control-label" for="cfg-mail_smtp_auth">{{ language.admin_settings_list_label_smtpauth_title }}</label>

            <div class="col-lg-9">
                <input type="hidden" name="cfgmain:mail_smtp_auth" value="0" />
                <input id="cfg-mail_smtp_auth" type="checkbox" name="cfgmain:mail_smtp_auth"{% if config.mail_smtp_auth %} checked{% endif %} class="switchable" value="1" />
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
    </div>
    <div class="tab-pane" id="db">
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
    </div>
    <div class="tab-pane" id="api">
        <h2>{{ language.admin_settings_list_api_block }}</h2>
        <hr />
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_settings_list_label_api_yandextranslate_title }}</label>

            <div class="col-lg-9">
                <input class="form-control" type="text"  name="cfgmain:yandex_translate_key" value="{{ config.yandex_translate_key }}"/>

                <p class="help-block">{{ language.admin_settings_list_label_api_yandextranslate_desc }}</p>
            </div>
        </div>
    </div>
</div>
</fieldset>
<input type="hidden" name="cfgmain:password_salt" value="{{ config.password_salt }}" />
<input type="submit" name="submit" value="{{ language.admin_settings_list_button_save }}" class="btn btn-success"/>
</form>
