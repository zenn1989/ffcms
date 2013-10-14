<?php
error_reporting(E_ERROR);
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы (api.major.minor)
define('version', '1.1.0');
// указатель на админ-интерфейс
define('loader', 'back');
// указатель позволяющий изменить имя скрипта
define('file_name', $_SERVER['SCRIPT_NAME']);
// подключаем файл конфигураций
require_once(root . "/config.php");

// подключаем и инициируем все используемые классы движка
require_once(root . "/load.php");
date_default_timezone_set($engine->constant->time_zone);
// загрузка интерфейса админ панели
$engine->admin->doload();
echo $engine->template->compile();
$engine->template->cleanafterprint();

?>