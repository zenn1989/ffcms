{$jsapi api.php?action=js&dir=js&name=functions}
<div class="well">
    <h5 class="centered">{$lang::position_left_header_bookmark}</h5>
    <script>
        document.write('<a href="#" onclick="ffcmsAddBookmark(\'{$self_url}\', \''+document.title+'\')"><i class="icon-star-empty icon-large"></i></a> ');
        document.write('<a href="http://vk.com/share.php?url='+encodeURIComponent('{$self_url}')+'&title='+encodeURIComponent(document.title).replace(/%20/g,'+')+'" target="_blank" rel="nofollow"><i class="icon-vk icon-large"></i></a> ');
        document.write('<a href="https://twitter.com/intent/tweet?source=webclient&text='+encodeURIComponent(document.title).replace(/%20/g,'+')+'+-+{$self_url}" target="_blank" rel="nofollow"><i class="icon-twitter icon-large"></i></a> ');
        document.write('<a href="https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('{$self_url}')+'" target="_blank" rel="nofollow"><i class="icon-facebook icon-large"></i></a>');
    </script>
</div>