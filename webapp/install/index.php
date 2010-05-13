<?php
require_once 'model/class.Installer.php';
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