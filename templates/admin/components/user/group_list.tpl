<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_group }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p>{{ language.admin_component_usercontrol_group_desctext }}</p>
<table class="table table-bordered table-responsive">
    <thead>
    <tr>
        <th>{{ language.admin_component_usercontrol_group_id }}</th>
        <th>{{ language.admin_component_usercontrol_group_name }}</th>
        <th>{{ language.admin_component_usercontrol_group_rights }}</th>
        <th>{{ language.admin_component_usercontrol_group_actions }}</th>
    </tr>
    </thead>
    <tbody>
    {% for item in group %}
    <tr>
        <td>{{ item.id }}</td>
        <td>{{ item.name }}</td>
        <td>
            {% for right in item.rights %}
            <div class="label {% if right == 'global/owner' %}label-danger{% elseif right starts with 'admin/' %}label-warning{% else %}label-info{% endif %}">{{ right }}</div>
            {% endfor %}
        </td>
        <td class="text-center"><a href="?object=components&action=user&make=groupedit&id={{ item.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
            <a href="?object=components&action=user&make=groupdelete&id={{ item.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
        </td>
    </tr>
    {% endfor %}
    </tbody>
</table>
<a href="?object=components&action=user&make=groupadd" class="btn btn-success">{{ language.admin_component_usercontrol_group_addbutton }}</a>