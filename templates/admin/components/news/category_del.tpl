{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_news_category_add_title }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
{{ notifytpl.warning(language.admin_component_news_category_delete_pdesc) }}
{% if notify.nomoveto %}
    {{ notifytpl.error(language.admin_component_news_category_delete_nocat) }}
{% endif %}
{% if notify.unpos_delete %}
    {{ notifytpl.error(language.admin_component_news_category_delete_unposible) }}
{% endif %}
<form class="form-horizontal" method="post">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_component_news_category_delete_label_target }}</label>

        <div class="col-lg-9">
            <input type="text" value="{{ cat.name }}[/{{ cat.path }}]" disabled class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_component_news_category_delete_label_moveto }}</label>

        <div class="col-lg-9">
            <select name="move_to_category" class="form-control">
                {% for category in news.categorys %}
                {% if category.id != news.selected_category %}
                    <option value="{{ category.id }}">{{ category.name }}</option>
                {% endif %}
                {% endfor %}
            </select>
        </div>
    </div>
    <input type="submit" name="deletecategory" value="{{ language.admin_component_news_category_delete_button_submit }}" class="btn btn-danger"/>
</form>