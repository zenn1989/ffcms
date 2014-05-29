<h1>{{ language.sitemap_title }}</h1>
<hr />
<ul>
{% for map in local %}
    <li><a href="{{ map.uri }}">{{ map.title|default(map.uri) }}</a></li>
{% endfor %}
</ul>