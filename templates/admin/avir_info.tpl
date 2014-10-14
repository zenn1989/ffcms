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
            <li{% if system.get_data.action == null %} class="active"{% endif %}><a href="?object=antivirus">{{ language.admin_antivirus_exclude_tomain }}</a></li>
            <li{% if system.get_data.action == 'rescan' %} class="active"{% endif %}><a href="?object=antivirus&action=rescan">{{ language.admin_antivirus_rescan }}</a></li>
            <li><a href="?object=antivirus&action=exclude">{{ language.admin_antivirus_exclude_onlist }}</a></li>
        </ul>
    </div>
</nav>
<div class="table table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Файл</th>
            <th>Информация</th>
            <th>Пояснение</th>
        </tr>
        </thead>
        <tbody>
        {% for dir,md5 in scan.inject %}
            <tr class="alert-danger">
                <td><strong>/{{ dir }}</strong><br />
                    <small><abbr title="md5 sum">{{ md5 }}</abbr></small>
                </td>
                <td><i class="fa fa-bug"></i> {{ language.admin_antivirus_injected_h5 }}</td>
                <td>{{ language.admin_antivirus_injected_desc }}</td>
            </tr>
        {% endfor %}
        {% for dir,md5 in scan.md5wrong %}
            <tr class="alert-warning">
                <td><strong>/{{ dir }}</strong><br />
                    <small><abbr title="md5 sum">{{ md5 }}</abbr></small>
                </td>
                <td><i class="fa fa-pencil-square-o"></i> {{ language.admin_antivirus_wrong_h5 }}</td>
                <td>{{ language.admin_antivirus_wrong_desc }}</td>
            </tr>
        {% endfor %}
        {% for dir,md5 in scan.notfound %}
            <tr class="alert-warning">
                <td><strong>/{{ dir }}</strong><br />
                    <small><abbr title="md5 sum">{{ md5 }}</abbr></small>
                </td>
                <td><i class="fa fa-eye-slash"></i> {{ language.admin_antivirus_unknown_h5 }}</td>
                <td>{{ language.admin_antivirus_unknown_desc }}</td>
            </tr>
        {% endfor %}
        {% for dir,md5 in scan.success %}
            <tr class="alert-success{% if loop.index > 10 %} hidden special-hide{% endif %}">
                <td>/{{ dir }}<br />
                    <small><abbr title="md5 sum">{{ md5 }}</abbr></small>
                </td>
                <td><i class="fa fa-check"></i> {{ language.admin_antivirus_safe_h5 }}</td>
                <td>{{ language.admin_antivirus_safe_desc }}</td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
</div>
<div class="row" id="showhidden_block">
    <div class="col-md-12">
        <div class="text-center"><a href="#showhidden_block" class="btn btn-success" id="showhidden_btn"><i class="fa fa-arrow-down"></i> {{ language.admin_antivirus_showall_secure_files }}</a></div>
    </div>
</div>
<script>
    $(function(){
       $('#showhidden_btn').click(function(){
            $('.special-hide').removeClass('hidden');
            $('#showhidden_block').addClass('hidden');
       });
    });
</script>