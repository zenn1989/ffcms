<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_serviceban }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p class="alert alert-warning">{{ language.admin_component_usercontrol_ban_del_warn }}</p>
<table class="table table-responsive table-bordered">
    <thead>
    <tr>
        <th>№</th>
        <th>ID</th>
        <th>IP</th>
        <th>{{ language.admin_component_usercontrol_ban_exception }}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ ban.list.id }}</td>
        <td>{% if ban.list.user_id < 1 %}{{ language.admin_global_undefined }}{% else %}{{ ban.list.user_id }}{% endif %}</td>
        <td>{% if ban.list.ip|length < 1 %}{{ language.admin_global_undefined }}{% else %}{{ ban.list.ip }}{% endif %}</td>
        <td>{% if ban.list.express > 0 %}{{ ban.list.express|date('d.m.Y') }}{% else %}{{ language.admin_global_undefined }}{% endif %}</td>
    </tr>
    </tbody>
</table>
<form action="" method="post">
    <input type="submit" name="submit" value="{{ language.admin_component_usercontrol_ban_del_subm }}" class="btn btn-danger" />
    <a href="?object=components&action=user&make=banlist" class="btn btn-warning">{{ language.admin_component_usercontrol_ban_del_cancel }}</a>
</form>