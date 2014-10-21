<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

if(file_exists(root . '/config.php')) // mb its a update action?
    require_once(root . '/config.php');

\engine\property::getInstance()->init(); // processing of URI for multi-language and friendly url's
\engine\language::getInstance()->init(); // prepare language
\engine\database::getInstance()->init(); // init database PDO connect
\engine\router::getInstance()->init(); // prepare URI worker
\engine\template::getInstance()->init(); // set default template variables according changes in dymanic variables

\engine\install::getInstance()->make(); // prepare data
echo \engine\template::getInstance()->make(); // output main tpl