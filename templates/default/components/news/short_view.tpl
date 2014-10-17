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
    <article class="article-item" itemscope="itemscope" itemtype="{{ system.protocol }}://schema.org/NewsArticle">

        <h2 itemprop="name"><a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}">{% if newsdata.important > 0 %}<i class="fa fa-paperclip"></i> {% endif %}{{ newsdata.title }}</a></h2>
        <div class="meta">
            <span><i class="fa fa-list"></i><a href="{{ system.url }}/news/{{ newsdata.category_url }}{% if newsdata.category_url|length > 0 %}/{% endif %}" itemprop="genre">{{ newsdata.category_name }}</a></span>
            <span><i class="fa fa-calendar"></i><time datetime="{{ newsdata.unixtime|date("c") }}" itemprop="datePublished">{{ newsdata.date }}</time></span>
            <span><i class="fa fa-user"></i><a href="{{ system.url }}/user/id{{ newsdata.author_id }}" itemprop="author">{{ newsdata.author_nick }}</a></span>
            {% if cfg.view_count %}
                <span><i class="fa fa-eye"></i> {{ newsdata.view_count }}</span>
            {% endif %}
        </div>
        <div class="row">
            <div class="col-md-12">
                {% if newsdata.poster %}
                    <img src="{{ newsdata.poster }}" class="image_poster" itemprop="image" />
                {% endif %}
                <div itemprop="text articleBody">
                    {{ newsdata.text }}
                </div>
            </div>
        </div>
        <div class="meta">
            {% if newsdata.tags and cfg.view_tags %}
            <span><i class="fa fa-tags"></i>
                    {% for tag in newsdata.tags %}
                        <a href="{{ system.url }}/news/tag/{{ tag }}.html">{{ tag }}</a>{% if not loop.last %},{% endif %}
                    {% endfor %}
            </span>
                <meta itemprop="keywords" content="{% for tag in newsdata.tags %}{{ tag }}{% if not loop.last %},{% endif %}{% endfor %}">
            {% endif %}
            <span><i class="fa fa-comments"></i> <a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}#comment_load">{{ language.comments_text_title }} : <span itemprop="commentCount">{{ newsdata.comment_count }}</span></a></span>
            <span class="pull-right">
                <i class="fa fa-share"></i><a href="{{ system.url }}/news/{{ newsdata.full_news_uri }}" itemprop="url">{{ language.news_view_button_more }}</a>
            </span>
        </div>
    </article>
{% endfor %}
{{ pagination }}