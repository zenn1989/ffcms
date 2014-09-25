{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_stream_settings_title }}</small></h1>
<hr />
{% include 'components/stream/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        {{ settingstpl.textgroup('count_stream_page', config.count_stream_page, language.admin_components_stream_settings_pagecount_title, language.admin_components_stream_settings_pagecount_desc) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>