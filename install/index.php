<?php
error_reporting(E_ERROR);
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '1.1.0');
// указатель интерфейса
define('loader', 'install');


// подключаем файл конфигураций
require_once(root . "/config.php");

// минимальный набор джентельмена ;D
require_once(root . "/engine/constant.class.php");
require_once(root . "/engine/database.class.php");
require_once(root . "/engine/language.class.php");
require_once(root . "/engine/page.class.php");
require_once(root . "/engine/template.class.php");
require_once(root . "/engine/system.class.php");
require_once(root . "/engine/install.class.php");
require_once(root . "/engine/engine.class.php");

$constant = new constant();
$system = new system();
$language = new language();
$template = new template();
$page = new page();
$install = new install();
$engine = new engine();
$engine->load();

echo $install->make();

?>