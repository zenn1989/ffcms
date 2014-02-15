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
            <li><a href="?object=antivirus&action=exclude">{{ language.admin_antivirus_exclude_onlist }}</a></li>
        </ul>
    </div>
</nav>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-danger">
            <div class="panel-heading">{{ language.admin_antivirus_injected_h5 }}</div>
            <div class="panel-body">
                <p>{{ language.admin_antivirus_injected_desc }}</p>
                {% for dir,md5 in scan.inject %}
                    <p class="alert alert-danger">/{{ dir }} => {{ md5 }}</p>
                {% else %}
                    <p class="alert alert-success">{{ language.admin_antivirus_cleaall }}</p>
                {% endfor %}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-warning">
            <div class="panel-heading">{{ language.admin_antivirus_wrong_h5 }}</div>
            <div class="panel-body">
                <p>{{ language.admin_antivirus_wrong_desc }}</p>
                {% for dir,md5 in scan.md5wrong %}
                    <p class="alert alert-danger">/{{ dir }} => {{ md5 }}</p>
                {% else %}
                    <p class="alert alert-success">{{ language.admin_antivirus_cleaall }}</p>
                {% endfor %}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-info">
            <div class="panel-heading">{{ language.admin_antivirus_unknown_h5 }}</div>
            <div class="panel-body">
                <p>{{ language.admin_antivirus_unknown_desc }}</p>
                {% for dir,md5 in scan.notfound %}
                    <p class="alert alert-warning">/{{ dir }} => {{ md5 }}</p>
                {% else %}
                    <p class="alert alert-success">{{ language.admin_antivirus_cleaall }}</p>
                {% endfor %}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-success">
            <div class="panel-heading">{{ language.admin_antivirus_safe_h5 }}</div>
            <div class="panel-body">
                <p>{{ language.admin_antivirus_safe_desc }}</p>
                {% for dir,md5 in scan.success %}
                    <p class="alert alert-success">/{{ dir }} => {{ md5 }}</p>
                {% endfor %}
            </div>
        </div>
    </div>
</div>