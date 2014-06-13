{% import 'macro/notify.tpl' as notify %}
<h1>{{ language.feedback_form_title }}</h1>
<hr />
<p>{{ language.feedback_form_desc }}</p>
{% if local.notify %}
    {% if local.notify.wrong_email %}
        {{ notify.error(language.feedback_error_email) }}
    {% endif %}
    {% if local.notify.wrong_title %}
        {{ notify.error(language.feedback_error_title) }}
    {% endif %}
    {% if local.notify.wrong_name %}
        {{ notify.error(language.feedback_error_name) }}
    {% endif %}
    {% if local.notify.wrong_text %}
        {{ notify.error(language.feedback_error_text) }}
    {% endif %}
    {% if local.notify.wrong_captcha %}
        {{ notify.error(language.feedback_error_captcha) }}
    {% endif %}
    {% if local.notify.success %}
        {{ notify.success(language.feedback_success_send) }}
    {% endif %}
{% endif %}
<form method="post" class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="control-label col-md-3">{{ language.feedback_form_name_label }}</label>

            <div class="controls col-md-9">
                <input type="text" name="topic_name" class="form-control" required/>
                <p class="help-block">{{ language.feedback_form_name_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-3">{{ language.feedback_form_title_label }}</label>

            <div class="controls col-md-9">
                <input type="text" name="topic_title" class="form-control" required/>
                <p class="help-block">{{ language.feedback_form_title_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-3">{{ language.feedback_form_text_label }}</label>

            <div class="controls col-md-9">
                <textarea name="topic_body" class="form-control" placeholder="Message ..." rows="5"></textarea>
                <p class="help-block">{{ language.feedback_form_text_desc }}</p>
            </div>
        </div>
        {% if user.id < 1 %}
        <div class="form-group">
            <label class="control-label col-md-3">{{ language.feedback_form_email_label }}</label>

            <div class="controls col-md-9">
                <input type="email" name="topic_email" class="form-control" required/>
                <p class="help-block">{{ language.feedback_form_email_desc }}</p>
            </div>
        </div>
        {% endif %}
        {% if local.captcha_full %}
            <script>
                var RecaptchaOptions = { theme : 'white' };
            </script>
            <div class="form-group">
                <label class="control-label col-md-3">{{ language.feedback_form_captcha_label }}</label>

                <div class="controls col-md-9">
                    {{ local.captcha }}
                </div>
            </div>
        {% else %}
            <div class="form-group">
                <label class="control-label col-md-3">{{ language.feedback_form_captcha_label }}</label>

                <div class="controls col-md-9">
                    <img src="{{ local.captcha }}" id="captcha"/><a href="#" onclick="document.getElementById('captcha').src='{{ local.captcha }}?'+Math.random();"><i class="icon-refresh"></i></a><br/>
                    <input type="text" name="captcha" class="form-control" required>
                </div>
            </div>
        {% endif %}
        <div class="form-group">
            <div class="controls col-md-9">
                <input type="submit" name="dofeedback" value="{{ language.feedback_form_send_button }}" class="btn btn-danger" />
            </div>
        </div>
        </fieldset>
</form>