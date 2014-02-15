<form id="search" class="form-search">
    <input class="search-query" id="search-term" placeholder="query..." type="text"/>

    <button class="btn" id="search-submit" type="submit"> {{ language.global_search_button }}</button>
</form>
<script>
    $('#search').submit(function (e) {
        var query = $('#search-term').val();
        window.location.replace("{{ system.url }}/search/" + query);
        return false;
    });
</script>