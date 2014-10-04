{% include 'components/user/profile/profile_settings_menu.tpl' %}
<h3>{{ language.usercontrol_profile_settings_logs_title }}</h3>
<p>{{ language.usercontrol_profile_settings_logs_desc }}</p>
<hr />
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>{{ language.usercontrol_profile_settings_logs_th_date }}</th>
            <th>{{ language.usercontrol_profile_settings_logs_th_type }}</th>
            <th>IP</th>
        </tr>
        </thead>
        <tbody>
        {% for row in local.profilelog %}
        <tr>
            <td>{{ row.date }}</td>
            <td>{{ row.lang_type|default(row.type) }}</td>
            <td>{{ row.ip }}</td>
        </tr>
        {% endfor %}
        </tbody>
    </table>
</div>