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
    if(loader !== 'install')
        exit("System are not installed or file config.php is missed. Run <a href='/install/'>Installer</a>.");
} else {
    require_once(root . '/config.php');
}

// default timezone from configs
date_default_timezone_set(\engine\property::getInstance()->get('time_zone'));

\engine\router::getInstance()->prepareRoutes(); // prepare URI worker
\engine\property::getInstance()->dymanicPrepares(); // processing of URI for multi-language and friendly url's
\engine\template::getInstance()->twigDefaultVariables(); // set default template variables according changes in dymanic variables
\engine\template::getInstance()->twigUserVariables(); // user default variables init

// statistic collector
\engine\robot::getInstance();

// check ip/user is fully blocked?
\engine\ban::getInstance()->check();

\engine\language::getInstance();
\engine\user::getInstance();
\engine\extension::getInstance()->loadModules();
\engine\extension::getInstance()->loadHooks();
\engine\router::getInstance()->makeRoute();
\engine\meta::getInstance()->compile();
\engine\maintenance::getInstance()->make();
echo \engine\template::getInstance()->make();
// independ debug from templates. Sounds not good but cant be removed from theme.
if(\engine\permission::getInstance()->have('global/owner') && \engine\property::getInstance()->get('debug')) {
    $debug_endtime = microtime(true);
    $load_time = round($debug_endtime - $debug_starttime, 3);
    echo "<hr />Debug loading: " .
        $load_time . " sec <br />Sql query count: " .
        \engine\database::getInstance()->getQueryCount() . "<br />Memory(peak): " .
        round(memory_get_peak_usage() / (1024 * 1024), 3) . "mb";
}