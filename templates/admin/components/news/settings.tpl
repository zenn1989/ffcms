{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_news_settings }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#main" role="tab" data-toggle="tab">{{ language.admin_component_news_settings_mainblock }}</a></li>
    <li><a href="#category" role="tab" data-toggle="tab">{{ language.admin_component_news_settings_catblock }}</a></li>
    <li><a href="#tags" role="tab" data-toggle="tab">{{ language.admin_component_news_settings_tags }}</a></li>
    <li><a href="#images" role="tab" data-toggle="tab">{{ language.admin_component_news_settings_images }}</a></li>
    <li><a href="#rss" role="tab" data-toggle="tab">{{ language.admin_component_news_settings_rss }}</a></li>
</ul>
<form action="" method="post" class="form-horizontal" role="form">
    <div class="tab-content">
        <div class="tab-pane active" id="main">
            <h2>{{ language.admin_component_news_settings_mainblock }}</h2>
            <hr />
            {{ settingstpl.textgroup('count_news_page', config.count_news_page, language.admin_component_news_config_newscount_page_title, language.admin_component_news_config_newscount_page_desc) }}
            {{ settingstpl.textgroup('short_news_length', config.short_news_length, language.admin_component_news_config_newsshort_length_title, language.admin_component_news_config_newsshort_length_desc) }}
            {{ settingstpl.selectYNgroup('enable_views_count', config.enable_views_count, language.admin_component_news_config_viewcount_title, language.admin_component_news_config_viewcount_desc, _context) }}
            {{ settingstpl.selectYNgroup('enable_useradd', config.enable_useradd, language.admin_component_news_config_useradd_title, language.admin_component_news_config_useradd_desc, _context) }}
        </div>
        <div class="tab-pane" id="category">
            <h2>{{ language.admin_component_news_settings_catblock }}</h2>
            <hr />
            {{ settingstpl.selectYNgroup('multi_category', config.multi_category, language.admin_component_news_config_newscat_multi_title, language.admin_component_news_config_newscat_multi_desc, _context) }}
        </div>
        <div class="tab-pane" id="tags">
            <h2>{{ language.admin_component_news_settings_tags }}</h2>
            <hr />
            {{ settingstpl.selectYNgroup('enable_tags', config.enable_tags, language.admin_component_news_config_tag_title, language.admin_component_news_config_tag_desc, _context) }}
        </div>
        <div class="tab-pane" id="images">
            <h2>{{ language.admin_component_news_settings_images }}</h2>
            {{ settingstpl.textgroup('poster_dx', config.poster_dx, language.admin_component_news_config_poster_dx_title, language.admin_component_news_config_poster_dx_desc) }}
            {{ settingstpl.textgroup('poster_dy', config.poster_dy, language.admin_component_news_config_poster_dy_title, language.admin_component_news_config_poster_dy_desc) }}
            {{ settingstpl.textgroup('gallery_dx', config.gallery_dx, language.admin_component_news_config_gallery_dx_title, language.admin_component_news_config_gallery_dx_desc) }}
            {{ settingstpl.textgroup('gallery_dy', config.gallery_dy, language.admin_component_news_config_gallery_dy_title, language.admin_component_news_config_gallery_dy_desc) }}
        </div>
        <div class="tab-pane" id="rss">
            <h2>{{ language.admin_component_news_settings_rss }}</h2>
            {{ settingstpl.selectYNgroup('enable_rss', config.enable_rss, language.admin_component_news_config_rss_enable_title, language.admin_component_news_config_rss_enable_desc, _context) }}
            {{ settingstpl.textgroup('rss_count', config.rss_count, language.admin_component_news_config_rss_count_title, language.admin_component_news_config_rss_count_desc) }}
            {{ settingstpl.selectYNgroup('enable_full_rss', config.enable_full_rss, language.admin_component_news_config_rss_fulltext_title, language.admin_component_news_config_rss_fulltext_desc, _context) }}
            {{ settingstpl.selectYNgroup('enable_soc_rss', config.enable_soc_rss, language.admin_component_news_config_rss_soc_title, language.admin_component_news_config_rss_soc_desc, _context) }}
            {{ settingstpl.languagegroup('rss_hash', config.rss_hash, language.admin_component_news_config_rss_sochash_title, language.admin_component_news_config_rss_sochash_desc, _context) }}
            {{ settingstpl.selectYNgroup('rss_soc_linkshort', config.rss_soc_linkshort, language.admin_component_news_config_rss_soclinkshort_title, language.admin_component_news_config_rss_soclinkshort_desc, _context) }}
        </div>
    </div>
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
</form>