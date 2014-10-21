{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_modules_menu_itemdel_title }}</small></h1>
<hr />
{% if modmenu.have_depend %}
    <p class="alert alert-danger">{{ language.admin_modules_menu_itemdel_notify_cheilds }}</p>
{% else %}
    <p>{{ language.admin_modules_menu_itemdel_alert }}</p>
    <div class="table-responsive">
        <table class="table table-striped">
            <tbody>
            <tr>
                <td>{% if modmenu.data.is_depend %}<i class="fa fa-arrow-right"></i>{% else %}<i class="fa fa-home"></i>{% endif %} {{ modmenu.data.name }}</td>
                <td>{{ modmenu.data.url }}</td>
            </tr>
            </tbody>
        </table>
    </div>
    <form method="post">
        <input type="submit" name="submit" class="btn btn-danger" value="{{ language.admin_modules_menu_itemdel_btn_del }}" />
        <a href="?object=modules&action=menu&make=manage&id={{ modmenu.data.menu_id }}" class="btn btn-default">{{ language.admin_modules_menu_itemdel_btn_cancel }}</a>
    </form>
{% endif %}