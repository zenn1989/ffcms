<?php
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '0.1');
// указатель интерфейса
define('loader', 'install');


// подключаем файл конфигураций
require_once(root . "/config.php");

// минимальный набор джентельмена ;D
require_once(root . "/engine/constant.class.php");
require_once(root . "/engine/database.class.php");
require_once(root . "/engine/language.class.php");
require_once(root . "/engine/template.class.php");
require_once(root . "/engine/system.class.php");
require_once(root . "/engine/install.class.php");

$constant = new constant();
$system = new system();
$language = new language($_COOKIE['ff_lang'] != null ? $_COOKIE['ff_lang'] : 'ru');
$template = new template();
$install = new install();

echo $install->make();

?>