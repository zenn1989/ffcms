<script type="text/javascript" src="{$url}/{$tpl_dir}/js/admin.js"></script>
{$notify}
<form action="" method="post">
    <div class="row">
        <div class="span5">
            <h5>{$lang::admin_component_news_edit_page_pathway_title}</h5>

            <div class="input-prepend input-append">
                <input class="span4" type="text" id="out" name="pathway" value="{$news_path}">
                <span class="add-on">.html</span>
            </div>
            <span class="help-block">{$lang::admin_component_news_edit_page_pathway_desc}</span>
        </div>
        <div class="span4">
                <h5>{$lang::admin_component_news_edit_page_date_text}</h5>
                <input type="text" name="date" id="datefield" value="{$news_date}"/>
                <input type="checkbox" id="setcurrentdate" name="current_date"/> {$lang::admin_component_news_edit_page_current_date}
                <span class="help-block">{$lang::admin_component_news_edit_page_date_desc}</span>
        </div>
    </div>
    <p class="alert alert-info">{$lang::admin_component_news_alert_info}</p>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {$selecter_li_languages}
        </ul>
        <div class="tab-content">
            {$selecter_body_languages}
        </div>
    </div>
    <div class="row">
        <div class="span4">
            <h5>{$lang::admin_component_news_edit_page_category_name}</h5>

            <div>
                <select name="category" size="10" style="width:99%;height:80px">
                    {$category_option_list}
                </select>
            </div>
            <span class="help-block">{$lang::admin_component_news_edit_page_category_desc}</span>
        </div>
        <div class="span5">
            <h5>{$lang::admin_component_news_edit_page_params_name}</h5>

            <div>
                <label class="checkbox">
                    <input type="checkbox" name="display_content" {$news_display_check}> {$lang::admin_component_news_edit_page_display_ckechbox}
                </label>
                <label class="checkbox">
                    <input type="checkbox" name="important_content" {$news_important_check}> {$lang::admin_component_news_edit_page_important_ckechbox}
                </label>
                {$notify_message}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span4">
            <br /><br /><input type="submit" name="save" value="{$lang::admin_component_news_edit_page_button_save}" class="btn btn-success btn-large"/>
        </div>
    </div>
</form>