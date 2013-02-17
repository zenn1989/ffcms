<?php
// маркер начала загруки, для отладочной информации.
$debug_startmemory = memory_get_usage();
$debug_starttime = microtime(true);
session_start();
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
	$used_memory = memory_get_usage()-$debug_startmemory;
	echo "<hr />Debug loading: ".$load_time." sec <br />Sql query count: ".$database->totalQueryCount()."<br />Theme files readed count: ".$template->getReadCount()."<br />Memory(peak/now)mb :(".round(memory_get_peak_usage(true)/1048576,2)."/".round($used_memory/1048576,2).")";
}
?>