<h1>{{ language.stream_h1_title }}</h1>
<hr />
{% if stream %}
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>{{ language.stream_th_date }}</th>
            <th>{{ language.stream_th_user }}</th>
            <th>{{ language.stream_th_action }}</th>
            <th>{{ language.stream_th_object }}</th>
        </tr>
        </thead>
        <tbody>
        {% for item in stream %}
        <tr>
            <td>{{ item.id }}</td>
            <td>{{ item.date }}</td>
            <td>{% if item.user_name == null %}{{ item.user_id }}{% else %}<a href="{{ system.url }}/user/id{{ item.user_id }}" target="_blank">{{ item.user_name }}</a>{% endif %}</td>
            <td>{{ item.type_language|default(item.type) }}</td>
            <td><a href="{{ item.url }}" target="_blank">{{ item.text|default(item.url) }}</a></td>
        </tr>
        {% endfor %}
        </tbody>
    </table>
</div>
{% else %}
    <p class="alert alert-warning">{{ language.stream_line_empty }}</p>
{% endif %}
{{ pagination }}