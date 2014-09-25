<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">{{ language.admin_menu_word }}</a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li{% if system.get_data.make == null or system.get_data.make == 'list' %} class="active"{% endif %}><a href="?object=components&action=stream">{{ language.admin_components_stream_manage_title }}</a></li>
            <li{% if system.get_data.make == 'settings' %} class="active"{% endif %}><a href="?object=components&action=stream&make=settings">{{ language.admin_components_stream_settings_title }}</a></li>
        </ul>
    </div>
</nav>