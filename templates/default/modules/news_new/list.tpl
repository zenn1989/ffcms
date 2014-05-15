<div class="panel panel-default">
    <div class="panel-body">
        <h4>{{ language.newsnew_title }}</h4>
        {% for item in local.latest %}
            <a href="{{ item.pathway }}">{{ item.title }}</a>
            <!-- {% if item.image is not null %}<img src="{{ system.script_url }}/upload/news/{{ item.image }}" />{% endif %} -->
            <hr class="commenttype" />
        {% else %}
            {{ language.viewnews_empty }}
        {% endfor %}
    </div>
</div>