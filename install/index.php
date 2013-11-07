<?php
error_reporting(0);
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '1.2.1');
// указатель интерфейса
define('loader', 'install');

// минимальный набор джентельмена ;D
require_once(root . "/engine/constant.class.php");
require_once(root . "/engine/database.class.php");
require_once(root . "/engine/language.class.php");
require_once(root . "/engine/page.class.php");
require_once(root . "/engine/template.class.php");
require_once(root . "/engine/system.class.php");
require_once(root . "/engine/install.class.php");
require_once(root . "/engine/engine.class.php");

// for update reason
if(file_exists(root . "/config.php"))
    require_once(root . "/config.php");

$constant = new constant();
$system = new system();
$language = new language();
$template = new template();
$page = new page();
$install = new install();
$engine = new engine();

echo $install->make();

?>