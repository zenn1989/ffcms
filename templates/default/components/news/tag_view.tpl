<h3>{{ language.news_tag_search_title }} : {{ local.tagname }}</h3>
<hr />
<ul>
    {% for item in local.newsinfo %}
    <li><a href="{{ system.url }}/news/{{ item.link }}">{{ item.title }}</a></li>
    {% endfor %}
</ul>