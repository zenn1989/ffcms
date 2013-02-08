<?php
// маркер начала загруки, для отладочной информации. Через ob_start() нет желания.
$debug_starttime = microtime(true);
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '0.1');

// подключаем файл конфигураций
require_once(root."/config.php");

// подключаем и инициируем все используемые классы движка
require_once(root."/load.php");

// вывод страницы, вторая весрия :D
echo $page->printload();
$template->cleanafterprint();

// таймер окончания загрузки.
$debug_endtime = microtime(true);

// Отладочная информация
if($config['debug'])
{
	$load_time = $debug_endtime-$debug_starttime;
	echo "<hr />Debug loading: ".$load_time." sec <br />Sql query count: ".$database->totalQueryCount()."<br />Theme files readed count: ".$template->getReadCount();
}
?>