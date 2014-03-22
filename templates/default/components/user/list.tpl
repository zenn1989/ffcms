<div class="row">
    <div class="col-md-8">
        <h2>{{ language.usercontrol_listuser_header_text }}</h2>
        <hr/>
        {% for user in local.user %}
        <div class="row" style="padding-top: 10px">
            <div class="col-md-3"><img src="{{ system.script_url }}/{{ user.user_avatar }}" class="img-responsive"/></div>
            <div class="col-md-9">
                <h3><a href="{{ system.url }}/user/id{{ user.user_id }}">{{ user.user_name }}</a></h3>

                <div class="pull-right">{{ user.user_regdate }}</div>
            </div>
        </div>
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
        <h4>{{ language.usercontrol_listuser_nowactive }}</h4>
        <p style="padding-left: 10%;">
            {% for online in local.online %}
                <a href="{{ system.url }}/user/id{{ online.user_id }}">{{ online.user_name }}</a>
            {% endfor %}
        </p>
    </div>
</div>
<br />
{{ local.pagination }}