<?php
define('DS', DIRECTORY_SEPARATOR);
// Define absolute path to thinktank's directory
define('THINKTANK_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DS);
// Define absolute path to thinktank's webapp directory
define('THINKTANK_WEBAPP_PATH', dirname(dirname(__FILE__)) . DS);
// Define base URL, the same as $THINKTANK_CFG['site_root_path']
define('THINKTANK_BASE_URL', substr($_SERVER['PHP_SELF'], 0, strpos( $_SERVER['PHP_SELF'], basename(dirname(__FILE__)))));

require_once '../model/class.Installer.php';
$installer = Installer::getInstance();
$step = (int) $_GET['step'];
if (!$step) {
  $step = 1;
}

switch ($step) {
  case 1:
    break;
  case 2:
    break;
  case 3:
    break;
  case 4:
    break;
}

$installer->page($step);
?>