<script type="text/javascript" src="{$url}/{$tpl_dir}/js/admin.js"></script>
<form action="" method="post">
<div class="row">
<div class="span5">
<h5>{$lang::admin_component_news_edit_page_title_name}</h5>
<input onkeyup="oJS.strNormalize(this)" type="text" name="title" class="input-block-level" value="{$news_title}" />
<span class="help-block">{$lang::admin_component_news_edit_page_title_desc}</span>
<h5>{$lang::admin_component_news_edit_page_category_name}</h5>
<div>
	<select name="category" size="10" style="width:99%;height:100px">
		{$category_option_list}
	</select>
</div>
<span class="help-block">{$lang::admin_component_news_edit_page_category_desc}</span>
</div>
<div class="span4">
<h5>{$lang::admin_component_news_edit_page_pathway_title}</h5>
<div class="input-prepend input-append">
  <input class="level" type="text" id="out" name="pathway" value="{$news_path}">
  <span class="add-on">.html</span>
</div>
<span class="help-block">{$lang::admin_component_news_edit_page_pathway_desc}</span>
<h5>{$lang::admin_component_news_edit_page_params_name}</h5>
<div>
<label class="checkbox"><input type="checkbox" name="display_content" {$news_display_check}> {$lang::admin_component_news_edit_page_display_ckechbox}</label>
<label class="checkbox"><input type="checkbox" name="important_content" {$news_important_check}> {$lang::admin_component_news_edit_page_important_ckechbox}</label>
{$notify_message}
</div>
</div>
</div>
<div class="row">
<div class="span9">
<h5>{$lang::admin_component_news_edit_page_textarea_title}</h5>
<textarea name="text" id="textobject" class="wysi">{$news_content}</textarea>
</div>
</div>
<div class="row">
<div class="span5">
<h5>{$lang::admin_component_news_edit_page_description}</h5>
<input type="text" name="description" class="input-block-level" value="{$news_description}" />
<span class="help-block">{$lang::admin_component_news_edit_page_description_desc}</span>
</div>
<div class="span4">
<h5>{$lang::admin_component_news_edit_page_keywords}</h5>
<input type="text" id="keywords" name="keywords" class="input-block-level" value="{$news_keywords}" /><input class="btn btn-info pull-right" type="button" value="{$lang::admin_component_news_edit_page_keybutton_gen}" onClick="countKeywords()">
<span class="help-block">{$lang::admin_component_news_edit_page_keywords_description}</span>
</div>
</div>
<div class="row">
<div class="span5">
	<label>{$lang::admin_component_news_edit_page_date_text}: <input type="text" name="date" id="datefield" value="{$news_date}"/> 
	<input type="checkbox" id="setcurrentdate" name="current_date" /> {$lang::admin_component_news_edit_page_current_date}
	<span class="help-block">{$lang::admin_component_news_edit_page_date_desc}</span></label>
</div>
<div class="span4">
<input type="submit" name="save" value="{$lang::admin_component_news_edit_page_button_save}" class="btn btn-success btn-large" />
</div>
</div>
</form>