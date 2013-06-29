{$notify}
<p class="alert alert-error">{$lang::admin_component_usercontrol_delete_notify_warm}</p>
<strong>{$lang::admin_component_usercontrol_delete_request_text}
    <code>{$target_user_login}</code>(<code>id{$target_user_id}</code>/<code>{$target_user_email}</code>) ?</strong>
<br/><br/>
<form action="" method="post">
    <input type="hidden" name="target_user_id" value="{$target_user_id}"/>
    <input type="submit" name="deleteuser" value="{$lang::admin_component_usercontrol_delete_button_success}"
           class="btn btn-danger"/> <a href="#"
                                       class="btn btn-info">{$lang::admin_component_usercontrol_delete_button_cancel}</a>
</form>