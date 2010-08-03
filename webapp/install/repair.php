<?php
require_once '../model/class.Loader.php';
Loader::register();
define(
    'THINKUP_BASE_URL', 
    substr($_SERVER['PHP_SELF'], 0, strpos( $_SERVER['PHP_SELF'], basename(dirname(__FILE__))))
);

$controller = new InstallerController();
echo $controller->go();
