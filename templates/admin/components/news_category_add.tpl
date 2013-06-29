<h5>{$lang::admin_component_news_category_add_title}</h5>
{$notify_message}
<form class="form-horizontal" method="post">
    <div class="control-group">
        <label class="control-label">{$lang::admin_component_news_category_add_mother_label}</label>

        <div class="controls">
            <select name="category_owner">{$news_category_select}</select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::admin_component_news_category_add_name_label}</label>

        <div class="controls">
            <input type="text" class="input-large" name="category_name">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::admin_component_news_category_add_url_label}</label>

        <div class="controls">
            <input type="text" class="input-large" name="category_path">
        </div>
    </div>
    <input type="submit" name="submit" value="{$lang::admin_component_news_category_add_form_button}"
           class="btn btn-success"/>
</form>