{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_block_operation_cacheclean }}</h1>
<hr />
{% if notify.cache_clear %}
    {{ notifytpl.success(language.admin_cacheclean_successaction) }}
{% endif %}
<p>{{ language.admin_cacheclean_desc }}</p>
<p>{{ language.admin_cacheclean_size }}: <strong>{{ (local.cache_size/(1024*1024))|number_format(2) }}mb</strong></p>
<form action="" method="post">
    <input type="submit" name="submit" value="{{ language.admin_cacheclean_clean }}" class="btn btn-danger" />
    <a href="?" class="btn btn-warning">{{ language.admin_cacheclean_cancel }}</a>
</form>