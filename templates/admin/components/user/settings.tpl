{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_settings }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#auth" role="tab" data-toggle="tab">{{ language.admin_component_usercontrol_first_data }}</a></li>
    <li><a href="#profile" role="tab" data-toggle="tab">{{ language.admin_component_usercontrol_second_data }}</a></li>
    <li><a href="#list" role="tab" data-toggle="tab">{{ language.admin_component_usercontrol_thred_data }}</a></li>
    <li><a href="#balance" role="tab" data-toggle="tab">{{ language.admin_component_usercontrol_config_balance_block }}</a></li>
</ul>
<form action="" method="post" class="form-horizontal" role="form">
    <div class="tab-content">
        <div class="tab-pane active" id="auth">
            <h2>{{ language.admin_component_usercontrol_first_data }}</h2>
            <hr />
            {{ settingstpl.selectYNgroup('login_captcha', config.login_captcha, language.admin_component_usercontrol_config_logincaptcha_name, language.admin_component_usercontrol_config_logincaptcha_desc, _context) }}
            {{ settingstpl.selectYNgroup('register_captcha', config.register_captcha, language.admin_component_usercontrol_config_regcaptcha_name, language.admin_component_usercontrol_config_regcaptcha_desc, _context) }}
            {{ settingstpl.selectYNgroup('register_aprove', config.register_aprove, language.admin_component_usercontrol_config_aprovereg_name, language.admin_component_usercontrol_config_aprovereg_desc, _context) }}
            {{ settingstpl.selectYNgroup('use_openid', config.use_openid, language.admin_component_usercontrol_config_openid_name, language.admin_component_usercontrol_config_openid_desc, _context) }}
        </div>
        <div class="tab-pane" id="profile">
            <h2>{{ language.admin_component_usercontrol_second_data }}</h2>
            <hr />
            {{ settingstpl.selectYNgroup('profile_view', config.profile_view, language.admin_component_usercontrol_config_guest_access_name, language.admin_component_usercontrol_config_guest_access_desc, _context) }}
            {{ settingstpl.textgroup('wall_post_count', config.wall_post_count, language.admin_component_usercontrol_config_userwall_name, language.admin_component_usercontrol_config_userwall_desc ) }}
            {{ settingstpl.textgroup('marks_post_count', config.marks_post_count, language.admin_component_usercontrol_config_marks_name, language.admin_component_usercontrol_config_marks_desc ) }}
            {{ settingstpl.textgroup('friend_page_count', config.friend_page_count, language.admin_component_usercontrol_config_friend_page_count_name, language.admin_component_usercontrol_config_friend_page_count_desc ) }}
            {{ settingstpl.textgroup('wall_post_delay', config.wall_post_delay, language.admin_component_usercontrol_config_wall_post_delay_name, language.admin_component_usercontrol_config_wall_post_delay_desc ) }}
            {{ settingstpl.textgroup('pm_count', config.pm_count, language.admin_component_usercontrol_config_pm_count_name, language.admin_component_usercontrol_config_pm_count_desc ) }}
            {{ settingstpl.selectYNgroup('use_karma', config.use_karma, language.admin_component_usercontrol_config_use_karma_title, language.admin_component_usercontrol_config_use_karma_desc, _context) }}
        </div>
        <div class="tab-pane" id="list">
            <h2>{{ language.admin_component_usercontrol_thred_data }}</h2>
            <hr />
            {{ settingstpl.textgroup('userlist_count', config.userlist_count, language.admin_component_usercontrol_config_userlist_count_name, language.admin_component_usercontrol_config_userlist_count_desc ) }}
        </div>
        <div class="tab-pane" id="balance">
            <h2>{{ language.admin_component_usercontrol_config_balance_block }}</h2>
            {{ settingstpl.selectYNgroup('balance_view', config.balance_view, language.admin_component_usercontrol_config_use_balance_name, language.admin_component_usercontrol_config_use_balance_desc, _context) }}
            {{ settingstpl.textgroup('balance_valut_name', config.balance_valut_name, language.admin_component_usercontrol_config_balancename_title, language.admin_component_usercontrol_config_balancename_desc) }}
            <h2>Webmoney Merchant</h2>
            <hr />
            {{ settingstpl.selectYNgroup('balance_use_webmoney', config.balance_use_webmoney, language.admin_component_usercontrol_config_balancewebmoney_enable_title, language.admin_component_usercontrol_config_balancewebmoney_enable_desc, _context) }}
            {{ settingstpl.selectYNgroup('balance_wm_test', config.balance_wm_test, language.admin_component_usercontrol_config_balancewebmoney_testtype_title, language.admin_component_usercontrol_config_balancewebmoney_testtype_desc, _context) }}
            {{ settingstpl.textgroup('balance_wm_purse', config.balance_wm_purse, language.admin_component_usercontrol_config_balancewebmoney_wmtype_title, language.admin_component_usercontrol_config_balancewebmoney_wmtype_desc) }}
            {{ settingstpl.textgroup('balance_wm_mul', config.balance_wm_mul, language.admin_component_usercontrol_config_balancewebmoney_mul_title, language.admin_component_usercontrol_config_balancewebmoney_mul_desc) }}
            {{ settingstpl.textgroup('balance_wm_secretkey', config.balance_wm_secretkey, 'Webmoney secret', language.admin_component_usercontrol_config_balancewebmoney_secret) }}
            <h2>Interkassa 2.0</h2>
            <hr />
            {{ settingstpl.selectYNgroup('balance_use_ik', config.balance_use_ik, language.admin_component_usercontrol_config_balanceik_enable_title, language.admin_component_usercontrol_config_balanceik_enable_desc, _context) }}
            {{ settingstpl.textgroup('balance_ik_id', config.balance_ik_id, language.admin_component_usercontrol_config_balanceik_caseid_title, language.admin_component_usercontrol_config_balanceik_caseid_desc) }}
            {{ settingstpl.textgroup('balance_ik_key', config.balance_ik_key, language.admin_component_usercontrol_config_balanceik_secretkey_title, language.admin_component_usercontrol_config_balanceik_secretkey_desc) }}
            {{ settingstpl.textgroup('balance_ik_mul', config.balance_ik_mul, language.admin_component_usercontrol_config_balanceik_mul_title, language.admin_component_usercontrol_config_balanceik_mul_desc) }}
            {{ settingstpl.textgroup('balance_ik_valute', config.balance_ik_valute, language.admin_component_usercontrol_config_balanceik_valutname_title, language.admin_component_usercontrol_config_balanceik_valutname_desc) }}
            <h2>Robokassa</h2>
            <hr />
            {{ settingstpl.selectYNgroup('balance_use_rk', config.balance_use_rk, language.admin_component_usercontrol_config_balancerk_enable_title, language.admin_component_usercontrol_config_balancerk_enable_desc, _context) }}
            {{ settingstpl.selectYNgroup('balance_rk_test', config.balance_rk_test, language.admin_component_usercontrol_config_balancerk_testmod_title, language.admin_component_usercontrol_config_balancerk_testmod_desc, _context) }}
            {{ settingstpl.textgroup('balance_rk_id', config.balance_rk_id, language.admin_component_usercontrol_config_balancerk_shopname_title, language.admin_component_usercontrol_config_balancerk_shopname_desc) }}
            {{ settingstpl.textgroup('balance_rk_key_1', config.balance_rk_key_1, language.admin_component_usercontrol_config_balancerk_pass1_title, language.admin_component_usercontrol_config_balancerk_pass1_desc) }}
            {{ settingstpl.textgroup('balance_rk_key_2', config.balance_rk_key_2, language.admin_component_usercontrol_config_balancerk_pass2_title, language.admin_component_usercontrol_config_balancerk_pass2_desc) }}
            {{ settingstpl.textgroup('balance_rk_mul', config.balance_rk_mul, language.admin_component_usercontrol_config_balancerk_mul_title, language.admin_component_usercontrol_config_balancerk_mul_desc) }}
            {{ settingstpl.textgroup('balance_rk_valute', config.balance_rk_valute, language.admin_component_usercontrol_config_balancerk_valut_title, language.admin_component_usercontrol_config_balancerk_valut_desc) }}
        </div>
    </div>
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
</form>

<div class="modal fade" id="helpwebmoney" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Webmoney help guide</h4>
            </div>
            <div class="modal-body">
                <p>{{ language.admin_component_usercontrol_config_wmhelp_p1 }}</p>
                <p>{{ language.admin_component_usercontrol_config_wmhelp_p2 }}</p>
                <h4>{{ language.admin_component_usercontrol_config_wmhelp_header_required }}</h4>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Param</th>
                        <th>Value</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Result URL</td>
                            <td>{{ system.script_url }}/api.php?iface=front&object=payment&type=webmoney</td>
                        </tr>
                        <tr>
                            <td>Success URL[POST]</td>
                            <td>{{ system.url }}/user/paynotify/wm/success/</td>
                        </tr>
                        <tr>
                            <td>Fail URL[POST]</td>
                            <td>{{ system.url }}/user/paynotify/wm/fail/</td>
                        </tr>
                        <tr>
                            <td>Control signature type</td>
                            <td>SHA-256</td>
                        </tr>
                    </tbody>
                </table>
                <p>{{ language.admin_component_usercontrol_config_wmhelp_p3 }}</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="helpik" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Interkassa help guide</h4>
            </div>
            <div class="modal-body">
                <p>{{ language.admin_component_usercontrol_config_balanceik_help_p1 }}</p>
                <p>{{ language.admin_component_usercontrol_config_balanceik_help_p2 }}</p>
                <h4>{{ language.admin_component_usercontrol_config_balanceik_head_params }}</h4>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Param</th>
                        <th>Value</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ language.admin_component_usercontrol_config_balanceik_param_success }}</td>
                        <td>{{ system.url }}/user/paynotify/ik/success/</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_component_usercontrol_config_balanceik_param_fail }}</td>
                        <td>{{ system.url }}/user/paynotify/ik/fail/</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_component_usercontrol_config_balanceik_param_wait }}</td>
                        <td>{{ system.url }}/user/paynotify/ik/wait/</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_component_usercontrol_config_balanceik_param_status }}</td>
                        <td>{{ system.script_url }}/api.php?iface=front&object=payment&type=interkassa</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_component_usercontrol_config_balanceik_param_crypt }}</td>
                        <td>MD5</td>
                    </tr>
                    </tbody>
                </table>
                <p>{{ language.admin_component_usercontrol_config_balanceik_help_p3 }}</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="helprk" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Robokassa help guide</h4>
            </div>
            <div class="modal-body">
                <p>{{ language.admin_component_usercontrol_config_balancerk_help_p1 }}</p>
                <p>{{ language.admin_component_usercontrol_config_balancerk_help_p2 }}</p>
                <h4>{{ language.admin_component_usercontrol_config_balancerk_help_header }}</h4>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Param</th>
                        <th>Value</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Success URL[POST]</td>
                        <td>{{ system.url }}/user/paynotify/rk/success/</td>
                    </tr>
                    <tr>
                        <td>Fail URL[POST]</td>
                        <td>{{ system.url }}/user/paynotify/ik/fail/</td>
                    </tr>
                    <tr>
                        <td>Result URL[POST]</td>
                        <td>{{ system.script_url }}/api.php?iface=front&object=payment&type=robokassa</td>
                    </tr>
                    </tbody>
                </table>
                <p>{{ language.admin_component_usercontrol_config_balancerk_help_p3 }}</p>
            </div>
        </div>
    </div>
</div>