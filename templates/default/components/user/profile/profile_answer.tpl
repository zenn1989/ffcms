{% for answer in local.answer %}
<blockquote class="white">
    <table class="table">
        <tr>
            <td></td>
            <td><a href="{{ system.url }}/user/id{{ answer.poster_id }}">{{ answer.poster_name }}</a>, {{ answer.time }}</td>
        </tr>
        <tr>
            <td width="20%"><img src="{{ system.script_url }}/{{ answer.poster_avatar }}" style="max-width: 60px;"/></td>
            <td>{{ answer.message }}</td>
        </tr>
    </table>
</blockquote>
{% endfor %}
{% if local.answer == null %}
    {{ language.usercontrol_profile_wall_noanswer }}
{% endif %}