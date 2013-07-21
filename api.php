<?php
error_reporting(E_ALL ^ E_NOTICE);
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы (api.major.minor)
define('version', '1.0.0');
// указатель интерфейса
define('loader', 'api');

// подключаем файл конфигураций
require_once(root . "/config.php");

require_once(root . "/engine/constant.class.php");
require_once(root . "/engine/database.class.php");
require_once(root . "/engine/language.class.php");
require_once(root . "/engine/template.class.php");
require_once(root . "/engine/page.class.php");
require_once(root . "/engine/extension.class.php");
require_once(root . "/engine/system.class.php");
require_once(root . "/engine/api.class.php");
require_once(root . "/engine/file.class.php");
require_once(root . "/engine/user.class.php");
require_once(root . "/engine/hook.class.php");
require_once(root . "/engine/rule.class.php");

$constant = new constant();
$system = new system();
$database = new database();
$language = new language();
$template = new template();
$user = new user();
$system = new system();
$file = new file();
$page = new page();
$extension = new extension();
$hook = new hook();
$rule = new rule();

$api = new api();
echo $api->load();

?>