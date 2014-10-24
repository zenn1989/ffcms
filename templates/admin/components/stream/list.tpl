{% import 'macro/scriptdata.tpl' as scriptdata %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_stream_manage_title }}</small></h1>
<hr />
{% include 'components/stream/menu_include.tpl' %}
{% if stream %}
<form action="" method="post" onsubmit="return confirm('{{ language.admin_onsubmit_warning }}');">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>{{ language.admin_components_stream_th_type }}</th>
                <th>{{ language.admin_components_stream_th_user }}</th>
                <th>{{ language.admin_components_stream_th_date }}</th>
                <th>{{ language.admin_components_stream_th_object }}</th>
            </tr>
            </thead>
            <tbody>
            {% for item in stream %}
                <tr class="checkbox-depend">
                    <td><input type="checkbox" name="check_array[]" class="check_array" value="{{ item.id }}"/> {{ item.id }}</td>
                    <td>{{ item.type }}</td>
                    <td>
                        {% if item.user_name == null %}
                            {{ item.user_id }}
                        {% else %}
                            <a href="{{ system.url }}/user/id{{ item.user_id }}" target="_blank">{{ item.user_name }}</a>
                        {% endif %}
                        </td>
                    <td>{{ item.date }}</td>
                    <td><a href="{{ item.url }}" target="_blank">{{ item.text|default(item.url) }}</a></td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    <a id="checkAll" class="btn btn-default">{{ language.admin_checkbox_all }}</a>
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <input type="submit" name="deleteSelected" value="{{ language.admin_checkbox_delselect }}" class="btn btn-danger" />
    {{ scriptdata.checkjs('#checkAll', '.check_array') }}
</form>
{% else %}
    {{ notifytpl.warning(language.admin_components_stream_notify_empty) }}
{% endif %}
{{ pagination }}