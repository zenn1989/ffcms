<?php
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '0.1');

// подключаем файл конфигураций
require_once(root."/config.php");

// подключаем и инициируем все используемые классы движка
require_once(root."/load.php");

// сборка шаблона, первый вариант.
$template->init();
echo $template->compile();
$template->cleanafterprint();

?>