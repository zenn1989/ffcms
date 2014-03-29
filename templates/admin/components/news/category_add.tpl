{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_news_category_edit_title }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
{% if notify.owner_notselect %}
    {{ notifytpl.error(language.admin_component_news_category_notify_noselectcat) }}
{% endif %}
{% if notify.noname %}
    {{ notifytpl.error(language.admin_component_news_category_notify_noname) }}
{% endif %}
{% if notify.wrongpath %}
    {{ notifytpl.error(language.admin_component_news_category_notify_pathwrong) }}
{% endif %}
<form class="form-horizontal" method="post" role="form">
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_component_news_category_add_mother_label }}</label>

        <div class="col-lg-9">
            <select name="category_owner" class="form-control">
                {% for category in news.categorys %}
                <option value="{{ category.id }}"{% if category.id == news.selected_category %} selected{% endif %}>{{ category.name }}</option>
                {% endfor %}
            </select>
        </div>
    </div>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in system.languages %}
                <li{% if itemlang == system.lang %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
            {% endfor %}
        </ul>
        <div class="tab-content">
            {% for itemlang in system.languages %}
            <div class="tab-pane fade{% if itemlang == system.lang %} in active{% endif %}" id="{{ itemlang }}">
                <br />
                <div class="form-group">
                    <label class="control-label col-lg-3">{{ language.admin_component_news_category_add_name_label }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="category_name[{{ itemlang }}]" value="{{ cat.name[itemlang] }}" maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">{{ language.admin_component_news_category_add_desc_label }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <textarea name="category_desc[{{ itemlang }}]" class="form-control" maxlength="250">{{ cat.desc[itemlang] }}</textarea>
                        <span class="help-block">{{ language.admin_component_news_category_add_desc_helper }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <hr />
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_component_news_category_add_url_label }}</label>

        <div class="col-lg-9">
            <input type="text" class="form-control" name="category_path" value="{{ cat.path }}" maxlength="128">
        </div>
    </div>
    <input type="submit" name="submit" value="{{ language.admin_component_news_category_add_form_button }}" class="btn btn-success"/>
</form>