{$notify}
<strong>{$lang::install_install_db_title}</strong>
<form class="form-horizontal" method="post" action="">
    <div class="control-group">
        <label class="control-label">{$lang::install_install_db_host_title}</label>
        <div class="controls">
            <input type="text" name="config:db_host" value="{$config_db_host}" placeholder="localhost">
            <p class="help-block">{$lang::install_install_db_host_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_db_user_title}</label>
        <div class="controls">
            <input type="text" placeholder="root" name="config:db_user" value="{$config_db_user}">
            <p class="help-block">{$lang::install_install_db_user_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_db_pass_title}</label>
        <div class="controls">
            <input type="password" placeholder="Password" name="config:db_pass" value="{$config_db_pass}">
            <p class="help-block">{$lang::install_install_db_pass_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_db_name_title}</label>
        <div class="controls">
            <input type="text" placeholder="ffcms" name="config:db_name" value="{$config_db_name}">
            <p class="help-block">{$lang::install_install_db_name_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_db_prefix_title}</label>
        <div class="controls">
            <input type="text" placeholder="ffcms" name="config:db_prefix" value="{$config_db_prefix}">
            <p class="help-block">{$lang::install_install_db_prefix_desc}</p>
        </div>
    </div>
<strong>{$lang::install_install_main_title}</strong>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_main_url_title}</label>
        <div class="controls">
            <input type="text" placeholder="http://ffcms.ru" name="config:url" value="{$config_url}">
            <p class="help-block">{$lang::install_install_main_url_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_main_lang_title}</label>
        <div class="controls">
            <select name="config:lang">{$config_option_lang}</select>
            <p class="help-block">{$lang::install_install_main_lang_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_main_timezone_title}</label>
        <div class="controls">
            <select name="config:time_zone">{$config_option_timezone}</select>
            <p class="help-block">{$lang::install_install_main_timezone_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_main_seotitle_title}</label>
        <div class="controls">
            <input type="text" placeholder="Dart Vader blog" name="config:seo_title" value="{$config_seo_title}">
            <p class="help-block">{$lang::install_install_main_seotitle_desc}</p>
        </div>
    </div>
    <p class="alert alert-info">{$lang::install_install_main_notifymore}</p>
<strong>{$lang::install_install_admin_title}</strong>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_admin_login_title}</label>
        <div class="controls">
            <input type="text" placeholder="admin" name="admin:login" value="{$admin_login}">
            <p class="help-block">{$lang::install_install_admin_login_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_admin_email_title}</label>
        <div class="controls">
            <input type="text" placeholder="admin@example.com" name="admin:email" value="{$admin_email}">
            <p class="help-block">{$lang::install_install_admin_email_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_admin_pass_title}</label>
        <div class="controls">
            <input type="password" placeholder="StrOnGPas1s4ord" name="admin:pass">
            <p class="help-block">{$lang::install_install_admin_pass_desc}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::install_install_admin_repass_title}</label>
        <div class="controls">
            <input type="password" placeholder="StrOnGPas1s4ord" name="admin:repass">
            <p class="help-block">{$lang::install_install_admin_repass_desc}</p>
        </div>
    </div>
    <input type="submit" name="submit" value="{$lang::install_install_button}" class="btn btn-success" />
</form>