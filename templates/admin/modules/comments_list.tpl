{$notify}
<form action="" method="post">
    {$comment_table}
    <a id="checkAll">{$lang::admin_modules_comment_select_all}</a><br /><br />
    <input type="submit" name="delete_comments" class="btn btn-danger" value="{$lang::admin_modules_comment_masss_delete}">
</form>
<div class="pagination pagination-centered">
    <ul>
        <li class="disabled"><a href="#">{$lang::admin_extension_pagination_word}: </a></li>
        {$ext_pagination_list}
    </ul>
</div>
<script>
    $('#checkAll').click(function()
    {
        if($('.check_array').is(':checked'))
            $('.check_array').attr('checked', false);
        else
            $('.check_array').attr('checked', true);
    });
</script>