<h3 style="text-align: center;">{$lang::usercontrol_recovery_header}</h3>
<ul class="nav nav-tabs">
    <li><a href="{$url}/login">{$lang::usercontrol_auth_header}</a></li>
    <li><a href="{$url}/register">{$lang::usercontrol_reg_header}</a></li>
    <li class="active"><a href="{$url}/recovery">{$lang::usercontrol_recovery_header}</a></li>
</ul>
{$notify}
<form class="form-horizontal" method="post" action="">
    <div class="control-group">
        <label class="control-label" for="inputEmail">{$lang::usercontrol_auth_email}</label>

        <div class="controls">
            <input name="email" type="text" placeholder="ivan.petrov@gmail.com">
        </div>
    </div>
    {$if !com.usercontrol.captcha_full}
    <div class="control-group">
        <label class="control-label" for="inputPassword">{$lang::usercontrol_auth_captcha}</label>

        <div class="controls">
            <img src="{$captcha}" id="captcha"/><a href="#" onclick="document.getElementById('captcha').src='{$captcha}?'+Math.random();"><i class="icon-refresh"></i></a><br/>
            <input type="text" name="captcha" required>
        </div>
    </div>
    {$/if}
    {$if com.usercontrol.captcha_full}
    <script>
        var RecaptchaOptions = { theme : 'white' };
    </script>
    <div class="control-group">
        <label class="control-label" for="inputPassword">{$lang::usercontrol_auth_captcha}</label>

        <div class="controls">
            {$captcha}
        </div>
    </div>
    {$/if}
    <div class="control-group">
        <div class="controls">
            <input type="submit" name="submit" class="btn btn-inverse" value="{$lang::usercontrol_recovery_button}"/>
        </div>
    </div>
</form>