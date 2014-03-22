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

<article class="article-item">
    <h1>{{ local.title }}</h1>
    <div class="meta">
        <span><i class="fa fa-list"></i><a href="{{ system.url }}/news/{{ local.category_url }}">{{ local.category_name }}</a></span>
        <span><i class="fa fa-calendar"></i>{{ local.date }}</span>
        <span><i class="fa fa-user"></i><a href="{{ system.url }}/user/id{{ local.author_id }}">{{ local.author_nick }}</a></span>
        {% if cfg.view_count %}
            <span><i class="fa fa-eye"></i> {{ local.view_count }}</span>
        {% endif %}
    </div>
    <div class="row">
        <div class="col-md-12">
            {% if local.poster %}
                <img src="{{ local.poster }}" class="image_poster" />
            {% endif %}
            {{ local.text }}
        </div>
    </div>
    {% if local.gallery %}
    <div class="row">
        <div class="col-md-12">
            {% for image in local.gallery %}
                <div class="col-md-2">
                    <a class="fancybox thumbnail" rel="gallery" href="{{ image.full }}" title="{{ local.title }}">
                        <img src="{{ image.thumb }}" alt="" class="img-responsive" />
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
                    <a href="{{ system.url }}/news/tag/{{ tag|url_encode }}.html">{{ tag }}</a>{% if not loop.last %},{% endif %}
                {% endfor %}
            </span>
    </div>
    {% endif %}
</article>
{# include comment area #}
{% include 'modules/comments/comment_area.tpl' %}
