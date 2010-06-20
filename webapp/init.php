<?php
define('DS', DIRECTORY_SEPARATOR);
// Define absolute path to thinktank's directory
define('THINKTANK_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
// Define absolute path to thinktank's webapp directory
define('THINKTANK_WEBAPP_PATH', dirname(__FILE__) . DS);
// Define base URL, the same as $THINKTANK_CFG['site_root_path']
define('THINKTANK_BASE_URL', substr($_SERVER['PHP_SELF'], 0, strpos( $_SERVER['PHP_SELF'], basename(__FILE__))));

require_once 'model/class.Loader.php';
Loader::register();
$installer = Installer::getInstance();
if ( !file_exists( THINKTANK_WEBAPP_PATH . 'config.inc.php' ) ) {
  // if config file doesn't exist
  
  $message  = "<p>Config's file, <code>config.inc.php</code>, is not found! ";
  $message .= "No need to worry, this may happens if you're going install ThinkTank for the first time. ";
  $message .= "If you've installed ThinkTank before, you can create config file by copying or renaming ";
  $message .= "<code>config.sample.inc.php</code> to <code>config.inc.php</code>. If you want to install ";
  $message .= "ThinkTank clik on the link below to start installation.";
  $message .= '<div class="clearfix"><div class="grid_10 prefix_8 left">';
  $message .= '<div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">';
  $message .= '<a href="install/">Start Installation!</a>';
  $message .= '</div></div></div>';
  
  $installer->diePage($message, 'Error');
} else {
  // config file exists in THINKTANK_WEBAPP_PATH
  require_once 'config.inc.php';
  
  try {
    // check if $THINKTANK_CFG related to path exists
    $installer->checkPath($THINKTANK_CFG);
    
    // check if ThinkTank is installed
    if ( !$installer->isThinkTankInstalled($THINKTANK_CFG) ) {
      throw new InstallerError('', Installer::ERROR_INSTALL_NOT_COMPLETE);
    }
  } catch (InstallerError $e) {
    $e->showError();
  }
}

$config = Config::getInstance();
require_once $config->getValue('source_root_path').'extlib/twitteroauth/twitteroauth.php';

if ($config->getValue('time_zone')) {
    putenv($config->getValue('time_zone'));
}
if ($config->getValue('debug')) {
    ini_set("display_errors", 1);
    ini_set("error_reporting", E_ALL);
}

$webapp = new Webapp();
$crawler = new Crawler();

// Instantiate global database variable
//@TODO remove this when the PDO port is complete
try {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
} catch(Exception $e) {
    echo $e->getMessage();
}

/* Start plugin-specific configuration handling */
$pdao = new PluginDAO($db);
$active_plugins = $pdao->getActivePlugins();
foreach ($active_plugins as $ap) {
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/model/*.php") as $includefile) {
        require_once $includefile;
    }
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/controller/*.php") as $includefile) {
        require_once $includefile;
    }
}