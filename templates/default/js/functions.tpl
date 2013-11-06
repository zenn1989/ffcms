function ffcmsAddBookmark(b_url, b_title) {
    $.post('{$url}/api.php?action=addbookmark', { url : b_url, title : b_title});
    return false;
}
/** Hide broken images */
$('img').error(function(){
    $(this).hide();
});