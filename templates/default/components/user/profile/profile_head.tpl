<div class="row">
<div class="col-md-12">
    <h2>{{ local.profile.user_name }}
        <small>
            {% if local.profile.user_status|length > 0 %}
            &laquo;{{ local.profile.user_status }}&raquo;
            {% endif %}
            {% if local.profile.is_self %}<a href="{{ system.url }}/user/id{{ local.profile.user_id }}/settings/status"><i class="fa fa-refresh"></i></a>{% endif %}
        </small>
    </h2>
</div>
</div>
<hr/>
<div class="row">
    <div class="col-md-4">
        <img src="{{ system.script_url }}/{{ local.profile.user_avatar }}" class="img-responsive"/><br/>
        {% if local.profile.use_karma %}
        <div class="text-center">
            <div class="btn-group">
                {% if local.profile.karma > 0 %}
                    <button type="button" class="btn btn-success"{% if local.profile.is_self %} data-toggle="modal" data-target="#karmahistory"{% endif %}>{{ language.usercontrol_profile_karma_title }}: <span id="karmabutton">+{{ local.profile.karma }}</span></button>
                {% elseif local.profile.karma == 0 %}
                    <button type="button" class="btn btn-warning"{% if local.profile.is_self %} data-toggle="modal" data-target="#karmahistory"{% endif %}>{{ language.usercontrol_profile_karma_title }}: <span id="karmabutton">{{ local.profile.karma }}</span></button>
                {% else %}
                    <button type="button" class="btn btn-danger"{% if local.profile.is_self %} data-toggle="modal" data-target="#karmahistory"{% endif %}>{{ language.usercontrol_profile_karma_title }}: <span id="karmabutton">{{ local.profile.karma }}</span></button>
                {% endif %}
                {% if user.id > 0 and not local.profile.is_self %}
                <button type="button" class="btn btn-success" onclick="return changeKarma(1, {{ local.profile.user_id }}, '{{ system.self_url }}');"><i class="fa fa-thumbs-up"></i> +1</button>
                <button type="button" class="btn btn-danger" onclick="return changeKarma(0, {{ local.profile.user_id }}, '{{ system.self_url }}');"><i class="fa fa-thumbs-down"></i> -1</button>
                {% endif %}
            </div>
        </div>
        {% endif %}
        <ul class="nav nav-pills nav-stacked">
            {% if user.id > 0 %}
                {% if local.profile.is_self %}
                    <li{% if local.path == 'avatar' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/avatar"><i class="fa fa-camera"></i> {{ language.usercontrol_profile_mymenu_avachange }}</a></li>
                    <li{% if local.path == 'messages' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages"><i class="fa fa-envelope"></i> {{ language.usercontrol_profile_mymenu_personalmsg }}</a></li>
                    {% if local.profile.show_usernews %}
                    <li{% if local.path == 'news' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/news"><i class="fa fa-pencil-square-o"></i> {{ language.usercontrol_profile_mymenu_newspublic }}</a></li>
                    {% endif %}
                    {% if local.profile.show_balance %}
                        <li{% if local.path == 'balance' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/balance"><i class="fa fa-money"></i> {{ language.usercontrol_profile_settings_tab_balance }}</a></li>
                    {% endif %}
                    <li{% if local.path == 'settings' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/settings"><i class="fa fa-cogs"></i> {{ language.usercontrol_profile_mymenu_settings }}</a></li>
                    {% if local.profile.add_menu.private %}
                        <hr />
                        {% for add_menu in local.profile.add_menu.private %}
                            <li><a href="{{ system.url }}/{{ add_menu.link }}">{{ add_menu.text }}</a></li>
                        {% endfor %}
                    {% endif %}
                {% else %}
                    {% if local.profile.is_friend %}
                        <li><a href="{{ system.url }}/user/id{{ user.id }}/messages/write/{{ local.profile.user_id }}"><i class="fa fa-envelope"></i> {{ language.usercontrol_profile_mymenu_writemsg }}</a></li>
                    {% else %}
                        {% if local.profile.is_request_friend %}
                            <li><a href="#"><i class="fa fa-hand-o-right"></i> {{ language.usercontrol_profile_mymenu_requestsended }}</a></li>
                        {% else %}
                            <form class="hidden" id="friendget" action="" method="post"><input type="hidden" name="requestfriend" value="1"/></form>
                            <li><a onclick="document.getElementById('friendget').submit();"><i class="fa fa-hand-o-right"></i>  {{ language.usercontrol_profile_mymenu_addfriend }}</a></li>
                        {% endif %}
                    {% endif %}
                {% endif %}
            {% endif %}
        </ul>
    </div>
    <div class="col-md-8">
        <div class="tabbable">
            <ul class="nav nav-tabs nav-justified">
                <li{% if local.path == '' or local.path == 'wall' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}"><i class="glyphicon glyphicon-home"></i> {{ language.usercontrol_profile_menu_wall }}</a></li>
                <li{% if local.path == 'bookmarks' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/bookmarks"><i class="glyphicon glyphicon-pencil"></i> {{ language.usercontrol_profile_menu_marks }}</a></li>
                <li class="dropdown{% if local.path == 'friends' %} active{% endif %}">
                    <a href="#" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i> {{ language.usercontrol_profile_menu_friends }}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/list"> {{ language.usercontrol_profile_menu_friends_itemlist }}</a></li>
                        {% if local.profile.is_self %}
                            <li><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/request"> {{ language.usercontrol_profile_menu_friends_itemrequest }}</a></li>
                        {% endif %}
                    </ul>
                </li>
                {% if local.profile.add_menu.public %}
                <li class="dropdown">
                    <a href="#" data-toggle="dropdown">
                        <i class="icon-share-alt"></i> {{ language.usercontrol_profile_menu_more }}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        {% for addmenu in local.profile.add_menu.public %}
                            <li><a href="{{ system.url }}/{{ addmenu.link }}">{{ addmenu.text }}</a></li>
                        {% endfor %}
                    </ul>
                </li>
                {% endif %}
            </ul>
            <br />
            {# include sub templates #}
            {% if local.path == '' or local.path == 'wall' %}
                {% include 'components/user/profile/profile_wall.tpl' %}
            {% elseif local.path == 'bookmarks' %}
                {% include 'components/user/profile/profile_bookmarks.tpl' %}
            {% elseif local.path == 'friends' %}
                {% if local.action == 'list' %}
                    {% include 'components/user/profile/profile_friendlist.tpl' %}
                {% elseif local.action == 'request' %}
                    {% include 'components/user/profile/profile_friendrequest.tpl' %}
                {% elseif local.action == 'delete' %}
                    {% include 'components/user/profile/profile_frienddelete.tpl' %}
                {% endif %}
            {% elseif local.path == 'avatar' %}
                {% include 'components/user/profile/profile_photo.tpl' %}
            {% elseif local.path == 'settings' %}
                {% if local.action == '' %}
                    {% include 'components/user/profile/profile_settings.tpl' %}
                {% elseif local.action == 'status' %}
                    {% include 'components/user/profile/profile_status.tpl' %}
                {% elseif local.action == 'logs' %}
                    {% include 'components/user/profile/profile_logs.tpl' %}
                {% endif %}
            {% elseif local.path == 'messages' %}
                {% if local.action == '' or local.action == 'all' or local.action == 'in' or local.action == 'out' %}
                    {% include 'components/user/profile/profile_messages.tpl' %}
                {% elseif local.action == 'write' %}
                    {% include 'components/user/profile/profile_writemessage.tpl' %}
                {% elseif local.action == 'topic' %}
                    {% include 'components/user/profile/profile_topic.tpl' %}
                {% endif %}
            {% elseif local.path == 'news' %}
                {% include 'components/user/profile/profile_newslist.tpl' %}
            {% elseif local.path == 'balance' %}
                {% include 'components/user/profile/profile_balance.tpl' %}
            {% endif %}
        </div>
    </div>
</div>
{% if local.profile.use_karma and local.profile.is_self %}
<div class="modal fade modalkarma" tabindex="-1" role="dialog" id="karmahistory" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">{{ language.usercontrol_profile_karma_changehistory_title }}</h3>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <p>{{ language.usercontrol_profile_karma_changehistory_desc }}</p>
                    {% if local.profile.karma_history %}
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>{{ language.usercontrol_profile_karma_th_date }}</th>
                            <th>{{ language.usercontrol_profile_karma_th_changes }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {% for karmahis in local.profile.karma_history %}
                            <tr>
                                <td>{{ karmahis.date }}</td>
                                {% if karmahis.type == 1 %}
                                <td class="alert alert-success">+1 <i class="fa fa-thumbs-up"></i></td>
                                {% else %}
                                <td class="alert alert-danger">-1 <i class="fa fa-thumbs-down"></i></td>
                                {% endif %}
                            </tr>
                        {% endfor %}
                        </tbody>
                    </table>
                    {% else %}
                        <p class="alert alert-warning">{{ language.usercontrol_profile_karma_nochanges }}</p>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>
{% endif %}