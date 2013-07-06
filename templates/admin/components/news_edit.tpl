<div class="tab-pane {$is_active_element}" id="{$current_language}">
    <div class="row">
        <div class="span8">
            <h5>{$lang::admin_component_news_edit_page_title_name}</h5>
            <input onkeyup="oJS.strNormalize(this)" type="text" name="title[{$current_language}]" class="input-block-level"
                   value="{$news_title_{$current_language}}"/>
            <span class="help-block">{$lang::admin_component_news_edit_page_title_desc}</span>
        </div>
    </div>
    <div class="row">
        <div class="span8">
            <h5>{$lang::admin_component_news_edit_page_textarea_title}</h5>
            <textarea name="text[{$current_language}]" id="textobject{$current_language}" class="wysi">{$news_content_{$current_language}}</textarea>
        </div>
    </div>
    <div class="row">
        <div class="span4">
            <h5>{$lang::admin_component_news_edit_page_description}</h5>
            <input type="text" name="description[{$current_language}]" class="input-block-level" value="{$news_description_{$current_language}}"/>
            <span class="help-block">{$lang::admin_component_news_edit_page_description_desc}</span>
        </div>
        <div class="span4">
            <h5>{$lang::admin_component_news_edit_page_keywords}</h5>
            <input type="text" id="keywords[{$current_language}]" name="keywords[{$current_language}]" class="input-block-level" value="{$news_keywords_{$current_language}}"/>
            <input class="btn btn-info pull-right" type="button" value="{$lang::admin_component_news_edit_page_keybutton_gen}" onClick="countKeywords('{$current_language}')">
            <span class="help-block">{$lang::admin_component_news_edit_page_keywords_description}</span>
        </div>
    </div>
</div>