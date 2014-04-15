<div class="panel panel-default">
    <div class="panel-body">
        <h4>{{ language.lastcomments_title }}</h4>
        {% if local.comment %}
            {% for comment in local.comment %}
                {% if comment.user_id < 1 %}
                    {{ comment.guest_name }}
                {% else %}
                    <a href="{{ system.url }}/user/id{{ comment.user_id }}">{{ comment.user_name }}</a>
                {% endif %}
                <i class="fa fa-pencil"></i>
                &laquo;<a href="{{ system.nolang_url }}{{ comment.uri }}#comment_list">{{ comment.preview }}</a>&raquo;, {{ comment.date }}
                <hr class="commenttype" />
            {% endfor %}
        {% else %}
            {{ language.lastcomments_notfound }}
        {% endif %}
    </div>
</div>