<h1>{{ extension.title }}<small>{{ language.admin_component_news_category }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
<div class="table-responsive">
    <table class="table table-line table-striped table-hover">
        <thead>
        <tr>
            <th class="col-lg-10">{{ language.admin_component_news_category_block_cat }}</th>
            <th class="col-lg-2 text-center">{{ language.admin_component_news_category_block_act }}</th>
        </tr>
        </thead>
        <tbody>
        {% for cat_data in news.categorys %}
        <tr>
            <td>
                <div class="row">
                    {% if cat_data.level == 0 %}
                    <div class="col-lg-12">
                        <strong>{{ cat_data.name }}</strong> {# {{ cat_data.level }} #}
                    </div>
                    {% else %}
                        {% set html_level = cat_data.level + 2 %}
                        {% if html_level > 9 %}
                            {% set html_level = 9 %}
                        {% endif %}
                        <div class="col-lg-{{ html_level }}">
                            <div class="bg-info">
                                <div class="text-center">
                                    <strong>{{ cat_data.path }}<abbr title="{{ language.admin_component_news_category_block_attr_level }}">[{{ cat_data.level }}]</abbr></strong>
                                    <span class="pull-right">&rarr;</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-{{ 12 - html_level }}">
                            {{ cat_data.name }}
                        </div>
                    {% endif %}
                </div>
            </td>
            <td class="text-center">
                <a href="?object=components&action=news&make=addcategory&id={{ cat_data.id }}" class="btn btn-default"><i class="fa fa-plus"></i></a>
                <a href="?object=components&action=news&make=editcategory&id={{ cat_data.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                {% if cat_data.level != 0 %}<a href="?object=components&action=news&make=delcategory&id={{ cat_data.id }}" class="btn btn-default"><i class="fa fa-minus"></i></a>{% endif %}
            </td>
        </tr>
        {% endfor %}
        </tbody>
    </table>
</div>

<h2>{{ language.admin_component_news_category_title_name }}</h2>
<p>{{ language.admin_component_news_category_manage_desc }}</p>
<a href="?object=components&action=news&make=addcategory" class="btn btn-default">{{ language.admin_component_news_category_alternative_button }}</a>