{% include 'components/user/profile/profile_settings_menu.tpl' %}
<form method="post" action="" class="form-horizontal">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <fieldset>
        <h3>{{ language.usercontrol_profile_settings_status_title }}</h3>

        <p>{{ language.usercontrol_profile_settings_status_desc }}</p>
        <hr/>
        <input type="text" class="form-control" name="newstatus" value="{{ local.status }}"/><br/>
        <input type="submit" name="updatestatus" class="btn btn-success pull-right" value="{{ language.global_send_button }}"/>
    </fieldset>
</form>