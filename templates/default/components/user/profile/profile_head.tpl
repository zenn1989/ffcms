<div class="row">
<div class="col-lg-12">
    <h2>{{ local.profile.user_name }}
        <small>
            "{{ local.profile.user_status }}"
            {% if local.profile.is_self %}<a href="{{ system.url }}/user/id{{ local.profile.user_id }}/settings/status"><i class="fa fa-refresh"></i></a>{% endif %}
        </small>
    </h2>
</div>
</div>
<hr/>
<div class="row">
    <div class="col-lg-4">
        <img src="{{ system.script_url }}/{{ local.profile.user_avatar }}" class="img-responsive"/><br/>
        <ul class="nav nav-pills nav-stacked">
            {% if user.id > 0 %}
                {% if local.profile.is_self %}
                    <li{% if local.path == 'avatar' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/avatar"><i class="fa fa-camera"></i> {{ language.usercontrol_profile_mymenu_avachange }}</a></li>
                    <li{% if local.path == 'messages' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages"><i class="fa fa-envelope"></i> {{ language.usercontrol_profile_mymenu_personalmsg }}</a></li>
                    {% if local.profile.show_usernews %}
                    <li{% if local.path == 'news' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/news"><i class="fa fa-pencil-square-o"></i> {{ language.usercontrol_profile_mymenu_newspublic }}</a></li>
                    {% endif %}
                    <li{% if local.path == 'settings' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/settings"><i class="fa fa-cogs"></i> {{ language.usercontrol_profile_mymenu_settings }}</a></li>
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
    <div class="col-lg-8">
        <div class="tabbable">
            <ul class="nav nav-tabs nav-justified">
                <li{% if local.path == '' or local.path == 'wall' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}"><i class="icon-home"></i> {{ language.usercontrol_profile_menu_wall }}</a></li>
                <li{% if local.path == 'bookmarks' %} class="active"{% endif %}><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/bookmarks"><i class="icon-pencil"></i> {{ language.usercontrol_profile_menu_marks }}</a></li>
                <li class="dropdown{% if local.path == 'friends' %} active{% endif %}">
                    <a href="#" data-toggle="dropdown">
                        <i class="icon-user"></i> {{ language.usercontrol_profile_menu_friends }}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/list"> {{ language.usercontrol_profile_menu_friends_itemlist }}</a></li>
                        {% if local.profile.is_self %}
                            <li><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/request"> {{ language.usercontrol_profile_menu_friends_itemrequest }}</a></li>
                        {% endif %}
                    </ul>
                </li>
                {% if local.profile.add_menu %}
                <li class="dropdown">
                    <a href="#" data-toggle="dropdown">
                        <i class="icon-share-alt"></i> {{ language.usercontrol_profile_menu_more }}
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        {% for addmenu in local.profile.add_menu %}
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
            {% endif %}
        </div>
    </div>
</div>