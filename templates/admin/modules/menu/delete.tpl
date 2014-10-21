{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_menu_delete_title }}</small></h1>
<hr />
<p>{{ language.admin_modules_menu_delete_desc }}</p>
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
        <tr>
            <td>{{ modmenu.name }}</td>
            <td>{{ modmenu.tag }}</td>
        </tr>
        </tbody>
    </table>
</div>
<form method="post">
    <input type="submit" name="submit" class="btn btn-danger" value="{{ language.admin_modules_menu_delete_btn_del }}" />
    <a href="?object=modules&action=menu" class="btn btn-default">{{ language.admin_modules_menu_delete_btn_cancel }}</a>
</form>