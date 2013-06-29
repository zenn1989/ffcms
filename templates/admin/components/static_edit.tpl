<script type="text/javascript" src="{$url}/{$tpl_dir}/js/admin.js"></script>
<form action="" method="post">
    <div class="row">
        <div class="span5">
            <h5>{$lang::admin_component_static_edit_page_title}</h5>
            <input onkeyup="oJS.strNormalize(this)" type="text" name="title" class="input-block-level"
                   value="{$static_title}"/>
            <span class="help-block">{$lang::admin_component_static_edit_page_title_desc}</span>
            {$notify_message}
        </div>
        <div class="span4">
            <h5>{$lang::admin_component_static_edit_page_pathway}</h5>

            <div class="input-prepend input-append">
                <span class="add-on"><a href="{$url}/static/{$static_path}.html" target="_blank"><i
                                class="icon-share-alt"></i></a></span>
                <span class="add-on">{$url}/static/</span>
                <input class="level" type="text" id="out" name="pathway" value="{$static_path}">
                <span class="add-on">.html</span>
            </div>
            <span class="help-block">{$lang::admin_component_static_edit_page_pathway_desc}</span>
        </div>
    </div>
    <div class="row">
        <div class="span9">
            <h5>{$lang::admin_component_static_edit_page_textarea_title}</h5>
            <textarea name="text" id="textobject" class="wysi">{$static_text}</textarea>
        </div>
    </div>
    <div class="row">
        <div class="span5">
            <h5>{$lang::admin_component_static_edit_page_description}</h5>
            <input type="text" name="description" class="input-block-level" value="{$static_description}"/>
            <span class="help-block">{$lang::admin_component_static_edit_page_description_desc}</span>
        </div>
        <div class="span4">
            <h5>{$lang::admin_component_static_edit_page_keywords}</h5>
            <input type="text" id="keywords" name="keywords" class="input-block-level"
                   value="{$static_keywords}"/><input class="btn btn-info pull-right" type="button"
                                                      value="{$lang::admin_component_static_edit_page_keybutton_gen}"
                                                      onClick="countKeywords()">
            <span class="help-block">{$lang::admin_component_static_edit_page_keywords_description}</span>
        </div>
    </div>
    <div class="row">
        <div class="span5">
            <label>{$lang::admin_component_static_edit_page_date_text}: <input type="text" name="date"
                                                                               value="{$static_date}"/><span
                        class="help-block">{$lang::admin_component_static_edit_page_date_desc}</span></label>
        </div>
        <div class="span4">
            <input type="submit" name="save" value="{$lang::admin_component_static_edit_page_button_save}"
                   class="btn btn-success btn-large"/>
        </div>
    </div>
</form>