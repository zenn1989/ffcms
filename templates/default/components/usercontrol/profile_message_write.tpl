<h3>{$lang::usercontrol_profile_pm_write_header}</h3>
<hr/>
<form action="" method="post">
    <div class="input-prepend">
        <span class="add-on">{$lang::usercontrol_profile_pm_write_touser}:</span>
        <select name="accepterid">
            {$option_names}
        </select>
    </div>
    <strong>{$lang::usercontrol_profile_pm_write_text}</strong><br/>
    <textarea class="input-block-level" name="message" rows="10"></textarea>

    <div class="pull-right"><input type="submit" name="sendmessage" value="{$lang::global_send_button}" class="btn btn-success"/></div>
</form>