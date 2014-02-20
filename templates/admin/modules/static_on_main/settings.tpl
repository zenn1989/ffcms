{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_staticonmain_settings }}</small></h1>
<hr />
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<p class="alert alert-info">{{ language.admin_modules_staticonmain_settings_desctext }}</p>
<form method="post" action="" class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_modules_staticonmain_settings_page_label}}</label>

            <div class="col-lg-9">
                <select class="form-control" name="cfg:news_id">
                    {% for item in staticpages %}
                        <option value="{{ item.id }}"{% if item.id == config.news_id %} selected{% endif %}>{{ item.title }}</option>
                    {% endfor %}
                </select>

                <p class="help-block">{{ language.admin_modules_staticonmain_settings_page_desc }}</p>
            </div>
        </div>
        {{ settingstpl.selectYNgroup('show_date', config.show_date, language.admin_modules_staticonmain_settings_showdate_title, language.admin_modules_staticonmain_settings_showdate_desc, _context) }}
    </fieldset>
    <input type="submit" name="submit" value="{{ language.admin_modules_staticonmain_settings_button_save }}" class="btn btn-success"/>
</form>