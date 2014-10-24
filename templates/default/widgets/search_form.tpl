<div class="row">
    <div class="col-md-12">
        <form id="search">
            <div class="input-group">
                <input type="text" class="form-control" id="search-term" placeholder="query...">
                  <span class="input-group-btn">
                    <button class="btn btn-default" id="search-submit" type="submit">{{ language.global_search_button }}</button>
                  </span>
            </div>
        </form>
        <script>
            $(function(){
                $('#search-submit').click(function(e){
                    var input_text = $('#search-term').val();
                    if(input_text.length > 0) {
                        window.location.replace("{{ system.url }}/search/" + input_text);
                    }
                    return false;
                });
            });
        </script>
    </div>
</div>
