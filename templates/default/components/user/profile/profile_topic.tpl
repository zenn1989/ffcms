<div class="pull-left">
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/all" class="btn btn-success">{{ language.usercontrol_profile_pm_menuall }}</a>
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/in" class="btn btn-info">{{ language.usercontrol_profile_pm_menuin }}</a>
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/out" class="btn btn-danger">{{ language.usercontrol_profile_pm_menuout }}</a>
</div>
<div class="pull-right">
    <a href="{{ system.url }}/user/id{{ local.profile.user_id }}/messages/write" class="btn btn-success">{{ language.usercontrol_profile_pm_writenew }}</a>
</div><br/>
<hr/>
<div class="well-item">
    <div class="media">
        <a class="pull-left" href="{{ system.url }}/user/id{{ local.message.main.user_id }}">
            <img style="max-height: 64px;max-width: 64px;" class="media-object" src="{{ system.script_url }}/{{ local.message.main.user_avatar }}">
        </a>
        <div class="media-body">
            <h4 class="media-heading"><a href="{{ system.url }}/user/id{{ local.message.main.user_id }}">{{ local.message.main.user_name }}</a>, {{ local.message.main.time }}</h4>
            <blockquote>{{ local.message.main.body }}</blockquote>
        </div>
    </div>
</div>

<hr/>
<div class="row">
    <div class="col-md-12">
        <form action="" method="post">
            <textarea name="topicanswer" class="form-control"></textarea>
            <input type="submit" name="newanswer" class="btn btn-success pull-right" value="{{ language.usercontrol_profile_pm_answermsg }}">
        </form>
    </div>
</div>
<br/>
{% for message in local.message.answer %}
    <div class="well">
        <div class="media">
            <a class="pull-left" href="{{ system.url }}/user/id{{ message.user_id }}">
                <img style="max-height: 64px;max-width: 64px;" class="media-object" src="{{ system.script_url }}/{{ message.user_avatar }}">
            </a>
            <div class="media-body">
                <h4 class="media-heading"><a href="{{ system.url }}/user/id{{ message.user_id }}">{{ message.user_name }}</a>, {{ message.time }}</h4>
                <blockquote>{{ message.body }}</blockquote>
            </div>
        </div>
    </div>
{% endfor %}