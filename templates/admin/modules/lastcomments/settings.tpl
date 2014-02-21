{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_lastcomments_settings }}</small></h1>
<hr />
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form method="post" action="" class="form-horizontal">
    <fieldset>
        {{ settingstpl.textgroup('last_count', config.last_count, language.admin_modules_lastcomments_label_count_title, language.admin_modules_lastcomments_label_count_desc ) }}
        {{ settingstpl.textgroup('text_length', config.text_length, language.admin_modules_lastcomments_label_length_title, language.admin_modules_lastcomments_label_length_desc ) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>