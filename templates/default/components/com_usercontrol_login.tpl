<h3 style="text-align: center;">{$lang::usercontrol_auth_header}</h3>
<ul class="nav nav-tabs">
  <li class="active">
    <a href="{$url}/login">{$lang::usercontrol_auth_header}</a>
  </li>
  <li><a href="{$url}/register">{$lang::usercontrol_reg_header}</a></li>
  <li><a href="{$url}/recovery">{$lang::usercontrol_recovery_header}</a></li>
</ul>
<form class="form-horizontal">
  <div class="control-group">
    <label class="control-label" for="inputEmail">Почта</label>
    <div class="controls">
      <input name="email" type="text" placeholder="ivan.petrov@gmail.com">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputPassword">Пароль</label>
    <div class="controls">
      <input type="password" placeholder="Password">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputPassword">АнтиРобот</label>
    <div class="controls">
	  <img src="{$captcha}" id="captcha" /><a href="#" onclick="document.getElementById('captcha').src='{$captcha}?'+Math.random();"><i class="icon-refresh"></i></a><br />
      <input type="text" name="captcha">
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <button type="submit" name="submit" class="btn btn-inverse">Войти</button>
    </div>
  </div>
</form>