<div class="well-item">
    <h1>{{ local.title }}</a></h1>

    <div class="pull-left">
        <a href="{{ system.url }}/news/{{ local.category_url }}" class="btn btn-small"><i class="icon-list"></i> {{ local.category_name }}</a>
        <span class="btn"><i class="icon-calendar"></i> {{ local.date }}</span>
    </div>
    <div class="pull-right">
        <a href="{{ system.url }}/user/id{{ local.author_id }}" class="btn btn-small"><i class="icon-pencil"></i> {{ language.news_view_author }}: {{ local.author_nick }}</a>
        {% if local.cfg.view_count %}
            <span class="btn btn-small"><i class="icon-eye-open"></i> {{ local.view_count }}</span>
        {% endif %}
    </div>
    <br/><hr/>
    <div>
        {{ local.text }}
    </div>
    <div class="pull-right">
        {% if local.tags and local.cfg.view_tags %}
            <i class="icon-tags"></i>
            {% for tag in local.tags %}
                <a href="{{ system.url }}/news/tag/{{ tag|url_encode }}.html" class="label">{{ tag }}</a>
            {% endfor %}
        {% endif %}
    </div>
    <br/>
    {% if local.similar_items %}
    <div>
        <h3>{{ language.news_view_similar }}:</h3>
        <div class="accordion" id="accordion_similar">
            {% for similar in local.similar_items %}
                <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion_similar" href="#collapseSimilar{{ loop.index }}">
                            {{ similar.title }}
                        </a>
                    </div>
                    <div id="collapseSimilar{{ loop.index }}" class="accordion-body collapse">
                        <div class="accordion-inner">
                            <a href="{{ similar.link }}">{{ similar.preview }}</a>
                        </div>
                    </div>
                </div>
            {% endfor %}
         </div>
    </div>
    {% endif %}
</div>
{# include comment area #}
{% include 'modules/comments/comment_area.tpl' %}
