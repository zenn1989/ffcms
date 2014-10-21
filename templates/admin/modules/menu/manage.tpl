<h1>{{ extension.title }}<small>{{ language.admin_modules_menu_manage_title }}</small></h1>
<hr />
<div class="panel panel-info">
    <div class="panel-heading">
        {{ language.admin_modules_menu_manage_header_menu }}: {{ modhead.data.name }}[{{ modhead.data.tag }}]
        <a href="?object=modules&action=menu&make=itemadd&id={{ modhead.data.id }}&owner=0" class="btn btn-default btn-sm"><i class="fa fa-plus"></i></a>
    </div>
    <div class="panel-body">
        {% if modmenu.item %}
        <div class="table-responsive">
            <table class="table table-line table-striped table-hover">
                <thead>
                <tr>
                    <th class="col-lg-6">{{ language.admin_modules_menu_manage_th_title }}</th>
                    <th class="col-lg-3">{{ language.admin_modules_menu_manage_th_url }}</th>
                    <th class="col-lg-1 text-center">{{ language.admin_modules_menu_manage_th_sort }}</th>
                    <th class="col-lg-2 text-center">{{ language.admin_modules_menu_manage_th_manage }}</th>
                </tr>
                </thead>
                <tbody>
                {% for row in modmenu.item %}
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-lg-12">
                                    <i class="fa fa-home"></i> {{ row.name }}
                                </div>
                            </div>
                        </td>
                        <td><a href="{{ row.url }}" target="_blank">{{ row.url|slice(0,30) }}{% if row.url|length > 30 %}[...]{% endif %}</a></td>
                        <td class="text-center">{{ row.priority }}</td>
                        <td class="text-center">
                            <a href="?object=modules&action=menu&make=itemadd&id={{ modhead.data.id }}&owner={{ row.id }}" class="btn btn-default"><i class="fa fa-plus"></i></a>
                            <a href="?object=modules&action=menu&make=itemedit&id={{ modhead.data.id }}&owner={{ row.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                            <a href="?object=modules&action=menu&make=itemdelete&id={{ modhead.data.id }}&owner={{ row.id }}" class="btn btn-default"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    {% if row.depend_array %}
                        {% for depend in row.depend_array %}
                            <tr>
                                <td>
                                    <div class="row">
                                        <div class="col-lg-offset-1 col-lg-11">
                                            <i class="fa fa-long-arrow-right"></i> {{ depend.name }}
                                        </div>
                                    </div>
                                </td>
                                <td><a href="{{ depend.url }}" target="_blank">{{ depend.url|slice(0,30) }}{% if depend.url|length > 30 %}[...]{% endif %}</a></td>
                                <td class="text-center">{{ depend.priority }}</td>
                                <td class="text-center">
                                    <a href="?object=modules&action=menu&make=dependedit&id={{ modhead.data.id }}&did={{ depend.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                                    <a href="?object=modules&action=menu&make=dependdelete&id={{ modhead.data.id }}&did={{ depend.id }}" class="btn btn-default"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        {% endfor %}
                    {% endif %}
                {% endfor %}
                </tbody>
            </table>
        </div>
        {% else %}
        <p class="alert alert-warning">{{ language.admin_modules_menu_manage_empty }}</p>
        {% endif %}
    </div>
</div>