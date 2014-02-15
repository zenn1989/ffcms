<div class="pull-left">
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/all" class="btn btn-success">{{ language.usercontrol_profile_pm_menuall }}</a>
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/in" class="btn btn-info">{{ language.usercontrol_profile_pm_menuin }}</a>
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/out" class="btn btn-danger">{{ language.usercontrol_profile_pm_menuout }}</a>
</div>
<div class="pull-right">
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/write" class="btn btn-success">{{ language.usercontrol_profile_pm_writenew }}</a>
</div><br/>
<hr/>
<ul class="pager">
    {% if local.pagination.index > 0 %}
    <li class="previous"><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/{{ local.action }}/{{ local.pagination.index-1 }}">&larr;</a></li>
    {% endif %}
    {% if local.pagination.index+1 < local.pagination.total/local.pagination.perpage %}
    <li class="next"><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/{{ local.action }}/{{ local.pagination.index+1 }}">&rarr;</a></li>
    {% endif %}
</ul>
{% for message in local.message %}
<div{% if message.not_readed %} class="well-item"{% else %} class="well"{% endif %}>
    <div class="media">
        <a class="pull-left" href="{{ system.url }}/user/id{{ message.user_id }}">
            <img style="max-height: 64px;max-width: 64px;" class="media-object" src="{{ system.script_url }}/{{ message.user_avatar }}">
        </a>
        <div class="media-body">
            <h4 class="media-heading"><a href="{{ system.url }}/user/id{{ message.user_id }}">{{ message.user_name }}</a>, {{ message.time }}</h4>
            <blockquote>{{ message.body }}</blockquote>
            <div class="pull-right">
                <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/topic/{{ message.topic_id }}">{{ language.usercontrol_profile_pm_readmore }}</a>
            </div>
        </div>
    </div>
</div>
{% else %}
<p>{{ language.global_info_missed }}</p>
{% endfor %}