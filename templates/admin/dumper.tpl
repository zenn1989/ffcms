{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_nav_li_backup }}</h1>
<hr />
{% if notify.rw_error %}
    {{ notifytpl.error(language.admin_dump_export_error_rw) }}
{% endif %}
<p>{{ language.admin_dump_export_desc1 }}</p>
<p>{{ language.admin_dump_export_desc2 }}</p>
<p class="alert alert-danger">{{ language.admin_dump_export_desc3 }}</p>
<p>{{ language.admin_dump_export_lastcopy }}: <strong>{% if backup.last %} {{ backup.last }}{% else %} {{ language.admin_dump_notexist }} {% endif %}</strong></p>
<form action="" method="post">
    <input type="submit" name="submit" value="{{ language.admin_dump_export_submit }}" class="btn btn-success" />
</form>