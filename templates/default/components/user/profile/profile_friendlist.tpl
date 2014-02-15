{% for friend in local.friend.list %}
<div class="well-item">
    <div class="media">
        {% if local.profile.is_self %}
            <div class="pull-right"><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/delete/{{ friend.user_id }}"><i class="icon-remove"></i></a></div>
        {% endif %}
        <a class="pull-left" href="{{ system.url }}/user/id{{ friend.user_id }}">
            <img style="max-width: 64px;max-height: 64px;" class="media-object" src="{{ system.script_url }}/{{ friend.user_avatar }}">
        </a>
        <div class="media-body">
            <h4 class="media-heading">{{ friend.user_name|escape|default('Deleted...') }}</h4>
            {% if local.profile.is_self %}
                <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/write/{{ friend.user_id }}" class="btn btn-success">{{ language.usercontrol_profile_pers_form_write }}</a>
            {% else %}
                <a href="{{ system.url }}/user/id{{ friend.user_id }}" class="btn btn-success">{{ language.usercontrol_profile_menu_friends_viewprofile }}</a>
            {% endif %}
        </div>
    </div>
</div>
{% else %}
<p>{{ language.global_info_missed }}</p>
{% endfor %}
<ul class="pager">
    {% if local.friend.index > 0 %}
    <li class="previous">
        <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/list/{{ local.friend.index-1 }}">&larr;</a>
    </li>
    {% endif %}
    {% if local.friend.index+1 < local.friend.total/local.friend.perpage %}
    <li class="next">
        <a href="{{ system.rul }}/user/id{{ local.profile.user_id }}/friends/list/{{ local.friend.index+1 }}">&rarr;</a>
    </li>
    {% endif %}
</ul>