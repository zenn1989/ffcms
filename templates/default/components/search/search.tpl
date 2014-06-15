{% import 'macro/notify.tpl' as notify %}
<h3>{{ language.search_form_title }}</h3>
<hr />
<form class="form-horizontal" id="makesearch">
    <div class="form-group">
        <div class="col-md-10">
            <input type="text" value="{{ local.query|striptags|escape }}" id="searchquery" class="form-control"/>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-default">{{ language.global_search_button }}</button>
        </div>
    </div>
</form>
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
            <h4><a href="{{ system.url }}/{{ news.link }}">{{ news.title }}</a> <span class="pull-right label label-success">{{ news.date }}</span></h4>
            <small>{{ news.snippet }}</small>
        </blockquote>
        {% else %}
            {{ notify.warning(language.search_nothing_found) }}
        {% endfor %}
    </div>
    <div class="tab-pane" id="pages">
        {% for page in local.search.static %}
        <blockquote><h4><a href="{{ system.url }}/{{ page.link }}">{{ page.title }}</a> <span class="pull-right label label-success">{{ page.date }}</span></h4>
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