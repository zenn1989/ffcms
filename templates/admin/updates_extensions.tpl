{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_update_extension_title }}</h1>
<hr />
<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">{{ language.admin_update_menu_title }}</a>
    </div>
    <div class="collapse navbar-collapse" id="navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li><a href="?object=updates">{{ language.admin_update_menu_system }}</a></li>
            <li class="active"><a href="?object=updates&action=extensions">{{ language.admin_update_menu_extensions }}</a></li>
        </ul>
    </div>
</nav>
{% if extinfo.notify_updated %}
    {{ notifytpl.success(language.admin_update_extension_updatesuccess) }}
{% endif %}
<table class="table table-bordered">
    <thead>
    <tr>
        <th>ID</th>
        <th>{{ language.admin_update_extension_td_type }}</th>
        <th>{{ language.admin_update_extension_td_sysname }}</th>
        <th>{{ language.admin_update_extension_td_version }}</th>
        <th>{{ language.admin_update_extension_td_compatable }}</th>
    </tr>
    </thead>
    <tbody>
    {% for item in extinfo.data %}
    <tr>
        <td>{{ item.id }}</td>
        <td>{{ item.type }}</td>
        <td>{{ item.name }}</td>
        {% if item.db_version == null or item.script_version == null %}
            <td class="alert alert-danger">null</td>
        {% else %}
            <td class="{% if item.db_version == item.script_version %}alert alert-success{% else %}alert alert-warning{% endif %}">
                {% if item.script_version > item.db_version %}
                    <span class="label label-danger">{{ item.db_version }}</span> / <span class="label label-primary">{{ item.script_version }}</span>
                {% else %}
                    <span class="label label-default">{{ item.db_version }}</span> / <span class="label label-default">{{ item.script_version }}</span>
                {% endif %}
            </td>
        {% endif %}
        {% if item.db_compatable == null or item.script_compatable == null %}
        <td class="alert alert-danger">null</td>
        {% else %}
            <td class="{% if item.db_compatable != system.version %}alert alert-danger{% else %}alert alert-success{% endif %}">
                {% if item.db_compatable != system.version %}
                    <span class="label label-warning">{{ item.db_compatable }}</span> / <span class="label label-primary">{{ item.script_compatable }}</span>
                {% else %}
                    <span class="label label-default">{{ item.db_compatable }}</span> / <span class="label label-default">{{ item.script_compatable }}</span>
                {% endif %}
            </td>
        {% endif %}
    </tr>
    {% endfor %}
    </tbody>
</table>
{{  notifytpl.warning(language.admin_update_extension_notifyrun) }}
<form action="" method="post">
    <input type="submit" name="submit" class="btn btn-success" value="{{ language.admin_update_extension_runupdate }}">
</form>