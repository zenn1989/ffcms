<link rel="stylesheet" href="{{ system.script_url }}/resource/fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="{{ system.script_url }}/resource/fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>
<script>
    $(document).ready(function() {
        $(".fancybox").fancybox({
            openEffect	: 'elastic',
            closeEffect	: 'elastic'
        });
    });
</script>

<article class="article-item" itemscope="itemscope" itemtype="{{ system.protocol }}://schema.org/NewsArticle">
    <ol class="breadcrumb">
        <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
        <li><a href="{{ system.url }}/news/">{{ language.news_view_category_unset }}</a></li>
        {% if local.category_url != null %}
        <li><a href="{{ system.url }}/news/{{ local.category_url }}">{{ local.category_name }}</a></li>
        {% endif %}
        <li class="active">{{ local.title|slice(0,50) }}{% if local.title|length > 50 %}...{% endif %}</li>
    </ol>
    <h1>{{ local.title }}</h1>
    <div class="meta">
        <span><i class="fa fa-list"></i><a href="{{ system.url }}/news/{{ local.category_url }}/" itemprop="genre">{{ local.category_name }}</a></span>
        <span><i class="fa fa-calendar"></i><time datetime="{{ local.unixtime|date("c") }}" itemprop="datePublished">{{ local.date }}</time></span>
        <span><i class="fa fa-user"></i><a href="{{ system.url }}/user/id{{ local.author_id }}" itemprop="author">{{ local.author_nick }}</a></span>
        {% if local.cfg.view_count %}
            <span><i class="fa fa-eye"></i> {{ local.view_count }}</span>
        {% endif %}
        <span class="pull-right">
                <a href="{{ system.nolang_url }}{{ local.pathway }}?print=true" target="_blank"><i class="fa fa-print"></i></a>
        </span>
    </div>
    <div class="row">
        <div class="col-md-12">
            {% if local.poster %}
                <img src="{{ local.poster }}" class="image_poster" itemprop="image" />
            {% endif %}
            <div itemprop="text articleBody">
                {{ local.text }}
            </div>
        </div>
    </div>
    {% if local.gallery %}
    <div class="row">
        <div class="col-md-12">
            {% for image in local.gallery %}
                <div class="col-md-2">
                    <a class="fancybox thumbnail" rel="gallery" href="{{ image.full }}" title="{{ local.title }}">
                        <img src="{{ image.thumb }}" alt="" class="img-responsive" itemprop="image" />
                    </a>
                </div>
            {% endfor %}
        </div>
    </div>
    {% endif %}
    {% if local.similar_items %}
    <div class="row">
        <div class="col-md-12">
            <h3>{{ language.news_view_similar }}:</h3>

            <div class="panel-group" id="accordion">
                {% for similar in local.similar_items %}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{ loop.index }}">
                                <i class="fa fa-arrows-v"></i> {{ similar.title }}
                            </a>
                        </h4>
                    </div>
                    <div id="collapse{{ loop.index }}" class="panel-collapse collapse">
                        <div class="panel-body">
                            <a href="{{ system.url }}/news/{{ similar.link }}">{{ similar.preview }}...</a>
                        </div>
                    </div>
                </div>
                {% endfor %}
            </div>
        </div>
    </div>
    {% endif %}
    {% if local.tags and local.cfg.view_tags %}
    <div class="meta">
            <span><i class="fa fa-tags"></i>
                {% for tag in local.tags %}
                    <a href="{{ system.url }}/news/tag/{{ tag }}.html">{{ tag }}</a>{% if not loop.last %},{% endif %}
                {% endfor %}
            </span>
    </div>
    <meta itemprop="keywords" content="{% for tag in local.tags %}{{ tag }}{% if not loop.last %},{% endif %}{% endfor %}">
    {% endif %}
</article>
{# include comment area #}
{% include 'modules/comments/comment_area.tpl' %}
