<p class="alert alert-info">{$lang::admin_component_usercontrol_ban_uid_notify_text} id{$block_user_id}</p>
<p class="alert alert-error">{$lang::admin_component_usercontrol_ban_uid_warning_text}</p>
<form method="post" action="" class="form-horizontal">
    <input type="hidden" name="blockuserid" value="{$block_user_id}"/>
    <fieldset>
        <div class="control-group">
            <label class="control-label">{$lang::admin_component_usercontrol_ban_label_endtime}</label>

            <div class="controls">
                <input type="text" class="input-large" name="enddate" placeholder="2020-10-25"/>

                <p class="help-block">{$lang::admin_component_usercontrol_ban_help_endtime}</p>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <input type="submit" name="banuserid" value="{$lang::admin_component_usercontrol_ban_button_red}"
                       class="btn btn-danger"/>
            </div>
        </div>
    </fieldset>
</form>