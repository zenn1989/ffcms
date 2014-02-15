<h1>{{ extension.title }}<small>{{ language.admin_component_static_delete }}</small></h1>
<hr />

<p>{{ language.admin_component_static_delete_message }}!</p>
<table class="table table-bordered table-responsive">
    <thead>
        <tr>
            <th>{{ language.admin_component_static_th_id }}</th>
            <th>{{ language.admin_component_static_th_title }}</th>
            <th>{{ language.admin_component_static_th_path }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ static.id }}</td>
            <td>{{ static.title }}</td>
            <td>/static/{{ static.pathway }}</td>
        </tr>
    </tbody>
</table>
<form method="post" action="">
        <input type="submit" name="submit" value="{{ language.admin_component_static_delete_button }}" class="btn btn-danger"/>
        <a href="?object=components&action=static" class="btn btn-success">{{ language.admin_component_static_delete_cancel }}</a>
</form>