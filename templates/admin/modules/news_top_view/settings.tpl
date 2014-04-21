{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_news_top_view_settings }}</small></h1>
<hr />
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form method="post" action="" class="form-horizontal">
    <fieldset>
        {{ settingstpl.textgroup('viewtop_count', config.viewtop_count, language.admin_modules_news_top_view_label_discount_title, language.admin_modules_news_top_view_label_discount_desc ) }}
        {{ settingstpl.textgroup('viewtop_days', config.viewtop_days, language.admin_modules_news_top_view_label_daycount_title, language.admin_modules_news_top_view_label_daycount_desc ) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>