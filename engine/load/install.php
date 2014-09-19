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


\engine\template::getInstance()->twigDefaultVariables(); // set default template variables according changes in dymanic variables

\engine\install::getInstance()->make(); // prepare data
echo \engine\template::getInstance()->make(); // output main tpl