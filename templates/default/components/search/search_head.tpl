<h3>{$lang::search_form_title}</h3>
<hr />
<div class="pull-right"><form class="form-search" id="makesearch"><input type="text" value="{$search_query}" id="searchquery" /><button type="submit" class="btn">{$lang::global_search_button}</button></form></div><br /><br />
<script>
    $('#makesearch').submit(function() {
        var query = $('#searchquery').val();
        window.location.replace("{$url}/search/" + query);
        return false;
    });
    $(document).ready(function() {
       $('#search-term').val($('#searchquery').val());
    });
</script>
{$if com.search.query_make}
<ul class="nav nav-tabs">
    <li class="active"><a href="#news" data-toggle="tab">{$lang::search_form_news}</a></li>
    <li><a href="#pages" data-toggle="tab">{$lang::search_form_pages}</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="news">{$search_news}</div>
    <div class="tab-pane" id="pages">{$search_pages}</div>
</div>
{$/if}
{$if !com.search.query_make}
<p class="alert alert-info">{$lang::search_form_no_query}</p>
{$/if}