<h1>{{ extension.title }}<small>{{ language.admin_component_usercontrol_ufields_del_title }}</small></h1>
<hr />
{% include 'components/user/menu_include.tpl' %}
<p class="alert alert-warning">{{ language.admin_component_usercontrol_ufields_del_notify }}</p>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
        <tr>
            <td>#{{ ufields.id }}</td>
            <td>{{ ufields.name }}</td>
            <td>{{ ufields.type }}</td>
        </tr>
        </tbody>
    </table>
</div>
<form method="post">
    <input type="submit" name="submit" value="{{ language.admin_component_usercontrol_ufields_del_btn_del }}" class="btn btn-danger" />
    <a href="?object=components&action=user&make=ufield" class="btn btn-default">{{ language.admin_component_usercontrol_ufields_del_btn_cancel }}</a>
</form>