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
            <li{% if system.get_data.make == null %} class="active"{% endif %}><a href="?object=components&action=user">{{ language.admin_component_usercontrol_manage }}</a></li>
            <li{% if system.get_data.make == 'grouplist' %} class="active"{% endif %}><a href="?object=components&action=user&make=grouplist">{{ language.admin_component_usercontrol_group }}</a></li>
            <li{% if system.get_data.make == 'ufield' %} class="active"{% endif %}><a href="?object=components&action=user&make=ufield">{{ language.admin_component_usercontrol_ufields_menutitle }}</a></li>
            <li{% if system.get_data.make == 'banlist' %} class="active"{% endif %}><a href="?object=components&action=user&make=banlist">{{ language.admin_component_usercontrol_serviceban }}</a></li>
            <li{% if system.get_data.make == 'settings' %} class="active"{% endif %}><a href="?object=components&action=user&make=settings">{{ language.admin_component_usercontrol_settings }}</a></li>
        </ul>
    </div>
</nav>