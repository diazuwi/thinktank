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

if ( file_exists( THINKTANK_WEBAPP_PATH . 'config.inc.php' ) ) {
  // config file exists in THINKTANK_WEBAPP_PATH
  require_once THINKTANK_WEBAPP_PATH . 'config.inc.php';
  
  try {
    // check if ThinkTank is installed
    if ( $installer->isThinkTankInstalled($THINKTANK_CFG) && $installer->checkPath($THINKTANK_CFG) ) {
      throw new InstallerError('', Installer::ERROR_INSTALL_COMPLETE);
    }
  } catch (InstallerError $e) {
    $e->showError();
  }
}
// clear error messages after called isThinkTankInstalled successfully
$installer->clearErrorMessages();
$step = 1;
if ( isset($_GET['step']) ) {
  $step = (int) $_GET['step'];
}
$installer->installPage($step);
?>