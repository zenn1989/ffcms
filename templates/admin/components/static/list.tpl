{% import 'macro/scriptdata.tpl' as scriptdata %}
<h1>{{ extension.title }}<small>{{ language.admin_component_static_control }}</small></h1>
<hr />
{% include 'components/static/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-12">
        <div class="pull-right">
            <form action="" method="post" class="form-inline" role="form">
                <div class="form-group">
                    <input type="text" name="search" placeHolder="Data..." value="{{ search.value }}" class="form-control" />
                </div>
                <div class="form-group">
                    <input value="{{ language.admin_component_static_search_button }}" type="submit" name="dosearch" class="btn btn-primary form-control"/>
                </div>
            </form>
        </div>
    </div>
</div>
{% if static %}
<form action="" method="post" onsubmit="return confirm('{{ language.admin_onsubmit_warning }}');">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>{{ language.admin_component_static_th_id }}</th>
                <th>{{ language.admin_component_static_th_title }}</th>
                <th>{{ language.admin_component_static_th_path }}</th>
                <th>{{ language.admin_component_static_th_date }}</th>
                <th>{{ language.admin_component_static_th_edit }}</th>
            </tr>
            </thead>
            <tbody>
            {% for item in static %}
                <tr class="checkbox-depend">
                    <td><input type="checkbox" name="check_array[]" class="check_array" value="{{ item.id }}"/> {{ item.id }}</td>
                    <td><a href="?object=components&action=static&make=edit&id={{ item.id }}">{{ item.title }}</a></td>
                    <td><a href="{{ system.url }}/static/{{ item.path }}" target="_blank">/static/{{ item.path }}</a></td>
                    <td>{{ item.date }}</td>
                    <td class="text-center">
                        <a href="?object=components&action=static&make=edit&id={{ item.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
                        <a href="?object=components&action=static&make=delete&id={{ item.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
<a id="checkAll" class="btn btn-default">{{ language.admin_checkbox_all }}</a>
<input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
<input type="submit" name="deleteSelected" value="{{ language.admin_checkbox_delselect }}" class="btn btn-danger" />
{{ scriptdata.checkjs('#checkAll', '.check_array') }}
</form>
{{ pagination }}
{% else %}
<p>{{ language.admin_component_static_nofound }}</p>
{% endif %}