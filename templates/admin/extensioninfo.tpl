{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_extension_info_title }}</h1>
<hr />
{% if extinfo.script_version == null or extinfo.script_compatable == null %}
    {{ notifytpl.error(language.admin_extension_info_noversion_support) }}
{% else %}
    {% if extinfo.script_version != extinfo.db_version %}
        <p><a href="?object=updates&action=extensions" class="btn btn-success btn-block">{{ language.admin_extension_info_update_extension }}</a></p>
        {{ notifytpl.error(language.admin_extension_info_version_wrong) }}
    {% endif %}
    {% if extinfo.script_compatable != system.version %}
        {{ notifytpl.error(language.admin_extension_info_version_uncompatable) }}
    {% endif %}
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>{{ language.admin_extension_info_th_param }}</th>
            <th>{{ language.admin_extension_info_th_data }}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ language.admin_extension_info_td_sysname }}</td>
            <td>{{ extinfo.sys_name }}</td>
        </tr>
        <tr>
            <td>{{ language.admin_extension_info_td_systype }}</td>
            <td>{{ extinfo.sys_type }}</td>
        </tr>
        <tr>
            <td>{{ language.admin_extension_info_td_script_version }}</td>
            <td>{{ extinfo.script_version }}</td>
        </tr>
        <tr>
            <td>{{ language.admin_extension_info_td_script_compatable }}</td>
            <td>{{ extinfo.script_compatable }}</td>
        </tr>
        <tr>
            <td>{{ language.admin_extension_info_td_db_version }}</td>
            <td>{{ extinfo.db_version }}</td>
        </tr>
        <tr>
            <td>{{ language.admin_extension_info_td_db_compatable }}</td>
            <td>{{ extinfo.db_compatable }}</td>
        </tr>
        </tbody>
    </table>
{% endif %}