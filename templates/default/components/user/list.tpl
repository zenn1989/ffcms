<div class="row">
    <div class="col-md-8">
        <h2>{{ language.usercontrol_listuser_header_text }}</h2>
        <ul class="nav nav-tabs" role="tablist">
            <li{% if local.tabtype == 'all' %} class="active"{% endif %}><a href="{{ system.url }}/user">{{ language.usercontrol_listuser_tab_all }}</a></li>
            {% if local.use_karma %}<li{% if local.tabtype == 'karma' %} class="active"{% endif %}><a href="{{ system.url }}/user/karma/">{{ language.usercontrol_listuser_tab_karma }}</a></li>{% endif %}
            <li{% if local.tabtype == 'search' %} class="active"{% endif %}><a href="{{ system.url }}/user/search/">{{ language.usercontrol_listuser_tab_search }}</a></li>
        </ul>
        {% if local.tabtype == 'search' %}
        <div class="row">
            <div class="col-md-12">
                <form method="post">
                    <div class="input-group">
                        <input name="search_user" type="text" class="form-control" placeholder="Kurt">
                        <span class="input-group-btn">
                           <input type="submit" name="submit" class="btn btn-default" value="{{ language.usercontrol_listuser_search_button }}">
                        </span>
                    </div>
                </form>
            </div>
        </div>
        {% endif %}
        {% for user in local.user %}
        <div class="row" style="padding-top: 10px">
            <div class="col-md-3"><img src="{{ system.script_url }}/{{ user.user_avatar }}" class="img-responsive"/></div>
            <div class="col-md-9">
                <h3><a href="{{ system.url }}/user/id{{ user.user_id }}">{{ user.user_name }}
                        {% if local.use_karma %}
                            <span class="pull-right label {% if user.user_karma > 0 %}label-success{% elseif user.user_karma == 0 %}label-default{% else %}label-danger{% endif %}">
                            {% if user.user_karma > 0 %}+{% endif %}{{ user.user_karma }}
                            </span>{% endif %}
                    </a>
                </h3>
                {% if user.user_status %}<p>&laquo;{{ user.user_status }}&raquo;</p>{% endif %}
                <div class="pull-right">{{ user.user_regdate }}</div>
            </div>
        </div>
        <hr />
        {% endfor %}
    </div>
    <div class="col-md-4">
        <h2>{{ language.usercontrol_listuser_info_text }}</h2>
        <hr/>
        <h4>{{ language.usercontrol_listuser_item_many }}</h4>
        <p style="padding-left: 10%;">{{ local.statistic.total }} {{ language.usercontrol_listuser_people }}</p>
        <h4>{{ language.usercontrol_listuser_info_demography }}</h4>
        <p style="padding-left: 10%;">{{ local.statistic.male }} - {{ language.usercontrol_listuser_male }}<br/>
            {{ local.statistic.female }} - {{ language.usercontrol_listuser_female }}<br/>
            {{ local.statistic.total - local.statistic.male - local.statistic.female }} - {{ language.usercontrol_listuser_unknown }}</p>
        {% if local.online %}
        <h4>{{ language.usercontrol_listuser_nowactive }}</h4>
        <p style="padding-left: 10%;">
            {% for online in local.online %}
                <a href="{{ system.url }}/user/id{{ online.user_id }}">{{ online.user_name }}</a>
            {% endfor %}
        </p>
        {% endif %}
    </div>
</div>
<br />
{{ local.pagination }}