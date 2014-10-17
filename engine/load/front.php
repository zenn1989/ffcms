<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

// system are not installed or file is missed
if(!file_exists("config.php")) {
    exit("System are not installed or file config.php is missed. Run <a href='./install/'>Installer</a>.");
} else {
    require_once(root . '/config.php');
}

// default timezone from configs
date_default_timezone_set(\engine\property::getInstance()->get('time_zone'));

\engine\property::getInstance()->init(); // processing of URI for multi-language and friendly url's
\engine\language::getInstance()->init(); // prepare language
\engine\user::getInstance()->init(); // prepare user data
\engine\router::getInstance()->init(); // prepare URI worker

\engine\meta::getInstance()->init();
\engine\template::getInstance()->init(); // set default template variables according changes in dymanic variables

\engine\extension::getInstance()->loadModules(); // prepare modules
\engine\extension::getInstance()->loadHooks(); // prepare hooks

// statistic collector
\engine\robot::getInstance()->init();

// check ip/user is fully blocked?
\engine\ban::getInstance()->init();

\engine\router::getInstance()->makeRoute();
\engine\meta::getInstance()->compile();
\engine\maintenance::getInstance()->make();
echo \engine\template::getInstance()->make();
// load debug indifferent of templates. Sounds not good but cant be removed from theme.
if(\engine\permission::getInstance()->have('global/owner') && \engine\property::getInstance()->get('debug')) {
    $debug_endtime = microtime(true);
    $load_time = number_format($debug_endtime - $debug_starttime, 3);
    echo "<hr />Debug loading: " .
        $load_time . " sec <br />Sql query count: " .
        \engine\database::getInstance()->getQueryCount() . "<br />Memory(peak): " .
        number_format(memory_get_peak_usage() / (1024 * 1024), 3) . "mb";
}