<table class='table table-striped'>
    <tbody>
    {% for bookmark in local.bookmarks %}
    <tr>
        <td>{{ bookmark.title }} <br/>
            <em class="pull-right"><a href="{{ system.url }}/api.php?iface=front&object=redirect&url={{ bookmark.link }}" target="_blank">{{ bookmark.link }}</a></em>
        </td>
    </tr>
    {% else %}
        <tr>
            <td>{{ language.global_info_missed }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>
<ul class="pager">
    {% if local.wall.bookindex > 0 %}
        <li class="previous"><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/bookmarks/{{ local.wall.bookindex-1 }}">&larr;</a></li>
    {% endif %}
    {% if local.wall.bookindex+1 < local.wall.maxbook/local.wall.bookperpage %}
        <li class="next"><a href="{{ system.url }}/user/id{{ local.profile.user_id }}/bookmarks/{{ local.wall.bookindex+1 }}">&rarr;</a></li>
    {% endif %}
</ul>