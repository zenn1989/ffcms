<p class="alert alert-danger">{$lang::admin_component_news_category_delete_pdesc}</p>
<form class="form-horizontal" method="post">
    <div class="control-group">
        <label class="control-label">{$lang::admin_component_news_category_delete_label_target}</label>

        <div class="controls">
            <input type="text" value="{$category_name}" disabled>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::admin_component_news_category_delete_label_moveto}</label>

        <div class="controls">
            <select name="move_to_category">{$category_list}</select>
        </div>
    </div>
    <input type="submit" name="deletecategory" value="{$lang::admin_component_news_category_delete_button_submit}"
           class="btn btn-danger"/>
</form>
