<h1>{{ extension.title }}<small>{{ language.admin_modules_menu_list_title }}</small></h1>
<hr />
<div class="pull-right">
    <a href="?object=modules&action=menu&make=add" class="btn btn-success"><i class="fa fa-plus"></i> {{ language.admin_modules_menu_list_btn_add }}</a>
</div>
{% if modmenu.list %}
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>{{ language.admin_modules_menu_list_th_name }}</th>
            <th>{{ language.admin_modules_menu_list_th_sysname }}</th>
            <th>{{ language.admin_modules_menu_list_th_tpl }}</th>
            <th>{{ language.admin_modules_menu_list_th_visible }}</th>
            <th>{{ language.admin_modules_menu_list_th_manage }}</th>
        </tr>
        </thead>
        <tbody>
        {% for item in modmenu.list %}
            <tr>
                <td>{{ item.id }}</td>
                <td><a href="?object=modules&action=menu&make=manage&id={{ item.id }}">{{ item.name }}</a></td>
                <td>{{ item.tag }}</td>
                <td>{{ item.tpl }}</td>
                <td class="text-center">{% if item.display == 1 %}<i class="fa fa-eye"></i>{% else %}<i class="fa fa-eye-slash"></i>{% endif %}</td>
                <td class="text-center">
                    <a href="?object=modules&action=menu&make=manage&id={{ item.id }}"><i class="fa fa-sitemap fa-lg"></i></a>
                    <a href="?object=modules&action=menu&make=edit&id={{ item.id }}"><i class="fa fa-pencil fa-lg"></i></a>
                    <a href="?object=modules&action=menu&make=delete&id={{ item.id }}"><i class="fa fa-trash fa-lg"></i></a>
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
</div>
{% else %}
<p>{{ language.admin_modules_menu_list_empty }}</p>
{% endif %}