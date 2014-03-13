<p>{{ language.usercontrol_profile_news_description }}</p>
{% if local.newslist %}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>{{ language.usercontrol_profile_news_object }}</th>
            <th>{{ language.usercontrol_profile_news_date }}</th>
            <th>{{ language.usercontrol_profile_news_status }}</th>
        </tr>
    </thead>
    <tbody>
        {% for item in local.newslist %}
        <tr>
            {% if item.display == 0 %}
            <td><a href="{{ system.url }}/news/add/{{ item.id }}">{{ item.title }}</a></td>
            <td>{{ item.date }}</td>
            <td class="alert alert-warning">{{ language.usercontrol_profile_news_tomoderate }}</td>
            {% else %}
            <td>{{ item.title }}</td>
            <td>{{ item.date }}</td>
            <td class="alert alert-success">{{ language.usercontrol_profile_news_topublic }}</td>
            {% endif %}
        </tr>
        {% endfor %}
    </tbody>
</table>
{% endif %}