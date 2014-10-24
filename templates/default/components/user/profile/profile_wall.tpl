{% import 'macro/notify.tpl' as notify %} {# fast notification macros #}
<div class="tab-content">
    <div class="tab-pane active">
       <div class="table-responsive">
            <table class="table table-striped">
                <tr>
                    <td>{{ language.usercontrol_profile_pers_reg_date }}</td>
                    <td>{{ local.wall.reg_date }}</td>
                </tr>
                <tr>
                    <td>{{ language.usercontrol_profile_pers_birthday_date }}</td>
                    <td>{{ local.wall.birthday }}</td>
                </tr>
                <tr>
                    <td>{{ language.usercontrol_profile_pers_sex }}</td>
                    <td>{{ local.wall.sex }}</td>
                </tr>
                {% if local.wall.phone %}
                <tr>
                    <td>{{ language.usercontrol_profile_pers_phone }}</td>
                    <td>{{ local.wall.phone }}</td>
                </tr>
                {% endif %}
                {% if local.wall.webpage %}
                <tr>
                    <td>{{ language.usercontrol_profile_pers_website }}</td>
                    <td><a href="{{ local.wall.webpage }}" target="_blank" rel="nofollow">{{ local.wall.webpage }}</a>
                    </td>
                </tr>
                {% endif %}
                {% for ufield in local.wall.ufields %}
                    {% if ufield.default != null %}
                    <tr>
                        <td>{{ ufield.title }}</td>
                        <td>
                            {% if ufield.type == 'text' %}
                            {{ ufield.default }}
                            {% elseif ufield.type == 'img' %}
                            <img src="{{ system.script_url }}/upload{{ ufield.default }}" alt="{{ ufield.title }}" />
                            {% elseif ufield.type == 'link' %}
                            <a href="{% if ufield.redirect %}{{ system.script_url }}/api.php?iface=front&object=redirect&url={% endif %}{{ ufield.default }}" target="_blank">{{ ufield.default }}</a>
                            {% endif %}
                        </td>
                    </tr>
                    {% endif %}
                {% endfor %}
            </table>
       </div>
            <h3 class="centered">{{ language.usercontrol_profile_pers_wall }}</h3>
            <hr/>
            <div class="row">
                <div class="col-md-12">
                    {% if user.id > 0 and local.profile.is_friend or local.profile.is_self %}
                        {% if local.wall.dopost %}
                            {% if local.wall.time_limit %}
                                {{ notify.error(language.usercontrol_profile_wall_answer_spamdetect) }}
                            {% endif %}
                        {% endif %}
                        <form action="{{ system.url }}/user/id{{ local.profile.user_id }}" method="post">
                            <textarea name="wall_text" class="form-control"
                                      placeHolder="{{ language.usercontrol_profile_pers_form_write }}"></textarea>
                            <input type="submit" name="wall_post" value="{{ language.global_send_button }}"
                                   class="btn btn-success pull-right"/>
                        </form>
                    {% endif %}
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-12">
                    {% for post in local.post %}
                        <div class="media">
                            <a class="pull-left" href="{{ system.url }}/user/id{{ post.caster_id }}">
                                <img class="media-object" src="{{ system.script_url }}/{{ post.caster_avatar }}" style="width:64px;height:64px;">
                            </a>
                            <div class="media-body">
                                <h5 class="media-heading"><a href="{{ system.url }}/user/id{{ post.caster_id }}">{{ post.caster_name }}</a>, {{ post.time }}</h5>
                                {{ post.message }}
                                <br />
                                <div class="pull-right">
                                    <a href="#readanswer" class="answershow" id="{{ post.id }}" data-toggle="modal"><i
                                                class="icon-random"></i> {{ language.usercontrol_profile_wall_answers }}</a>
                                </div>
                            </div>
                        </div>
                    {% endfor %}
                </div>
            </div>
            <div>
                <ul class="pager">
                    {% if local.wall.postindex > 0 %}
                    <li class="previous">
                        <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/wall/{{ local.wall.postindex-1 }}">&larr;</a>
                    </li>
                    {% endif %}
                    {% if local.wall.postindex+1 < local.wall.maxindex/local.wall.postperpage %}
                    <li class="next">
                        <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/wall/{{ local.wall.postindex+1 }}">&rarr;</a>
                    </li>
                    {% endif %}
                </ul>
            </div>
        </div>
</div>
{# modal item for reading answers #}
<div id="readanswer" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3>{{ language.usercontrol_profile_wall_answer_head }}</h3>
            </div>
            <div class="modal-body">
                {% if user.id > 0 %}
                    <div id="requestpost">
                        <textarea class="form-control" id="answer"></textarea>

                        <div class="pull-right"><a href="#" id="addanswer" class="btn btn-success">{{ language.global_send_button }}</a>
                        </div>
                        <br/>
                    </div>
                {% endif %}
                <hr/>
                <div id="wall-jquery">{{ language.usercontrol_profile_wall_answer_load }}</div>
            </div>
        </div>
    </div>
</div>