{% import 'macro/notify.tpl' as notify %}
<h3>{{ language.usercontrol_profile_photochange_title }}</h3>
<hr/>
<p>{{ language.usercontrol_profile_photochange_text }}</p>
{% if local.avatar.submit %}
    {% if local.avatar.success %}
        {{ notify.success(language.usercontrol_profile_photochange_success) }}
    {% endif %}
    {% if local.avatar.fail %}
        {{ notify.error(language.usercontrol_profile_photochange_fail) }}
    {% endif %}
{% endif %}
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="avatarupload"/><br/>
    <input type="submit" name="loadavatar" class="btn btn-success" value="{{ language.global_send_button }}"/>
</form>