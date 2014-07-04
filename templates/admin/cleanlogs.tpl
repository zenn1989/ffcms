{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_block_operation_logclean }}</h1>
<hr />
{% if notify.log_clear %}
    {{ notifytpl.success(language.admin_logclean_success_action) }}
{% endif %}
<p>{{ language.admin_logclean_desc }}</p>
<form action="" method="post">
    <input type="submit" name="submit" value="{{ language.admin_logclean_cleanup }}" class="btn btn-danger" />
    <a href="?" class="btn btn-warning">{{ language.admin_logclean_cancel }}</a>
</form>