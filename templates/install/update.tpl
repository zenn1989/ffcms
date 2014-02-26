{% import 'macro/notify.tpl' as ntpl %}
{% if notify.unlock_update %}
    {{ ntpl.error(language.install_update_notify_to_version|replace({ '{$version}' : system.version })) }}
{% endif %}
{% if notify.locked_update %}
    {{ ntpl.error(language.install_update_notify_locked|replace({ '{$version}' : system.version })) }}
{% endif %}
{% if notify.success %}
    {{ ntpl.success(language.install_update_success_notify) }}
{% endif %}
{% if notify.nosql_data %}
    {{ ntpl.error(language.install_updates_noexist) }}
{% endif %}
<p class="alert alert-info">
    {{ language.install_update_text_info }} <strong>{{ system.version }}</strong>
</p>
<p class="alert alert-warning">
    {{ language.install_update_text_notify }}
</p>
<form action="" method="post">
    <input type="submit" name="startupdate" value="{{ language.install_update_button_start }}" class="btn btn-danger"/>
</form>