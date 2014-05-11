{# local.total - count of all items #}
{# local.count - static count items on 1 page, as default - 10 #}
{# local.index - current cursor position on page index #}
{# local.lastpage - last page of all items [local.total/local.count] #}
{% import 'macro/list.tpl' as listmacro %}
{% if local.total > local.count %} {# if pagination must be #}
<div class="text-center">
    <ul class="pagination pagination-centered">
        {% if local.total/local.count > 10 %} {# more then 10 items available #}
            {% if local.index < 4 %} {# list start #}
                {% for pageidx in 0..4 %}
                    {{ listmacro.li_pagination(system.url, local.link, pageidx, pageidx == local.index) }}
                {% endfor %}
                <li class="disabled"><a href="#">...</a></li> {# splitter '...' #}
                {% for pageidx in local.lastpage-3..local.lastpage %}
                    {{ listmacro.li_pagination(system.url, local.link, pageidx, pageidx == local.index) }}
                {% endfor %}
            {% elseif (local.lastpage - local.index) < 4  %} {# end of list #}
                {% for pageidx in 0..3 %}
                    {{ listmacro.li_pagination(system.url, local.link, pageidx, pageidx == local.index) }}
                {% endfor %}
                <li class="disabled"><a href="#">...</a></li> {# splitter '...' #}
                {% for pageidx in local.lastpage-4..local.lastpage %}
                    {{ listmacro.li_pagination(system.url, local.link, pageidx, pageidx == local.index) }}
                {% endfor %}
            {% else %} {# list middle +-4 items from this +1st + last#}
                {{ listmacro.li_pagination(system.url, local.link, 0, false) }} {# 1st page #}
                <li class="disabled"><a href="#">...</a></li> {# splitter '...' #}
                {% for pageidx in local.index-3..local.index+3 %}
                    {{ listmacro.li_pagination(system.url, local.link, pageidx, pageidx == local.index) }}
                {% endfor %}
                <li class="disabled"><a href="#">...</a></li> {# splitter '...' #}
                {{ listmacro.li_pagination(system.url, local.link, local.lastpage, false) }} {# last page #}
            {% endif %}
        {% else %} {# lowest then 10 item available ;D #}
            {% for pageidx in 0..local.lastpage %}
                {{ listmacro.li_pagination(system.url, local.link, pageidx, pageidx == local.index) }}
            {% endfor %}
        {% endif %}
    </ul>
</div>
{% endif %}