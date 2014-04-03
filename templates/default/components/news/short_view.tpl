{% if page_title|length > 0 %} {# for module news on main - have no title #}
    <ol class="breadcrumb">
        <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
        <li><a href="{{ system.url }}/news/">{{ language.news_view_category_unset }}</a></li>
        {% if page_link != null %}
            <li class="active">{{ page_title }}</li>
        {% endif %}
    </ol>
    <h1>{{ page_title }}</h1>
    {% if page_desc|length > 0 %}
        <p>{{ page_desc }}</p>
    {% endif %}
    <hr />
{% endif %}
{% for newsdata in local %}
    <article class="article-item">

        <h2><a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}">{{ newsdata.title }}</a></h2>
        <div class="meta">
            <span><i class="fa fa-list"></i><a href="{{ system.url }}/news/{{ newsdata.category_url }}">{{ newsdata.category_name }}</a></span>
            <span><i class="fa fa-calendar"></i>{{ newsdata.date }}</span>
            <span><i class="fa fa-user"></i><a href="{{ system.url }}/user/id{{ newsdata.author_id }}">{{ newsdata.author_nick }}</a></span>
            {% if cfg.view_count %}
                <span><i class="fa fa-eye"></i> {{ newsdata.view_count }}</span>
            {% endif %}
        </div>
        <div class="row">
            <div class="col-md-12">
                {% if newsdata.poster %}
                    <img src="{{ newsdata.poster }}" class="image_poster" />
                {% endif %}
                {{ newsdata.text }}
            </div>
        </div>
        <div class="meta">
            {% if newsdata.tags and cfg.view_tags %}
            <span><i class="fa fa-tags"></i>
                    {% for tag in newsdata.tags %}
                        <a href="{{ system.url }}/news/tag/{{ tag|url_encode }}.html">{{ tag }}</a>{% if not loop.last %},{% endif %}
                    {% endfor %}
            </span>
            {% endif %}
            <span><i class="fa fa-comments"></i> <a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}#comment_load">{{ language.comments_text_title }} : {{ newsdata.comment_count }}</a></span>
            <span class="pull-right">
                <i class="fa fa-share"></i><a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}">{{ language.news_view_button_more }}</a>
            </span>
        </div>
    </article>
{% endfor %}
{{ pagination }}