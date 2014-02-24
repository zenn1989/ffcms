<?php

define('version', '2.0.0');

switch(loader) {
    case 'front':
        require_once(root . '/engine/load/front.php');
        break;
    case 'back':
        require_once(root . '/engine/load/back.php');
        break;
    case 'api':
        require_once(root . '/engine/load/api.php');
        break;
    case 'install':
        require_once(root . '/engine/load/install.php');
        break;
}