<h1>{{ extension.title }}<small>{{ language.admin_modules_comment_manage_title }}</small></h1>
<hr />
{% include 'modules/comments/menu_include.tpl' %}
<table class="table table-responsive table-bordered">
    <thead>
    <tr>
        <th>{{ language.admin_modules_comment_del_th1 }}</th>
        <th>{{ language.admin_modules_comment_del_th2 }}</th>
        <th>{{ language.admin_modules_comment_del_th3 }}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ comments.data.id }}</td>
        <td>{{ comments.data.user_name }}</td>
        <td>{{ comments.data.text }}</td>
    </tr>
    </tbody>
</table>
<form action="" method="post">.
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <input type="submit" name="delete_comment" value="{{ language.admin_modules_comment_del_button }}" class="btn btn-danger"/>
</form>