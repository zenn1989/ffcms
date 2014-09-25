{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_update_system_title }}</h1>
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
            <li class="active"><a href="?object=updates">{{ language.admin_update_menu_system }}</a></li>
            <li><a href="?object=updates&action=extensions">{{ language.admin_update_menu_extensions }}</a></li>
        </ul>
    </div>
</nav>
{% if extinfo.ffsite_down %}
    {{ notifytpl.error(language.admin_update_system_notify_repodown) }}
{% endif %}
<table class="table table-striped">
    <tr>
        <td>{{ language.admin_update_system_local_version }}</td>
        <td><span class="label{% if not extinfo.update_available %} label-success{% else %} label-warning{% endif %}">{{ system.version }}</span></td>
    </tr>
    <tr>
        <td>{{ language.admin_update_system_remote_version }}</td>
        <td><span class="label{% if not extinfo.update_available %} label-success{% else %} label-primary{% endif %}">{{ extinfo.ff_lastversion|default('error') }}</span></td>
    </tr>
</table>
{% if extinfo.notifysubmit %}
    {% if extinfo.notifyfail %}
        {{ notifytpl.error(language.admin_update_system_updatefail) }}
    {% else %}
        {{ notifytpl.success(language.admin_update_system_updatesuccess) }}
        <p class="alert alert-danger"><a href="{{ system.script_url }}/install/index.php?action=update">Update manager</a></p>
    {% endif %}
{% endif %}
{% if extinfo.update_available %}
    {% if not extinfo.notifysubmit %}
        {{ notifytpl.warning(language.admin_update_system_notify_access) }}
    {% endif %}
    <form action="" method="post" class="form-horizontal" role="form">
        <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
        <div class="form-group">
            <label class="col-lg-3 control-label">{{ language.admin_update_system_repo_name }}</label>
            <div class="col-lg-9">
                <select name="repo_name" class="form-control">
                    {% for repo in extinfo.repos %}
                        <option value="{{ repo.name }}">{{ repo.name }}</option>
                    {% endfor %}
                </select>
            </div>
        </div>
        <div class="col-lg-9 col-lg-offset-3"><input type="submit" name="submit" value="{{ language.admin_update_system_run_update }}" class="btn btn-primary" /></div>
    </form>
{% endif %}