<?php
// маркер начала загруки, для отладочной информации.
$debug_starttime = microtime(true);
session_start();
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы
define('version', '0.1');
// указатель интерфейса
define('loader', 'front');

// подключаем файл конфигураций
require_once(root."/config.php");

// подключаем и инициируем все используемые классы движка
require_once(root."/load.php");
// выставляем стандарт времени
date_default_timezone_set($constant->time_zone);

// сборщик статистики бдит
$robot->collect();

// вывод страницы, вторая весрия :D
$page->doload();
echo $template->compile();
$template->cleanafterprint();
// таймер окончания загрузки.
$debug_endtime = microtime(true);

// Отладочная информация
if($config['debug'])
{
	$load_time = $debug_endtime-$debug_starttime;
	echo "<hr />Debug loading: ".$load_time." sec <br />Sql query count: ".$database->totalQueryCount()."<br />Theme files readed count: ".$template->getReadCount()."<br />Memory(peak) :".round(memory_get_peak_usage(true)/1048576,2)."mb";
}
?>