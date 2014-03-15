<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

abstract class singleton {
    protected final function __construct() {}
    protected final function __clone() {}
    protected final function __wakeup() {}
}