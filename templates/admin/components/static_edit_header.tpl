<script type="text/javascript" src="{$url}/{$tpl_dir}/js/admin.js"></script>
{$notify}
<form action="" method="post">
    <div class="row">
        <div class="span5">
            <h5>{$lang::admin_component_static_edit_page_pathway}</h5>
            <div class="input-prepend input-append">
                <span class="add-on"><a href="{$url}/static/{$static_path}.html" target="_blank"><i class="icon-share-alt"></i></a></span>
                <span class="add-on">{$url}/static/</span>
                <input class="level" type="text" id="out" name="pathway" value="{$static_path}" onkeyup="return pathCallback();" />
                <span class="add-on">.html</span>
            </div>
            <span class="help-block">{$lang::admin_component_static_edit_page_pathway_desc}</span>
        </div>
        <div class="span4">
            <h5>{$lang::admin_component_static_edit_page_date_text}</h5>
            <input type="text" name="date" class="input-block-level" value="{$static_date}"/>
            <span class="help-block">{$lang::admin_component_static_edit_page_date_desc}</span>
        </div>
    </div>
    <p class="alert alert-info">{$lang::admin_component_static_language_alert}</p>
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
            <input type="submit" name="save" value="{$lang::admin_component_static_edit_page_button_save}" class="btn btn-success btn-large"/>
        </div>
    </div>
</form>