{% import 'macro/scriptdata.tpl' as scriptdata %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_comment_manage_title }}</small></h1>
<hr />
{% include 'modules/comments/menu_include.tpl' %}
<div class="pull-left">
    <div class="btn-group">
        <button type="button" class="btn btn-default">{{ language.admin_modules_comment_filter_title }}</button>
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li><a href="?object=modules&action=comments&make=list&filter=0">{{ language.admin_modules_comment_filter_all }}</a></li>
            <li><a href="?object=modules&action=comments&make=list&filter=1">{{ language.admin_modules_comment_filter_moderate }}</a></li>
        </ul>
    </div>
</div>
{% if comments.list %}
<form action="" method="post" onsubmit="return confirm('{{ language.admin_onsubmit_warning }}');">
<table class="table table-responsive table-bordered">
    <thead>
    <tr>
        <th>{{ language.admin_modules_comment_th1 }}</th>
        <th>{{ language.admin_modules_comment_th2 }}</th>
        <th>{{ language.admin_modules_comment_th3 }}</th>
        <th>{{ language.admin_modules_comment_th4 }}</th>
        <th>{{ language.admin_modules_comment_th5 }}</th>
        <th>{{ language.admin_modules_comment_th6 }}</th>
    </tr>
    </thead>
    <tbody>
    {% for item in comments.list %}
    <tr>
        <td><input type="checkbox" name="check_array[]" class="check_array" value="{{ item.id }}"/> {{ item.id }}</td>
        <td>{% if item.user_id < 1 %}
            {{ item.guest_name }}
            {% else %}
            {{ item.user_name }}
            {% endif %}
        </td>
        <td>{{ item.comment|striptags|escape }}</td>
        <td>{{ item.date }}</td>
        <td><a href="{{ system.script_url }}{{ item.uri }}">{{ item.uri }}</a></td>
        <td class="text-center">
            <a href="?object=modules&action=comments&make=edit&id={{ item.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
            <a href="?object=modules&action=comments&make=delete&id={{ item.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
            {% if item.moderate %}
                <a href="?object=modules&action=comments&make=aprove&id={{ item.id }}" title="Aprove"><i class="fa fa-check fa-lg"></i></a>
            {% else %}
                <a href="?object=modules&action=comments&make=hide&id={{ item.id }}" title="hide"><i class="fa fa-lock fa-lg"></i></a>
            {% endif %}
        </td>
    </tr>
    {% endfor %}
    </tbody>
</table>
<a id="checkAll" class="btn btn-default">{{ language.admin_checkbox_all }}</a>
<input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
<input type="submit" name="deleteSelected" value="{{ language.admin_checkbox_delselect }}" class="btn btn-danger" />
{{ scriptdata.checkjs('#checkAll', '.check_array') }}
</form>
{{ pagination }}
{% endif %}