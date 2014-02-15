<p>{{ language.position_left_header_bookmark }}:
<script>
document.write('<a href="#" onclick="ffcmsAddBookmark(\'{{ system.self_url|striptags|escape }}\', \''+document.title+'\')"><i class="fa fa-star-o fa-lg"></i></a> ');
document.write('<a href="http://vk.com/share.php?url='+encodeURIComponent('{{ system.self_url|striptags|escape }}')+'&title='+encodeURIComponent(document.title).replace(/%20/g,'+')+'" target="_blank" rel="nofollow"><i class="fa fa-vk fa-lg"></i></a> ');
document.write('<a href="https://twitter.com/intent/tweet?source=webclient&text='+encodeURIComponent(document.title).replace(/%20/g,'+')+'+-+{{ system.self_url|striptags|escape }}" target="_blank" rel="nofollow"><i class="fa fa-twitter fa-lg"></i></a> ');
document.write('<a href="https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('{{ system.self_url|striptags|escape }}')+'" target="_blank" rel="nofollow"><i class="fa fa-facebook fa-lg"></i></a>');
</script>
</p>