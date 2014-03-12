{% import 'macro/notify.tpl' as notify %}
{% if add.notify.time_delay %}
    {{ notify.error(language.comments_api_delay_exception) }}
{% endif %}
{% if add.notify.wrong_text %}
    {{ notify.error(language.comments_api_incorrent_length) }}
{% endif %}
{% for comment in local %}
<div class="media">
    <a class="pull-left" href="{{ system.url }}/user/id{{ comment.author_id }}">
        <img class="media-object img-responsive" src="{{ system.script_url }}/{{ comment.author_avatar }}" style="width: 64px;height: 64px;">
    </a>
    <div class="media-body">
        <div class="pull-right">
            {% if comment.can_delete %}<a href="#comment_site" onclick="return deletecomment({{ comment.comment_id }});">{{ language.comment_text_delete_link }}</a> |{% endif %}
            {% if comment.can_edit %}<a href="#edit-comment" data-toggle="modal" class="edit-comment" onclick="return editcomment({{ comment.comment_id }})">{{ language.comment_text_edit_link }}</a> |{% endif %}
            {% if user.id > 0 %}<a onclick="replayto('{{ comment.author_nick }}')" href="#comment_value"><i class="icon-random"></i>{{ language.comments_text_answerto }}</a> {% endif %}
        </div>
        <h5 class="media-heading">{{ language.comments_text_messagefrom }}: <a href="{{ system.url }}/user/id{{ comment.author_id }}">{{ comment.author_nick }}</a>, {{ language.comments_text_messageon }} {{ comment.comment_date }}</h5>
        {{ comment.comment_text }}
    </div>
</div>
{% endfor %}