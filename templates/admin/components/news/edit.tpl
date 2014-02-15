{% import 'macro/notify.tpl' as notifytpl %}
<script type="text/javascript" src="{{ system.script_url }}/resource/ckeditor/ckeditor.js"></script>
<script src="{{ system.script_url }}/resource/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
    $(document).ready(
            function()
            {
                CKEDITOR.disableAutoInline = true;
                $('.wysi').ckeditor();
            }
    );
</script>
<h1>{{ extension.title }}<small>{{ language.admin_component_news_modedit_title }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
{% if notify.notitle %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_title_length) }}
{% endif %}
{% if notify.nocat %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_category_wrong) }}
{% endif %}
{% if notify.wrongway %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_pathway_null) }}
{% endif %}
{% if notify.notext %}
    {{ notifytpl.error(language.admin_component_news_edit_notify_text_null) }}
{% endif %}
{% if notify.success %}
    {{ notifytpl.success(language.admin_component_news_edit_notify_success_save) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_pathway_title }}</h2>

            <div class="input-group">
                <input class="form-control" type="text" id="out" name="pathway" value="{{ news.pathway }}" onkeyup="return pathCallback();">
                <span class="input-group-addon">.html</span>
            </div>
            <span class="help-block">{{ language.admin_component_news_edit_page_pathway_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_date_text }}<small><input type="checkbox" id="setcurrentdate" name="current_date"/> {{ language.admin_component_news_edit_page_current_date }}</small></h2>
            <input type="text" name="date" id="datefield" value="{{ news.date }}" class="form-control" />
            <span class="help-block">{{ language.admin_component_news_edit_page_date_desc }}</span>
        </div>
    </div>
    <p class="alert alert-info">{{ language.admin_component_news_alert_info }}</p>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in langs.all %}
                <li{% if itemlang == langs.current %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
            {% endfor %}
        </ul>
        <div class="tab-content">
            {% for itemlang in langs.all %}
            <div class="tab-pane fade{% if itemlang == langs.current %} in active{% endif %}" id="{{ itemlang }}">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ language.admin_component_news_edit_page_title_name }}[{{ itemlang }}]</h2>
                        <input onkeyup="oJS.strNormalize(this)" type="text" name="title[{{ itemlang }}]" class="form-control" value="{{ news.title[itemlang] }}"/>
                        <span class="help-block">{{ language.admin_component_news_edit_page_title_desc }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ language.admin_component_news_edit_page_textarea_title }}[{{ itemlang }}]</h2>
                        <textarea name="text[{{ itemlang }}]" id="textobject{{ itemlang }}" class="wysi form-control">{{ news.text[itemlang] }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <h2>{{ language.admin_component_news_edit_page_description }}[{{ itemlang }}]</h2>
                        <input type="text" name="description[{{ itemlang }}]" class="form-control" value="{{ news.description[itemlang] }}"/>
                        <span class="help-block">{{ language.admin_component_news_edit_page_description_desc }}</span>
                    </div>
                    <div class="col-lg-6">
                        <h2>{{ language.admin_component_news_edit_page_keywords }}[{{ itemlang }}]</h2>
                        <input type="text" id="keywords[{{ itemlang }}]" name="keywords[{{ itemlang }}]" class="form-control" value="{{ news.keywords[itemlang] }}"/>
                        <input class="btn btn-info pull-right" type="button" value="{{ language.admin_component_news_edit_page_keybutton_gen }}" onClick="countKeywords('{{ itemlang }}')">
                        <span class="help-block">{{ language.admin_component_news_edit_page_keywords_description }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_category_name }}</h2>

            <div>
                <select name="category" size="5" class="form-control">
                    {% for cat in news.categorys %}
                        <option value="{{ cat.id }}"{% if cat.id == news.cat_id %} selected{% endif %}>{{ cat.name }}</option>
                    {% endfor %}
                </select>
            </div>
            <span class="help-block">{{ language.admin_component_news_edit_page_category_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>{{ language.admin_component_news_edit_page_params_name }}</h2>
            <label class="checkbox">
                <input type="checkbox" name="display_content"{% if news.display == 1 %} checked{% endif %} /> {{ language.admin_component_news_edit_page_display_ckechbox }}
            </label>
            <label class="checkbox">
                <input type="checkbox" name="important_content"{% if news.important == 1 %} checked{% endif %} /> {{ language.admin_component_news_edit_page_important_ckechbox }}
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <input type="submit" name="save" value="{{ language.admin_component_news_edit_page_button_save }}" class="btn btn-success btn-large"/>
        </div>
    </div>
</form>