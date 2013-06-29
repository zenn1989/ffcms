{$notify}
<form method="post" action="" class="form-horizontal">
    <fieldset>
        {$lang::admin_settings_list_desc}
        <h5>{$lang::admin_settings_list_main_block}</h5>
        <hr/>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_url_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:url" value="{$config.url}"/>

                <p class="help-block">{$lang::admin_settings_list_label_url_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_tpldir_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:tpl_dir" value="{$config.tpl_dir}"/>

                <p class="help-block">{$lang::admin_settings_list_label_tpldir_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_tplname_title}</label>

            <div class="controls">
                <select name="cfgmain:tpl_name">{$settings_tpl_name_list}</select>

                <p class="help-block">{$lang::admin_settings_list_label_tplname_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_debug_title}</label>

            <div class="controls">
                <select name="cfgmain:debug">{$settings_debug_list}</select>

                <p class="help-block">{$lang::admin_settings_list_label_debug_desc}</p>
            </div>
        </div>


        <h5>{$lang::admin_settings_list_seo_block}</h5>
        <hr/>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_title_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:seo_title" value="{$config.seo_title}"/>

                <p class="help-block">{$lang::admin_settings_list_label_title_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_desc_title}</label>

            <div class="controls">
                <textarea name="cfgmain:seo_description">{$config.seo_description}</textarea>

                <p class="help-block">{$lang::admin_settings_list_label_desc_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_keywords_title}</label>

            <div class="controls">
                <textarea name="cfgmain:seo_keywords">{$config.seo_keywords}</textarea>

                <p class="help-block">{$lang::admin_settings_list_label_keywords_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_multititle_title}</label>

            <div class="controls">
                <select name="cfgmain:multi_title">{$settings_multi_title_list}</select>

                <p class="help-block">{$lang::admin_settings_list_label_multititle_desc}</p>
            </div>
        </div>
        <h5>{$lang::admin_settings_list_token_block}</h5>
        <hr/>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_cache_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:cache_interval" value="{$config.cache_interval}"/>

                <p class="help-block">{$lang::admin_settings_list_label_cache_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_token_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:token_time" value="{$config.token_time}"/>

                <p class="help-block">{$lang::admin_settings_list_label_token_desc}</p>
            </div>
        </div>
        <h5>{$lang::admin_settings_list_mail_block}</h5>
        <hr/>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_from_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:mail_from" value="{$config.mail_from}"/>

                <p class="help-block">{$lang::admin_settings_list_label_from_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_nick_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:mail_ownername" value="{$config.mail_ownername}"/>

                <p class="help-block">{$lang::admin_settings_list_label_nick_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_smtpuse_title}</label>

            <div class="controls">
                <select name="cfgmain:mail_smtp_use">{$settings_smtp_use_list}</select>

                <p class="help-block">{$lang::admin_settings_list_label_smtpuse_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_smtphost_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:mail_smtp_host" value="{$config.mail_smtp_host}"/>

                <p class="help-block">{$lang::admin_settings_list_label_smtphost_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_smtpport_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:mail_smtp_port" value="{$config.mail_smtp_port}"/>

                <p class="help-block">{$lang::admin_settings_list_label_smtpport_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_smtpauth_title}</label>

            <div class="controls">
                <select name="cfgmain:mail_smtp_auth">{$settings_smtp_auth_list}</select>

                <p class="help-block">{$lang::admin_settings_list_label_smtpauth_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_smtplogin_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:mail_smtp_login" value="{$config.mail_smtp_login}"/>

                <p class="help-block">{$lang::admin_settings_list_label_smtplogin_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_smtppass_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:mail_smtp_password" value="{$config.mail_smtp_password}"/>

                <p class="help-block">{$lang::admin_settings_list_label_smtppass_desc}</p>
            </div>
        </div>
        <h5>{$lang::admin_settings_list_db_block}</h5>
        <hr/>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_db_host_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:db_host" value="{$config.db_host}"/>

                <p class="help-block">{$lang::admin_settings_list_label_db_host_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_db_user_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:db_user" value="{$config.db_user}"/>

                <p class="help-block">{$lang::admin_settings_list_label_db_user_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_db_pass_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:db_pass" value="{$config.db_pass}"/>

                <p class="help-block">{$lang::admin_settings_list_label_db_pass_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_db_name_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:db_name" value="{$config.db_name}"/>

                <p class="help-block">{$lang::admin_settings_list_label_db_name_desc}</p>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{$lang::admin_settings_list_label_db_prefix_title}</label>

            <div class="controls">
                <input type="text" name="cfgmain:db_prefix" value="{$config.db_prefix}"/>

                <p class="help-block">{$lang::admin_settings_list_label_db_prefix_desc}</p>
            </div>
        </div>
    </fieldset>
    <input type="submit" name="submit" value="{$lang::admin_settings_list_button_save}" class="btn btn-success"/>
</form>
