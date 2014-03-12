<form class="form-inline" role="form" id="search">
    <div class="form-group">
        <input class="form-control" id="search-term" placeholder="query..." type="text"/>
    </div>
    <button class="btn btn-default" id="search-submit" type="submit"> {{ language.global_search_button }}</button>
</form>
<script>
    $('#search').submit(function (e) {
        var query = $('#search-term').val();
        window.location.replace("{{ system.url }}/search/" + query);
        return false;
    });
</script>