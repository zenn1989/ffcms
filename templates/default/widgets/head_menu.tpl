<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <div class="nav-collapse">
                <ul class="nav">
                    <li><a href="{{ system.url }}"> {{ language.position_header_main }}</a></li>
                    <li><a href="{{ system.url }}/news/"> {{ language.position_header_news }}</a></li>
                    <li><a href="{{ system.url }}/user"> {{ language.position_header_users }}</a></li>
                    <li><a href="{{ system.url }}/feedback/">{{ language.feedback_form_title }}</a></li>
                    <li><a href="{{ system.script_url }}/api.php?iface=front&object=changelanguage&to=en"><img class="flag flag-en" src="{{ system.script_url }}/resource/flags/blank.gif" /></a></li>
                    <li><a href="{{ system.script_url }}/api.php?iface=front&object=changelanguage&to=ru"><img class="flag flag-ru" src="{{ system.script_url }}/resource/flags/blank.gif" /></a></li>
                </ul>
                <ul class="nav pull-right">
                    {% if user.id > 0 %}
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> {{ user.name }} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ system.url }}/user/id{{ user.id }}">{{ language.position_header_toprofile }}</a></li>
                            <li><a href="{{ system.url }}/user/id{{ user.id }}/messages">{{ language.position_header_messages }} ({{ module.message_new_count }})</a></li>
                            <li><a href="{{ system.url }}/user/id{{ user.id }}/friends/request">{{ language.position_header_friendrequests }} ({{ module.friendrequest_new_count }})</a></li>
                            <li class="divider"></li>
                            <li><a href="{{ system.url }}/user/logout.html">{{ language.usercontrol_menu_exit }}</a></li>
                        </ul>
                    </li>
                    {% if user.admin %}
                    <li class="navbar-text"><a href="{{ system.script_url }}/admin.php">{{ language.position_header_adminpanel }}</a></li>
                    {% endif %}
                    {% endif %}
                    {% if user.id < 1 %}
                    <li class="navbar-text"><a href="{{ system.url }}/user/register.html">{{ language.usercontrol_menu_reg }}</a></li>
                    <li class="navbar-text"><a href="{{ system.url }}/user/login.html">{{ language.usercontrol_menu_auth }}</a></li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </div>
</div>