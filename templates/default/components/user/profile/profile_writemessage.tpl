<div class="pull-left">
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/all" class="btn btn-success">{{ language.usercontrol_profile_pm_menuall }}</a>
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/in" class="btn btn-info">{{ language.usercontrol_profile_pm_menuin }}</a>
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/out" class="btn btn-danger">{{ language.usercontrol_profile_pm_menuout }}</a>
</div>
<div class="pull-right">
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/write" class="btn btn-success">{{ language.usercontrol_profile_pm_writenew }}</a>
</div><br/>
<hr/>
<h3>{{ language.usercontrol_profile_pm_write_header }}</h3>
<hr/>
<form action="" method="post">
    <div class="input-prepend">
        <span class="add-on">{{ language.usercontrol_profile_pm_write_touser }}: </span>
        {% if local.message.friend %}
        <select name="accepterid">
            {% for friend in local.message.friend %}
                <option value="{{ friend.user_id }}"{% if friend.user_id == local.message.target %} selected{% endif %}>{{ friend.user_name }}</option>
            {% endfor %}
        </select>
        {% endif %}
    </div>
    <strong>{{ language.usercontrol_profile_pm_write_text }}</strong><br/>
    <textarea class="input-block-level" name="message" rows="10"></textarea>

    <div class="pull-right"><input type="submit" name="sendmessage" value="{{ language.global_send_button }}" class="btn btn-success"/></div>
</form>