{$news_delete_info}
<p>{$lang::admin_component_news_delete_warning}</p>
<form method="post" action="">
    <div class="centered">
        <input type="submit" name="submit" value="{$lang::admin_component_news_delete_button_success}" class="btn btn-danger"/>
        <a href="{$cancel_link}" class="btn btn-success">{$lang::admin_component_news_delete_button_cancel}</a>
    </div>
</form>