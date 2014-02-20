{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_news_settings }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
<p>{{ language.admin_component_news_description }}</p>
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <fieldset>
    <h2>{{ language.admin_component_news_settings_mainblock }}</h2>
    <hr />
        {{ settingstpl.textgroup('count_news_page', config.count_news_page, language.admin_component_news_config_newscount_page_title, language.admin_component_news_config_newscount_page_desc) }}
        {{ settingstpl.textgroup('short_news_length', config.short_news_length, language.admin_component_news_config_newsshort_length_title, language.admin_component_news_config_newsshort_length_desc) }}
        {{ settingstpl.selectYNgroup('enable_views_count', config.enable_views_count, language.admin_component_news_config_viewcount_title, language.admin_component_news_config_viewcount_desc, _context) }}
    <h2>{{ language.admin_component_news_settings_catblock }}</h2>
    <hr />
        {{ settingstpl.selectYNgroup('multi_category', config.multi_category, language.admin_component_news_config_newscat_multi_title, language.admin_component_news_config_newscat_multi_desc, _context) }}
    <h2>{{ language.admin_component_news_settings_tags }}</h2>
    <hr />
        {{ settingstpl.selectYNgroup('enable_tags', config.enable_tags, language.admin_component_news_config_tag_title, language.admin_component_news_config_tag_desc, _context) }}
    <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>