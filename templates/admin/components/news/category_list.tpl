<h1>{{ extension.title }}<small>{{ language.admin_component_news_category }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-9">
        <div class="row">
            <div class="col-lg-9">
                <div class="alert alert-success">{{ language.admin_component_news_category_block_cat }}</div>
            </div>
            <div class="col-lg-3">
                <div class="alert alert-danger">{{ language.admin_component_news_category_block_act }}</div>
            </div>
        </div>
        {% for cat_data in news.categorys %}
            <div class="row">
                <div class="col-lg-9">
                    <div class="alert alert-info">
                        <div class="label label-danger">{{ cat_data.name }}</div> <div class="label label-success">[/{{ cat_data.path }}]</div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="text-center">
                        <a href="?object=components&action=news&make=addcategory&id={{ cat_data.id }}" class="btn btn-success"><i class="fa fa-plus"></i></a>
                        <a href="?object=components&action=news&make=delcategory&id={{ cat_data.id }}" class="btn btn-danger"><i class="fa fa-minus"></i></a>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>
    <div class="col-lg-3">
        <h2>{{ language.admin_component_news_category_title_name }}</h2>
        <p>{{ language.admin_component_news_category_manage_desc }}</p>
        <a href="?object=components&action=news&make=addcategory" class="btn btn-primary btn-block">{{ language.admin_component_news_category_alternative_button }}</a>
    </div>
</div>