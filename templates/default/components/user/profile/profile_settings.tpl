{% import 'macro/notify.tpl' as notify %}
{% include 'components/user/profile/profile_settings_menu.tpl' %}
<form method="post" action="" class="form-horizontal">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        <h3>{{ language.usercontrol_profile_settings_title_public }}</h3>

        <p>{{ language.usercontrol_profile_settings_desc_public }}</p>
        <hr/>
        {% if local.form.submit %}
            {{ notify.success(language.usercontrol_profile_settings_notify_updated) }}
            {% if local.form.pass_changed %}
                {{  notify.warning(language.usercontrol_profile_settings_notify_passchange) }}
            {% endif %}
        {% endif %}
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_name_title }}</label>

            <div class="col-md-9">
                <input type="text" name="nickname" value="{{ local.settings.user_name }}" class="form-control" />

                <p class="help-block">{{ language.usercontrol_profile_settings_label_name_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_phone_title }}</label>

            <div class="col-md-9">
                <input type="text" name="phone" value="{{ local.settings.user_phone }}" class="form-control" />

                <p class="help-block">{{ language.usercontrol_profile_settings_label_phone_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_birthday_title }}</label>

            <div class="col-md-9">
                <div class="col-md-4">
                    <select name="bitrhday[day]" class="form-control">
                        {% for day in 1..31 %}
                            <option value="{{ day }}"{% if day == local.settings.user_birth_day %} selected{% endif %}>{{ day }}</option>
                        {% endfor %}
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="bitrhday[month]" class="form-control">
                        {% for month in 1..12 %}
                            <option value="{{ month }}"{% if month == local.settings.user_birth_month %} selected{% endif %}>{{ month }}</option>
                        {% endfor %}
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="bitrhday[year]" class="form-control">
                        {% for year in local.settings.current_year-120..local.settings.current_year %}
                            <option value="{{ year }}"{% if year == local.settings.user_birth_year %} selected{% endif %}>{{ year }}</option>
                        {% endfor %}
                    </select>
                </div>

                <p class="help-block">{{ language.usercontrol_profile_settings_label_birthday_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_sex_title }}</label>

            <div class="col-md-9">
                <select name="sex" class="form-control">
                    <option value="1"{% if local.settings.user_sex == 1 %} selected{% endif %}>{{ language.usercontrol_profile_sex_man }}</option>
                    <option value="2"{% if local.settings.user_sex == 2 %} selected{% endif %}>{{ language.usercontrol_profile_sex_woman }}</option>
                </select>

                <p class="help-block">{{ language.usercontrol_profile_settings_label_sex_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_website_title }}</label>

            <div class="col-md-9">
                <input type="text" name="website" value="{{ local.settings.user_website }}" class="form-control"/>

                <p class="help-block">{{ language.usercontrol_profile_settings_label_website_desc }}</p>
            </div>
        </div>
        <h3>{{ language.usercontrol_profile_settings_title_password }}</h3>

        <p>{{ language.usercontrol_profile_settings_desc_password }}</p>
        <hr/>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_oldpwd_title }}</label>

            <div class="col-md-9">
                <input type="password" name="oldpwd" value="" autocomplete="off" class="form-control"/>

                <p class="help-block">{{ language.usercontrol_profile_settings_label_oldpwd_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_newpwd_title }}</label>

            <div class="col-md-9">
                <input type="password" name="newpwd" value="" autocomplete="off" class="form-control"/>

                <p class="help-block">{{ language.usercontrol_profile_settings_label_newpwd_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">{{ language.usercontrol_profile_settings_label_renewpwd_title }}</label>

            <div class="col-md-9">
                <input type="password" name="renewpwd" value="" autocomplete="off" class="form-control"/>

                <p class="help-block">{{ language.usercontrol_profile_settings_label_renewpwd_desc }}</p>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-9">
                <input type="submit" name="saveprofile" value="{{ language.global_send_button }}" class="btn btn-success"/>
            </div>
        </div>
    </fieldset>
</form>