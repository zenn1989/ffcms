{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_settings }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p>{{ language.admin_component_usercontrol_description }}</p>
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        <h2>{{ language.admin_component_usercontrol_first_data }}</h2>
        <hr />
        {{ settingstpl.selectYNgroup('login_captcha', config.login_captcha, language.admin_component_usercontrol_config_logincaptcha_name, language.admin_component_usercontrol_config_logincaptcha_desc, _context) }}
        {{ settingstpl.selectYNgroup('register_captcha', config.register_captcha, language.admin_component_usercontrol_config_regcaptcha_name, language.admin_component_usercontrol_config_regcaptcha_desc, _context) }}
        {{ settingstpl.selectYNgroup('register_aprove', config.register_aprove, language.admin_component_usercontrol_config_aprovereg_name, language.admin_component_usercontrol_config_aprovereg_desc, _context) }}
        {{ settingstpl.selectYNgroup('use_openid', config.use_openid, language.admin_component_usercontrol_config_openid_name, language.admin_component_usercontrol_config_openid_desc, _context) }}
        <h2>{{ language.admin_component_usercontrol_second_data }}</h2>
        <hr />
        {{ settingstpl.selectYNgroup('profile_view', config.profile_view, language.admin_component_usercontrol_config_guest_access_name, language.admin_component_usercontrol_config_guest_access_desc, _context) }}
        {{ settingstpl.textgroup('wall_post_count', config.wall_post_count, language.admin_component_usercontrol_config_userwall_name, language.admin_component_usercontrol_config_userwall_desc ) }}
        {{ settingstpl.textgroup('marks_post_count', config.marks_post_count, language.admin_component_usercontrol_config_marks_name, language.admin_component_usercontrol_config_marks_desc ) }}
        {{ settingstpl.textgroup('friend_page_count', config.friend_page_count, language.admin_component_usercontrol_config_friend_page_count_name, language.admin_component_usercontrol_config_friend_page_count_desc ) }}
        {{ settingstpl.textgroup('wall_post_delay', config.wall_post_delay, language.admin_component_usercontrol_config_wall_post_delay_name, language.admin_component_usercontrol_config_wall_post_delay_desc ) }}
        {{ settingstpl.textgroup('pm_count', config.pm_count, language.admin_component_usercontrol_config_pm_count_name, language.admin_component_usercontrol_config_pm_count_desc ) }}
        {{ settingstpl.selectYNgroup('balance_view', config.balance_view, language.admin_component_usercontrol_config_use_balance_name, language.admin_component_usercontrol_config_use_balance_desc, _context) }}
        <h2>{{ language.admin_component_usercontrol_thred_data }}</h2>
        <hr />
        {{ settingstpl.textgroup('userlist_count', config.userlist_count, language.admin_component_usercontrol_config_userlist_count_name, language.admin_component_usercontrol_config_userlist_count_desc ) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>