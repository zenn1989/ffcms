<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_edit }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p class="alert alert-warning">{{ language.admin_component_usercontrol_delete_notify_warm }}</p>
<strong>{{ language.admin_component_usercontrol_delete_request_text }}
    <code>{{ udata.login|striptags|escape }}</code>(<code>id{{ udata.id|striptags|escape }}</code>/<code>{{ udata.email|escape|striptags }}</code>) ?</strong>
<br/><br/>
<form action="" method="post">
    <input type="submit" name="deleteuser" value="{{ language.admin_component_usercontrol_delete_button_success }}" class="btn btn-danger"/>
    <a href="?object=components&action=user" class="btn btn-info">{{ language.admin_component_usercontrol_delete_button_cancel }}</a>
</form>