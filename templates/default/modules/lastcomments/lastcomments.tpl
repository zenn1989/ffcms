<div class="well">
    <h4>{{ language.lastcomments_title }}</h4>
    {% if local.comment %}
    <ul>
        {% for comment in local.comment %}
        <li>
            <a href="{{ system.url }}/user/id{{ comment.user_id }}">{{ comment.user_name }}</a> <i class="icon-pencil"></i>
            <a href="{{ system.url }}{{ comment.uri }}#comment_list">{{ comment.preview }}</a>
        </li>
        {% endfor %}
    </ul>
    {% else %}
        {{ language.lastcomments_notfound }}
    {% endif %}
</div>