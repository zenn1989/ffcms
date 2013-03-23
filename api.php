<?php

// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '0.1');
// указатель интерфейса
define('isadmin', false);

// подключаем файл конфигураций
require_once(root."/config.php");

require_once(root."/engine/constant.class.php");
require_once(root."/engine/database.class.php");
require_once(root."/engine/language.class.php");
require_once(root."/engine/system.class.php");
require_once(root."/engine/api.class.php");

$constant = new constant;
$system = new system;
$database = new database;
$system = new system;

$api = new api;
// если необходима работа с правами доступа и проверкой пользовательских данных
if($system->get('u') == 1)
{
	require_once(root."/engine/user.class.php");
	$user = new user;
	echo $api->userinterface();
}
else
{
	echo $api->standalone();
}


?>