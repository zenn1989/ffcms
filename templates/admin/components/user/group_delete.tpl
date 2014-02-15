{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_group }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<form action="" method="post" class="form-horizontal">
    {{ notifytpl.warning(language.admin_component_usercontrol_group_del_notify) }}
    {% if notify.cant_delete_owner %}
        {{ notifytpl.error(language.admin_component_usercontrol_group_del_ownererror) }}
    {% endif %}
    <strong>{{ language.admin_component_usercontrol_group_name }}: </strong> <code>{{ group.name }}</code> <br />
    <input type="submit" name="submit" value="{{ language.admin_component_usercontrol_group_button_delete }}" class="btn btn-danger"/>
</form>