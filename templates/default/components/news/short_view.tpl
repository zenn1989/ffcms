{% if page_title|length > 0 %} {# for module news on main - have no title #}
<h1>{{ page_title }}</h1>
<hr class="soften" />
{% endif %}
{% for newsdata in local %}
    <div class="well-item">
        <h2><a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}" class="blacklnk">{{ newsdata.title }}</a></h2>
        <div class="pull-left">
             <a href="{{ system.url }}/news/{{ newsdata.category_url }}" class="btn btn-small"><i class="icon-list"></i> {{ newsdata.category_name }}</a>
             <span class="btn btn-small"><i class="icon-calendar"></i> {{ newsdata.date }}</span>
        </div>
        <div class="pull-right">
             <a href="{{ system.url }}/user/id{{ newsdata.author_id }}" class="btn btn-small"><i class="icon-pencil"></i> {{ language.news_view_author }}: {{ newsdata.author_nick }}</a>
             <a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}#comment_load" class="btn btn-small"><i class="icon-comment"></i> {{ language.comments_text_title }}: {{ newsdata.comment_count }}</a>
        </div>
        <br /><hr/>
        <div>
            {{ newsdata.text }}
        </div>
        <br/>
        <div class="pull-left">
        {% if newsdata.tags and cfg.view_tags %}
            <i class="icon-tags"></i>
            {% for tag in newsdata.tags %}
                <a href="{{ system.url }}/news/tag/{{ tag|url_encode }}.html" class="label">{{ tag }}</a>
            {%  endfor %}
        {% endif %}
        </div>
        <div class="pull-right">
            {% if cfg.view_count %}
                <span class="btn btn-small"><i class="icon-eye-open"></i> {{ newsdata.view_count }}</span>
            {% endif %}
            <a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}" class="btn btn-small"><i class="icon-share-alt"></i> {{ language.news_view_button_more }}</a>
        </div>
        <br />
    </div>
{% endfor %}
{{ pagination }}