{% import 'macro/notify.tpl' as notify %}
<h3 style="text-align: center;">{{ language.usercontrol_recovery_header }}</h3>
<ul class="nav nav-tabs">
    <li><a href="{{ system.url }}/user/login.html">{{ language.usercontrol_auth_header }}</a></li>
    <li><a href="{{ system.url }}/user/register.html">{{ language.usercontrol_reg_header }}</a></li>
    <li class="active"><a href="{{ system.url }}/user/recovery.html">{{ language.usercontrol_recovery_header }}</a></li>
</ul>
<br />
{% if local.submit %}
    {% if local.notify.captcha_error %}
        {{ notify.error(language.usercontrol_captcha_form_error) }}
    {% endif %}
    {% if local.notify.email_typewrong %}
        {{ notify.error(language.usercontrol_invalid_email_error) }}
    {% endif %}
    {% if local.notify.email_notexist %}
        {{ notify.error(language.usercontrol_recovery_mail_unknown) }}
    {% endif %}
    {% if local.notify.success %}
        {{ notify.success(language.usercontrol_recovery_mail_sended) }}
    {% endif %}
{% endif %}
<form class="form-horizontal" method="post" action="">
    <div class="form-group">
        <label class="control-label col-md-3">{{ language.usercontrol_auth_email }}</label>

        <div class="col-md-9">
            <input name="email" type="text" placeholder="ivan.petrov@gmail.com" class="form-control">
        </div>
    </div>
    {% if local.cfg.captcha_full %}
        <script>
            var RecaptchaOptions = { theme : 'white' };
        </script>
        <div class="form-group">
            <label class="control-label col-md-3">{{ language.usercontrol_auth_captcha }}</label>

            <div class="col-md-9">
                {{ local.captcha }}
            </div>
        </div>
    {% else %}
        <div class="form-group">
            <label class="control-label col-md-3">{{ language.usercontrol_auth_captcha }}</label>

            <div class="col-md-9">
                <img src="{{ local.captcha }}" id="captcha"/><a href="#" onclick="document.getElementById('captcha').src='{{ local.captcha }}?'+Math.random();"><i class="fa fa-refresh"></i></a>
                <input type="text" name="captcha" class="form-control" required>
            </div>
        </div>
    {% endif %}
    <div class="form-group">
        <div class="col-md-offset-3 col-md-9">
            <input type="submit" name="submit" class="btn btn-inverse" value="{{ language.usercontrol_recovery_button }}"/>
        </div>
    </div>
</form>