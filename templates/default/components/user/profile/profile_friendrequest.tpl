{% for friend in local.friend.request %}
<div class="well-item">
    <div class="media">
        <a class="pull-left" href="{{ system.url }}/user/id{{ friend.user_id }}">
            <img style="max-width: 64px;max-height: 64px;" class="media-object" src="{{ system.script_url }}/{{ friend.user_avatar }}">
        </a>
        <div class="media-body">
            <h4 class="media-heading">{{ friend.user_name|escape|default('Deleted...') }}</h4>
            <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/accept/{{ friend.user_id }}" class="btn btn-success">{{ language.global_accept_button }}</a>
            <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/friends/deny/{{ friend.user_id }}" class="btn btn-warning">{{ language.global_reject_button }}</a>
        </div>
    </div>
</div>
{% else %}
    <p>{{ language.global_info_missed }}</p>
{% endfor %}