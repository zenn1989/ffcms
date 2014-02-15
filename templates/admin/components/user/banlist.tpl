<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_serviceban }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-12">
        <a href="?object=components&action=user&make=banadd" class="btn btn-danger pull-right">{{ language.admin_component_usercontrol_ban_new }}</a>
    </div>
</div>
<table class="table table-responsive table-bordered">
    <thead>
    <tr>
        <th>№</th>
        <th>ID</th>
        <th>IP</th>
        <th>{{ language.admin_component_usercontrol_ban_exception }}</th>
        <th>{{ language.admin_component_usercontrol_ban_actions }}</th>
    </tr>
    </thead>
    <tbody>
    {% for item in ban.list %}
    <tr>
        <td>{{ item.id }}</td>
        <td>{% if item.user_id < 1 %}{{ language.admin_global_undefined }}{% else %}{{ item.user_id }}{% endif %}</td>
        <td>{% if item.ip|length < 1 %}{{ language.admin_global_undefined }}{% else %}{{ item.ip }}{% endif %}</td>
        <td>{% if item.express > 0 %}{{ item.express|date('d.m.Y') }}{% else %}{{ language.admin_global_undefined }}{% endif %}</td>
        <td class="text-center">
            <a href="?object=components&action=user&make=bandelete&id={{ item.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
        </td>
    </tr>
    {% endfor %}
    </tbody>
</table>
{{ pagination }}