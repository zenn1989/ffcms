<?php
error_reporting(E_ALL ^ E_NOTICE);
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '0.1');
// указатель на админ-интерфейс
define('loader', 'back');
// указатель позволяющий изменить имя скрипта
define('file_name', $_SERVER['SCRIPT_NAME']);
// подключаем файл конфигураций
require_once(root . "/config.php");

// подключаем и инициируем все используемые классы движка
require_once(root . "/load.php");
date_default_timezone_set($constant->time_zone);
// загрузка интерфейса админ панели
$admin->doload();
echo $template->compile();
$template->cleanafterprint();

?>