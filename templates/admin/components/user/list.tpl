<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_manage }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<div class="row">
    <div class="col-lg-12">
        <div class="pull-right">
            <form action="" method="post" class="form-inline">
                <div class="form-group">
                    <input type="text" name="search" placeHolder="Data..." value="{{ search.value }}" class="form-control"/>
                </div>
                <div class="form-group">
                    <input value="{{ language.admin_component_static_search_button }}" type="submit" name="dosearch" class="btn btn-primary"/>
                </div>
            </form>
        </div>
    </div>
</div>

<table class="table table-bordered table-responsive">
    <thead>
    <tr>
        <th>{{ language.admin_component_usercontrol_th_id }}</th>
        <th>{{ language.admin_component_usercontrol_th_login }}</th>
        <th>{{ language.admin_component_usercontrol_th_email }}</th>
        <th>{{ language.admin_component_usercontrol_th_edit }}</th>
    </tr>
    </thead>
    <tbody>
    {% for row in udata %}
        <tr>
            <td>{{ row.id }}</td>
            <td>{{ row.login|escape|striptags }}</td>
            <td>{{ row.email|escape|striptags }}</td>
            <td class="text-center">
                <a href="?object=components&action=user&make=edit&id={{ row.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
                <a href="?object=components&action=user&make=delete&id={{ row.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a>
            </td>
        </tr>
    {% endfor %}
    </tbody>
</table>

{{ pagination }}