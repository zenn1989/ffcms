<p class="alert alert-info">{$lang::admin_modules_staticonmain_settings_desctext}</p>
<form method="post" action="" class="form-horizontal">
    <fieldset>
        <div class="control-group">
            <label class="control-label">{$lang::admin_modules_staticonmain_settings_page_label}</label>
            <div class="controls">
                <select class="input-large" name="config:news_id">
                    {$static_page_option_list}
                </select>
                <p class="help-block">{$lang::admin_modules_staticonmain_settings_page_desc}</p>
            </div>
        </div>
        {$config_show_date}
    </fieldset>
    <input type="submit" name="submit" value="{$lang::admin_modules_staticonmain_settings_button_save}" class="btn btn-success" />
</form>
