<?php

// Среда работы.
// work - рабочий проект
// dev - отладка и разработка
define('env', 'work');
// маркер начала загруки, для отладочной информации.
$debug_starttime = microtime(true);
session_start();
// задаем глобальную корня
define('root', $_SERVER['DOCUMENT_ROOT']);
// версия системы (api.major.minor)
define('version', '1.2.0');
// указатель интерфейса
define('loader', 'front');

// подключаем и инициируем все используемые классы движка
require_once(root . "/load.php");
require_once(root . "/engine/engine.class.php");
$engine = new engine();
// выставляем стандарт времени
date_default_timezone_set($engine->constant->time_zone);

// сборщик статистики бдит
$engine->robot->collect();

// вывод страницы, вторая весрия :D
$engine->page->doload();
echo $engine->template->compile();
$engine->template->cleanafterprint();
// таймер окончания загрузки.
$debug_endtime = microtime(true);

// Отладочная информация
if ($config['debug'] && $engine->user->get('access_to_admin') > 0) {
    $load_time = round($debug_endtime - $debug_starttime, 3);
    echo "<hr />Debug loading: " . $load_time . " sec <br />Sql query count: " . $engine->database->totalQueryCount() . "<br />Theme files readed count: " . $engine->template->getReadCount() . "<br />Memory(peak): " . round(memory_get_peak_usage() / (1024 * 1024), 3) . "mb";
}
?>