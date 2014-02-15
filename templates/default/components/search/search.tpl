{% import 'macro/notify.tpl' as notify %}
<h3>{{ language.search_form_title }}</h3>
<hr />
<div class="pull-right">
    <form class="form-search" id="makesearch">
        <input type="text" value="{{ local.query|striptags|escape }}" id="searchquery" />
        <button type="submit" class="btn">{{ language.global_search_button }}</button>
    </form>
</div><br /><br />
<script>
    $('#makesearch').submit(function() {
        var query = $('#searchquery').val();
        window.location.replace("{{ system.url }}/search/" + query);
        return false;
    });
    $(document).ready(function() {
        $('#search-term').val($('#searchquery').val());
    });
</script>
{% if local.search %}
<ul class="nav nav-tabs">
    <li class="active"><a href="#news" data-toggle="tab">{{ language.search_form_news }}</a></li>
    <li><a href="#pages" data-toggle="tab">{{ language.search_form_pages }}</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="news">
        {% for news in local.search.news %}
        <blockquote>
            <h4><a href="{{ system.url }}/{{ news.link }}">{{ news.title }}</a></h4>
            <small>{{ news.snippet }}</small>
        </blockquote>
        {% else %}
            {{ notify.warning(language.search_nothing_found) }}
        {% endfor %}
    </div>
    <div class="tab-pane" id="pages">
        {% for page in local.search.static %}
        <blockquote><h4><a href="{{ system.url }}/{{ page.link }}">{{ page.title }}</a></h4>
            <small>{{ page.snippet }}</small>
        </blockquote>
        {% else %}
            {{ notify.warning(language.search_nothing_found) }}
        {% endfor %}
    </div>
</div>
{% else %}
<p class="alert alert-info">{{ language.search_form_no_query }}</p>
{% endif %}