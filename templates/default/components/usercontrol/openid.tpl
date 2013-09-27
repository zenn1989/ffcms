<h3 style="text-align: center;">{$lang::usercontrol_auth_header}</h3>
<ul class="nav nav-tabs">
    <li><a href="{$url}/login">{$lang::usercontrol_auth_header}</a></li>
    <li><a href="{$url}/register">{$lang::usercontrol_reg_header}</a></li>
    <li><a href="{$url}/recovery">{$lang::usercontrol_recovery_header}</a></li>
    <li class="active"><a href="#">OpenID</a></li>
</ul>
{$notify}
<p>{$lang::usercontrol_openid_reg_notify}</p>
<form class="form-horizontal" method="post" action="">
    <input type="hidden" name="openid_token" value="{$openid_session}" />
    <div class="control-group">
        <label class="control-label">{$lang::usercontrol_auth_email}</label>

        <div class="controls">
            <input name="email" type="text" value="{$openid_email}" required>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::usercontrol_auth_login}</label>

        <div class="controls">
            <input name="login" type="text" value="{$openid_login}" autocomplete="off" required>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{$lang::usercontrol_auth_pseudoname}</label>

        <div class="controls">
            <input name="nick" type="text" value="{$openid_nick}" autocomplete="off" required>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <input type="submit" name="submit" class="btn btn-inverse" value="{$lang::usercontrol_reg_button}"/>
        </div>
    </div>
</form>
