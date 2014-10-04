{% import 'macro/notify.tpl' as notify %}
<h3 style="text-align: center;">{{ language.usercontrol_reg_header }}</h3>
<ul class="nav nav-tabs">
    <li><a href="{{ system.url }}/user/login.html">{{ language.usercontrol_auth_header }}</a></li>
    <li class="active"><a href="{{ system.url }}/user/register.html">{{ language.usercontrol_reg_header }}</a></li>
    <li><a href="{{ system.url }}/user/recovery.html">{{ language.usercontrol_recovery_header }}</a></li>
</ul>
<br />
{% if local.submit %}
    {% if local.notify.captcha_error %}
        {{ notify.error(language.usercontrol_captcha_form_error) }}
    {% endif %}
    {% if local.notify.email_error %}
        {{ notify.error(language.usercontrol_invalid_email_error) }}
    {% endif %}
    {% if local.notify.passlength_error %}
        {{ notify.error(language.usercontrol_incorrent_password_error) }}
    {% endif %}
    {% if local.notify.mail_exist %}
        {{ notify.error(language.usercontrol_mail_exist) }}
    {% endif %}
    {% if local.notify.login_exist %}
        {{ notify.error(language.usercontrol_login_exist) }}
    {% endif %}
    {% if local.notify.nicklength_error %}
        {{ notify.error(language.usercontrol_nick_incorrent) }}
    {% endif %}
    {% if local.notify.aprove_sended %}
        {{ notify.warning(language.usercontrol_register_success_aprove) }}
    {% endif %}
    {% if local.notify.success %}
        {{ notify.success(language.usercontrol_register_success_noaprove) }}
    {% endif %}
{% endif %}
<div class="row">
    <div class="col-md-8">
        <form class="form-horizontal" method="post" action="" autocomplete="off">
            <div class="form-group">
                <label class="control-label col-md-3">{{ language.usercontrol_auth_login }}</label>

                <div class="col-md-9">
                    <input name="login" type="text" placeholder="ivan2013" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3">{{ language.usercontrol_auth_email }}</label>

                <div class="col-md-9">
                    <input name="email" type="text" placeholder="ivan.petrov@gmail.com" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3">{{ language.usercontrol_auth_pseudoname }}</label>

                <div class="col-md-9">
                    <input name="nick" type="text" placeholder="Kurt Cobain" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3">{{ language.usercontrol_auth_pass }}</label>

                <div class="col-md-9">
                    <input type="password" name="password" placeholder="Password" class="form-control" required>
                </div>
            </div>
            {% if local.cfg.use_captcha %}
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
                            <img src="{{ local.captcha }}" id="captcha"/><a href="#" onclick="document.getElementById('captcha').src='{{ local.captcha }}?'+Math.random();"><i class="fa fa-refresh"></i></a><br/>
                            <input type="text" name="captcha" class="form-control" required>
                        </div>
                    </div>
                {% endif %}
            {% endif %}
            <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                    <input type="submit" name="submit" class="btn btn-inverse" value="{{ language.usercontrol_reg_button }}"/>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-4">
        {% if local.cfg.use_openid %}
            <p>{{ language.usercontrol_openid_desc }}</p>
            <script src="{{ system.protocol }}://loginza.ru/js/widget.js" type="text/javascript"></script>
            <a href="{{ system.protocol }}://loginza.ru/api/widget?token_url={{ system.url }}/user/openid.html" class="loginza" rel="nofollow">
                <img src="{{ system.protocol }}://loginza.ru/img/sign_in_button_gray.gif" alt="Use social network" />
            </a>
        {% endif %}
    </div>
</div>