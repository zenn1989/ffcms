{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_nav_li_avir }}</h1>
<hr />
<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">{{ language.admin_antivirus_headmenu }}</a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li><a href="?object=antivirus">{{ language.admin_antivirus_exclude_tomain }}</a></li>
            <li><a href="?object=antivirus&action=rescan">{{ language.admin_antivirus_rescan }}</a></li>
            <li{% if system.get_data.action == 'exclude' %} class="active"{% endif %}><a href="?object=antivirus&action=exclude">{{ language.admin_antivirus_exclude_onlist }}</a></li>
        </ul>
    </div><!-- /.navbar-collapse -->
</nav>
<br />
<p>{{ language.admin_antivirus_exclude_desc }}</p>
{% if notify.folder_add %}
    {{ notifytpl.warning(language.admin_antivirus_exclude_notify_successadd) }}
{% endif %}
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-primary">
            <div class="panel-body">
                <form action="" method="post" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{ language.admin_antivirus_exclude_label_dir }}</label>
                        <div class="col-lg-4">
                            <input type="text" name="antivir_dir" placeholder="forum" class="form-control" />
                        </div>
                        <input type="submit" name="antivir_exclude" class="btn btn-danger" value="{{ language.admin_antivirus_exclude_dosubmit }}" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        {% if antivirus.excluded %}
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>{{ language.admin_antivirus_exclude_th1 }}</th>
                <th>{{ language.admin_antivirus_exclude_th2 }}</th>
            </tr>
            </thead>
            <tbody>
                {% for exclude in antivirus.excluded %}
                    <tr>
                        <td>/{{ exclude }}</td>
                        <td><a href="?object=antivirus&action=exclude&directory={{ exclude }}"><i class="fa fa-minus-circle"></i></a></td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        {% endif %}
    </div>
</div>