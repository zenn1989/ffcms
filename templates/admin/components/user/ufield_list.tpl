<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_ufields_list_title }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
        {{ language.admin_component_usercontrol_ufields_add_title }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li><a href="?object=components&action=user&make=ufieldaddtext">{{ language.admin_component_usercontrol_ufields_add_text }}</a></li>
        <li><a href="?object=components&action=user&make=ufieldaddimg">{{ language.admin_component_usercontrol_ufields_add_img }}</a></li>
        <li><a href="?object=components&action=user&make=ufieldaddlink">{{ language.admin_component_usercontrol_ufields_add_link }}</a></li>
    </ul>
</div>
{% if ufield.data %}
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>{{ language.admin_component_usercontrol_ufields_list_th_name }}</th>
            <th>{{ language.admin_component_usercontrol_ufields_list_th_type }}</th>
            <th>{{ language.admin_component_usercontrol_ufields_list_th_manage }}</th>
        </tr>
        </thead>
        <tbody>
        {% for item in ufield.data %}
        <tr>
            <td>{{ item.id }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.type }}</td>
            <td class="text-center">
                <a href="?object=components&action=user&make=ufieldedit{{ item.type }}&id={{ item.id }}"><i class="fa fa-pencil fa-lg"></i></a>
                <a href="?object=components&action=user&make=ufielddel&id={{ item.id }}"><i class="fa fa-trash fa-lg"></i></a>
            </td>
        </tr>
        {% endfor %}
        </tbody>
    </table>
</div>
{% else %}
    <p class="alert alert-warning">{{ language.admin_component_usercontrol_ufields_list_empty }}</p>
{% endif %}