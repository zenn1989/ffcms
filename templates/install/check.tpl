{% import 'macro/notify.tpl' as ntpl %}
{% if notify.prepare %}
    {% if notify.prepare.lock %}
        {{ ntpl.error(language.install_locked) }}
    {% endif %}
    {% if notify.prepare.cfg_write %}
        {{ ntpl.error(language.install_config_notwritable) }}
    {% endif %}
    {% if notify.prepare.inst_write %}
        {{ ntpl.error(language.install_self_notwritable) }}
    {% endif %}
    {% if notify.prepare.sql_notfound %}
        {{ ntpl.error(language.install_sql_not_found) }}
    {% endif %}
    {% if notify.prepare.inst_unlock %}
        {{ ntpl.error(language.install_unlock_file_require) }}
    {% endif %}
{% else %}
<p>{{ language.install_check_desc }}</p>
<div class="table-responsive">
    <table class="table table-bordered">
        <tbody>
        <tr class="{% if compare.php_check %}alert-success{% else %}alert-danger{% endif %}">
            <td>PHP</td>
            <td>{{ compare.php_version }}</td>
        </tr>
        <tr class="{% if compare.php_pdo %}alert-success{% else %}alert-danger{% endif %}">
            <td>PHP::PDO</td>
            <td>{% if compare.php_pdo %}On{% else %}Off{% endif %}</td>
        </tr>
        <tr class="{% if compare.apache_rewrite %}alert-success{% else %}alert-danger{% endif %}">
            <td>Apache mod_rewrite</td>
            <td>{% if compare.apache_rewrite %}On{% else %}Off{% endif %}</td>
        </tr>
        <tr class="{% if compare.php_gd %}alert-success{% else %}alert-danger{% endif %}">
            <td>PHP GD Lib</td>
            <td>{% if compare.php_gd %}On{% else %}Off{% endif %}</td>
        </tr>
        </tbody>
    </table>
</div>
{% if compare.all_ok %}
    <a href="?action=install" class="btn btn-success">{{ language.install_check_btn_start }}</a>
{% else %}
    {{ ntpl.error(language.install_check_fix_notify) }}
{% endif %}
{% endif %}