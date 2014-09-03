{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_serviceban }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p>{{ language.admin_component_usercontrol_ban_text_p_info }}</p>
{% if notify.wrong_data %}
    {{ notifytpl.error(language.admin_component_usercontrol_ban_wrong_data) }}
{% endif %}
{% if notify.added %}
    {{ notifytpl.success(language.admin_component_usercontrol_ban_ip_setted) }}
{% endif %}
{% if notify.refreshed %}
    {{ notifytpl.success(language.admin_component_usercontrol_ban_ip_refreshed) }}
{% endif %}
{% if notify.ban_ip_set %}
    {{ notifytpl.success(language.admin_component_usercontrol_ban_ip_setted) }}
{% endif %}
<h2>{{ language.admin_component_usercontrol_ban_h5_reg_title }}</h2>
<hr />
<p>{{ language.admin_component_usercontrol_ban_reg_desc }}</p>
<form method="post" action="" class="form-horizontal">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_ban_label_userdata }}</label>

            <div class="col-lg-9">
                <input type="text" class="form-control" name="userdata" placeholder="27" {% if udata.id %}value="{{ udata.id }}" {% endif %}/>

                <p class="help-block">{{ language.admin_component_usercontrol_ban_help_loginid }}</p>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <input type="submit" name="idorloginblock" value="{{ language.admin_component_usercontrol_ban_regsearch_button }}" class="btn btn-success"/>
            </div>
        </div>
    </fieldset>
</form>
{% if bansearch %}
    <p class="alert alert-info">{{ language.admin_component_usercontrol_ban_uid_notify_text }} id{{ udata.id }}(<a href="{{ system.script_url }}/user/id{{ udata.id }}" target="_blank">{{ udata.login }}</a>)</p>
    <p class="alert alert-danger">{{ language.admin_component_usercontrol_ban_uid_warning_text }}</p>
    <form method="post" action="" class="form-horizontal">
        <input type="hidden" name="blockuserid" {% if udata.id %}value="{{ udata.id }}" {% endif %}/>
        <fieldset>
            <div class="form-group">
                <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_ban_label_endtime }}</label>

                <div class="col-lg-9">
                    <input type="text" class="form-control" name="enddate" placeholder="2020-10-25"/>

                    <p class="help-block">{{ language.admin_component_usercontrol_ban_help_endtime }}</p>
                </div>
            </div>
            <div class="control-group">
                <div class="col-lg-offset-3 col-lg-9">
                    <input type="submit" name="banuserid" value="{{ language.admin_component_usercontrol_ban_button_red }}" class="btn btn-danger"/>
                </div>
            </div>
        </fieldset>
    </form>
{% endif %}

<h2>{{ language.admin_component_usercontrol_ban_h5_ip_info }}</h2>
<hr />
<p>{{ language.admin_component_usercontrol_ban_ip_desc }}</p>
<form method="post" action="" class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_ban_label_userdata }}</label>

            <div class="col-lg-9">
                <input type="text" class="form-control" name="userip" placeholder="127.0.0.1"/>

                <p class="help-block">{{ language.admin_component_usercontrol_ban_help_ip }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_ban_label_endtime }}</label>

            <div class="col-lg-9">
                <input type="text" class="form-control" name="enddate" placeholder="2020-10-25"/>

                <p class="help-block">{{ language.admin_component_usercontrol_ban_help_endtime }}</p>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <input type="submit" name="ipblock" value="{{ language.admin_component_usercontrol_ban_button_red }}" class="btn btn-danger"/>
            </div>
        </div>
    </fieldset>
</form>