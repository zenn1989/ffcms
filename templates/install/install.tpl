{$notify}
<strong>Подключение к базе данных</strong>
<form class="form-horizontal" method="post" action="">
    <div class="control-group">
        <label class="control-label">Адрес(host)</label>
        <div class="controls">
            <input type="text" name="config:db_host" value="{$config_db_host}" placeholder="localhost">
            <p class="help-block">Адрес базы данных mysql(хост) - в большинстве случаев localhost/127.0.0.1:3306</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Пользователь(user)</label>
        <div class="controls">
            <input type="text" placeholder="root" name="config:db_user" value="{$config_db_user}">
            <p class="help-block">Имя пользователя(username) для подключения к базе данных(пример: root)</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Пароль(pass)</label>
        <div class="controls">
            <input type="password" placeholder="Password" name="config:db_pass" value="{$config_db_pass}">
            <p class="help-block">Пароль пользователя для подключения к базе данных(пример: myhardpassword123)</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Имя базы(db name)</label>
        <div class="controls">
            <input type="text" placeholder="ffcms" name="config:db_name" value="{$config_db_name}">
            <p class="help-block">Имя базы данных в которой расположены данные (пример: ffcms)</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Префикс таблиц</label>
        <div class="controls">
            <input type="text" placeholder="ffcms" name="config:db_prefix" value="{$config_db_prefix}">
            <p class="help-block">Префикс для таблиц в базе данных(пример: ffcms)</p>
        </div>
    </div>
<strong>Основные параметры сайта</strong>
    <div class="control-group">
        <label class="control-label">URL-сайта</label>
        <div class="controls">
            <input type="text" placeholder="http://ffcms.ru" name="config:url" value="{$config_url}">
            <p class="help-block">URL адрес вашего сайта(пример: http://ffcms.ru) без слеша на конце</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Заголовок</label>
        <div class="controls">
            <input type="text" placeholder="Dart Vader blog" name="config:seo_title" value="{$config_seo_title}">
            <p class="help-block">Основной заголовок вашего сайта (параметр meta title)</p>
        </div>
    </div>
    <p class="alert alert-info">Более подробную конфигурацию системы вы можете выполнить после установки системы в панели администратора, мы настоятельно рекомендуем вам это выполнить!</p>
<strong>Пользователь-администратор</strong>
    <div class="control-group">
        <label class="control-label">Логин</label>
        <div class="controls">
            <input type="text" placeholder="admin" name="admin:login" value="{$admin_login}">
            <p class="help-block">Ваш логин для пользователя-администратора сайта</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Почта</label>
        <div class="controls">
            <input type="text" placeholder="admin@example.com" name="admin:email" value="{$admin_email}">
            <p class="help-block">Ваша почта для пользователя-администратора</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Пароль</label>
        <div class="controls">
            <input type="text" placeholder="StrOnGPas1s4ord" name="admin:pass">
            <p class="help-block">Ваш пароль для пользователя-администратора</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Повторение пароля</label>
        <div class="controls">
            <input type="text" placeholder="StrOnGPas1s4ord" name="admin:repass">
            <p class="help-block">Повторите ваш пароль еще раз</p>
        </div>
    </div>
    <input type="submit" name="submit" value="Начать установку" class="btn btn-success" />
</form>