{% import 'macro/notify.tpl' as notify %}
<h3 style="text-align: center;">{{ language.usercontrol_auth_header }}</h3>
<ul class="nav nav-tabs">
    <li><a href="{{ system.url }}/user/login.html">{{ language.usercontrol_auth_header }}</a></li>
    <li><a href="{{ system.url }}/user/register.html">{{ language.usercontrol_reg_header }}</a></li>
    <li><a href="{{ system.url }}/user/recovery.html">{{ language.usercontrol_recovery_header }}</a></li>
    <li class="active"><a href="#">OpenID</a></li>
</ul>
<br />
{% if local.notify %}
    {% if local.notify.email_invalid %}
        {{ notify.error(language.usercontrol_invalid_email_error) }}
    {% endif %}
    {% if local.notify.email_exist %}
        {{ notify.error(language.usercontrol_mail_exist) }}
    {% endif %}
    {% if local.notify.login_exist %}
        {{ notify.error(language.usercontrol_login_exist) }}
    {% endif %}
    {% if local.notify.nick_wronglength %}
        {{ notify.error(language.usercontrol_nick_incorrent) }}
    {% endif %}
{% endif %}
<p>{{ language.usercontrol_openid_reg_notify }}</p>
<form class="form-horizontal" method="post" action="">
    <input type="hidden" name="openid_token" value="{{ local.openid.session }}" />
    <div class="form-group">
        <label class="control-label col-md-3">{{ language.usercontrol_auth_email }}</label>

        <div class="col-md-9">
            <input name="email" type="text" value="{{ local.openid.email }}" class="form-control" required>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3">{{ language.usercontrol_auth_login }}</label>

        <div class="col-md-9">
            <input name="login" type="text" value="{{ local.openid.login }}" class="form-control" autocomplete="off" required>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3">{{ language.usercontrol_auth_pseudoname }}</label>

        <div class="col-md-9">
            <input name="nick" type="text" value="{{ local.openid.name }}" class="form-control" autocomplete="off" required>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            <input type="submit" name="submit" class="btn btn-inverse" value="{{ language.usercontrol_reg_button }}"/>
        </div>
    </div>
</form>
