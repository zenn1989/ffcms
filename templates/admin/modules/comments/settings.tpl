{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_staticonmain_settings }}</small></h1>
<hr />
{% include 'modules/comments/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form method="post" action="" class="form-horizontal">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        {{ settingstpl.textgroup('comments_count', config.comments_count, language.admin_modules_comment_config_count_title, language.admin_modules_comment_config_count_desc ) }}
        {{ settingstpl.textgroup('time_delay', config.time_delay, language.admin_modules_comment_config_timedelay_title, language.admin_modules_comment_config_timedelay_desc ) }}
        {{ settingstpl.textgroup('edit_time', config.edit_time, language.admin_modules_comment_config_edittime_title, language.admin_modules_comment_config_edittime_desc ) }}
        {{ settingstpl.textgroup('min_length', config.min_length, language.admin_modules_comment_config_minlength_title, language.admin_modules_comment_config_minlength_desc ) }}
        {{ settingstpl.textgroup('max_length', config.max_length, language.admin_modules_comment_config_maxlength_title, language.admin_modules_comment_config_maxlength_desc ) }}
        {{ settingstpl.selectYNgroup('guest_comment', config.guest_comment, language.admin_modules_comment_config_guestaccess_title, language.admin_modules_comment_config_guestaccess_desc, _context) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>