<?php

require_once(root . "/engine/constant.class.php");
require_once(root . "/engine/database.class.php");
require_once(root . "/engine/language.class.php");
require_once(root . "/engine/event.class.php");
require_once(root . "/engine/extension.class.php");
require_once(root . "/engine/page.class.php");
require_once(root . "/engine/user.class.php");
require_once(root . "/engine/template.class.php");
require_once(root . "/engine/system.class.php");
require_once(root . "/engine/cache.class.php");
require_once(root . "/engine/hook.class.php");
require_once(root . "/engine/file.class.php");
require_once(root . "/engine/mail.class.php");
require_once(root . "/engine/admin.class.php");
require_once(root . "/engine/meta.class.php");
require_once(root . "/engine/robot.class.php");
require_once(root . "/engine/rule.class.php");
require_once(root . "/engine/antivirus.class.php");
require_once(root . "/engine/backup.class.php");

$constant = new constant();
$system = new system();
$database = new database();
$event = new event();
$extension = new extension();
$page = new page();
$user = new user();
$template = new template();
$cache = new cache();
$language = new language();
$file = new file();
$hook = new hook();
$mail = new mail();
$admin = new admin();
$meta = new meta();
$robot = new robot();
$rule = new rule();
$antivirus = new antivirus();
$backup = new backup();

?>
