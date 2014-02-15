<?php
$debug_starttime = microtime(true);
define('root', dirname(__FILE__));
define('loader', 'front');

require_once(root . "/load.php");

// statistic collector
\engine\robot::getInstance();

// check ip/user is fully blocked?
\engine\ban::getInstance()->check();

\engine\language::getInstance();
\engine\user::getInstance();
\engine\extension::getInstance()->loadModules();
\engine\router::getInstance()->makeRoute();
\engine\meta::getInstance()->compile();
echo \engine\template::getInstance()->make();
if(\engine\permission::getInstance()->have('global/owner') && \engine\property::getInstance()->get('debug')) {
    $debug_endtime = microtime(true);
    $load_time = round($debug_endtime - $debug_starttime, 3);
    echo "<hr />Debug loading: " .
        $load_time . " sec <br />Sql query count: " .
        \engine\database::getInstance()->getQueryCount() . "<br />Memory(peak): " .
        round(memory_get_peak_usage() / (1024 * 1024), 3) . "mb";
}