<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="{{ system.script_url }}/resource/cmscontent/sitemap.xsl"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

{% for map in local %}
<url>
    <loc>{{ map.uri }}</loc>
    <lastmod>{{ map.date }}</lastmod>
    <changefreq>{{ map.freq }}</changefreq>
    <priority>{{ map.priority }}</priority>
</url>
{% endfor %}

</urlset>