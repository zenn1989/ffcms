{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_edit }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p>{{ language.admin_component_usercontrol_edit_text_prev }}</p>
<!-- notify -->
{% if notify.saved %}
    {{ notifytpl.success(language.admin_component_usercontrol_edit_notify_success) }}
{% endif %}
<form method="post" action="" class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_main }}</label>

            <div class="col-lg-4">
                <div class="input-group">
                    <span class="input-group-addon">id</span>
                    <input type="text" class="form-control" value="{{ udata.id }}" disabled>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="input-group">
                    <span class="input-group-addon">login</span>
                    <input type="text" class="form-control" value="{{ udata.login }}" disabled>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_nick }}</label>

            <div class="col-lg-9">
                <input type="text" name="nick" value="{{ udata.nick }}" class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_sex }}</label>

            <div class="col-lg-9">
                <select name="sex" class="form-control">
                {% for idx in 0..2 %}
                    <option value="{{ idx }}"{% if idx == udata.sex %} selected{% endif %}>
                        {% if idx == 1 %}
                            {{ language.admin_component_usercontrol_sexselect_male }}
                        {% elseif idx == 2 %}
                            {{ language.admin_component_usercontrol_sexselect_female }}
                        {% else %}
                            {{ language.admin_component_usercontrol_sexselect_unknown }}
                        {% endif %}
                    </option>
                    {{ idx }}
                {% endfor %}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_phone }}</label>

            <div class="col-lg-9">
                <input type="text" name="phone" value="{{ udata.phone }}" class="form-control" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_url }}</label>

            <div class="col-lg-9">
                <input type="text" name="webpage" value="{{ udata.webpage }}" class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_birthday }}</label>

            <div class="col-lg-9">
                <input type="text" name="birthday" value="{{ udata.birthday }}" class="form-control"/>

                <p class="help-block">{{ language.admin_component_usercontrol_edit_desc_birthday }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_status }}</label>

            <div class="col-lg-9">
                <input type="text" name="status" value="{{ udata.status }}" class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_group }}</label>

            <div class="col-lg-9">
                <select name="groupid" class="form-control">
                    {% for groups in udata.group_data %}
                        <option value="{{ groups.group_id }}"{% if groups.group_id == udata.current_group %} selected{% endif %}>{{ groups.group_name }}</option>
                    {% endfor %}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{{ language.admin_component_usercontrol_edit_label_newpwd }}</label>

            <div class="col-lg-9">
                <input type="text" name="newpass" value="" class="form-control" autocomplete="off"/>

                <p class="help-block">{{ language.admin_component_usercontrol_edit_desc_newpwd }}</p>
            </div>
        </div>
        <div class="alert alert-warning">{{ language.admin_component_usercontrol_edit_warning_notify }}</div>
        <input type="submit" name="submit" class="btn btn-success" value="{{ language.admin_component_usercontrol_edit_button_success }}"/>
        <a href="?object=components&action=user" class="btn btn-info">{{ language.admin_component_usercontrol_edit_button_cancel }}</a>
    </fieldset>
</form>