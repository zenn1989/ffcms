<?php
error_reporting(E_ALL ^ E_NOTICE);
// маркер начала загруки, для отладочной информации.
$debug_starttime = microtime(true);
session_start();
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы (api.major.minor)
define('version', '1.0.0');
// указатель интерфейса
define('loader', 'front');

// подключаем и инициируем все используемые классы движка
require_once(root . "/load.php");
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
if ($config['debug'] && $user->get('access_to_admin') > 0) {
    $load_time = round($debug_endtime - $debug_starttime, 3);
    echo "<hr />Debug loading: " . $load_time . " sec <br />Sql query count: " . $database->totalQueryCount() . "<br />Theme files readed count: " . $template->getReadCount() . "<br />Memory(peak): " . round(memory_get_peak_usage() / (1024 * 1024), 3) . "mb";
}
?>