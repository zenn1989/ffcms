<h1>{{ extension.title }}<small>{{ language.admin_component_news_manage }}</small></h1>
<hr />
{% include 'components/news/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-12">
        <div class="pull-right">
            <form action="" method="post" class="form-inline">
                <div class="form-group">
                    <input type="text" name="search" placeHolder="Data..." value="{{ search.value|escape|striptags }}" class="form-control"/>
                </div>
                <div class="form-group">
                    <input value="{{ language.admin_component_static_search_button }}" type="submit" name="dosearch" class="btn btn-primary"/>
                </div>
            </form>
        </div>
    </div>
</div>
{% if news %}
    <table class="table table-bordered table-responsive">
        <thead>
        <tr>
            <th>{{ language.admin_component_news_th_id }}</th>
            <th>{{ language.admin_component_news_th_title }}</th>
            <th>{{ language.admin_component_news_th_link }}</th>
            <th>{{ language.admin_component_news_th_manage }}</th>
        </tr>
        </thead>
        <tbody>
        {% for row in news %}
            <tr>
                <td>{{ row.id }}</td>
                <td><a href="?object=components&action=news&make=edit&id={{ row.id }}">{{ row.title }}</a></td>
                <td><a href="{{ system.script_url }}/news/{{ row.link }}" target="_blank">/news/{{ row.link }}</a></td>
                <td class="text-center">
                    <a href="?object=components&action=news&make=edit&id={{ row.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
                    <a href="?object=components&action=news&make=delete&id={{ row.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
    {{ pagination }}
{% endif %}