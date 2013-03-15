<?php

require_once(root."/engine/constant.class.php");
require_once(root."/engine/database.class.php");
require_once(root."/engine/language.class.php");
require_once(root."/engine/extension.class.php");
require_once(root."/engine/page.class.php");
require_once(root."/engine/template.class.php");
require_once(root."/engine/user.class.php");
require_once(root."/engine/system.class.php");
require_once(root."/engine/cache.class.php");
require_once(root."/engine/hook.class.php");
require_once(root."/engine/mail.class.php");
require_once(root."/engine/admin.class.php");
require_once(root."/engine/meta.class.php");
require_once(root."/engine/robot.class.php");

$constant = new constant;
$system = new system;
$database = new database;
$extension = new extension;
$page = new page;
$template = new template;
$user = new user;
$cache = new cache;
$language = new language;
$hook = new hook;
$mail = new mail;
$admin = new admin;
$meta = new meta;
$robot = new robot;

?>
