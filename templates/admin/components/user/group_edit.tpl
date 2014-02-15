{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_group }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
{% if notify.empty_name %}
    {{ notifytpl.error(language.admin_component_usercontrol_group_add_empty) }}
{% endif %}
<form action="" method="post" role="form" class="form-horizontal">
    <fieldset>
        <h2>{{ language.admin_component_usercontrol_group_edit_globaldata }}</h2>
        <hr/>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_component_usercontrol_group_name }}</label>

            <div class="col-lg-9">
                <input class="form-control" type="text" name="group_name" value="{{ group.name }}"/>
            </div>
        </div>
        <h2>{{ language.admin_component_usercontrol_group_edit_perms }}</h2>
        <hr/>
        <div class="row">
            {% for perm in permission_all %}
            <div class="col-lg-2">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="perm[{{ perm }}]"{% if perm in group.rights %} checked{% endif %} /> {{ perm }}
                    </label>
                </div>
            </div>
            {% endfor %}
        </div>
        <br />
        <div class="row">
            <div class="col-lg-12">
                <input class="btn btn-success" type="submit" name="submit" value="{{ language.admin_component_usercontrol_group_save_button }}" />
            </div>
        </div>
    </fieldset>
</form>