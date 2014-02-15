{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_block_operation_statclean }}</h1>
<hr />
{% if notify.stat_clear %}
    {{ notifytpl.success(language.admin_statclean_successclean) }}
{% endif %}
<p>{{ language.admin_statclean_desc }}</p>
<p>{{ language.admin_statclean_deprsize }}: <strong>{{ local.stats_row }}</strong></p>
<form action="" method="post">
    <input type="submit" name="submit" value="{{ language.admin_statclean_cleanup }}" class="btn btn-danger" />
    <a href="?" class="btn btn-warning">{{ language.admin_statclean_cancel }}</a>
</form>