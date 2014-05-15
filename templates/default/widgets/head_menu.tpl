<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ system.url }}">{{ language.position_header_main }}</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="{{ system.url }}/news/"> {{ language.position_header_news }}</a></li>
                <li><a href="{{ system.url }}/static/about.html">{{ language.position_subheader_about }}</a></li>
                <li><a href="{{ system.url }}/user"> {{ language.position_header_users }}</a></li>
                <li><a href="{{ system.url }}/feedback/">{{ language.feedback_form_title }}</a></li>
                {% for langitem in system.languages %}
                    <li><a href="{{ system.nolang_url }}/{{ langitem }}/{{ system.uri }}"><img class="flag flag-{{ langitem }}" src="{{ system.script_url }}/resource/flags/blank.gif" /></a></li>
                {% endfor %}
            </ul>
            <ul class="nav navbar-nav navbar-right">
                {% if user.id > 0 %}
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> {{ user.name }} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ system.url }}/user/id{{ user.id }}">{{ language.position_header_toprofile }}</a></li>
                            <li><a href="{{ system.url }}/user/id{{ user.id }}/messages">{{ language.position_header_messages }} ({{ module.message_new_count }})</a></li>
                            <li><a href="{{ system.url }}/user/id{{ user.id }}/friends/request">{{ language.position_header_friendrequests }} ({{ module.friendrequest_new_count }})</a></li>
                            {% if user.news_add %}
                            <li><a href="{{ system.url }}/news/add">{{ language.news_add_menu_title }}</a></li>
                            {% endif %}
                            <li class="divider"></li>
                            <li><a href="{{ system.url }}/user/logout.html">{{ language.usercontrol_menu_exit }}</a></li>
                        </ul>
                    </li>
                    {% if user.admin %}
                        <li><a href="{{ system.script_url }}/admin.php">{{ language.position_header_adminpanel }}</a></li>
                    {% endif %}
                {% endif %}
                {% if user.id < 1 %}
                    <li><a href="{{ system.url }}/user/register.html">{{ language.usercontrol_menu_reg }}</a></li>
                    <li><a href="{{ system.url }}/user/login.html">{{ language.usercontrol_menu_auth }}</a></li>
                {% endif %}
            </ul>
        </div>
    </div>
</div>