<?php
define('DS', DIRECTORY_SEPARATOR);
// Define absolute path to thinktank's directory
define('THINKTANK_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
// Define absolute path to thinktank's webapp directory
define('THINKTANK_WEBAPP_PATH', dirname(__FILE__) . DS);
// Define base URL, the same as $THINKTANK_CFG['site_root_path']
define('THINKTANK_BASE_URL', substr($_SERVER['PHP_SELF'], 0, strpos( $_SERVER['PHP_SELF'], basename(__FILE__))));

require_once 'model/class.Installer.php';
$installer = Installer::getInstance();
if ( !file_exists( THINKTANK_WEBAPP_PATH . 'config.inc.php' ) ) {
  // if config file doesn't exist
  
  $message  = "<p>Config's file, <code>config.inc.php</code>, is not found! ";
  $message .= "No need to worry, this may happens if you're going install ThinkTank for the first time. ";
  $message .= "Clik on the link below to start installation.";
  $message .= '<div id="create-config-file" class="tt-button ui-state-default ui-priority-secondary ui-corner-all">';
  $message .= '<a href="install/">Start Installation!</a>';
  $message .= '</div>';
  
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

require_once 'model/class.Config.php';
require_once 'model/class.Database.php';
require_once 'model/class.MySQLDAO.php';
require_once 'model/class.User.php';
require_once 'model/class.Owner.php';
require_once 'model/class.Post.php';
require_once 'model/class.Link.php';
require_once 'model/class.Instance.php';
require_once 'model/class.OwnerInstance.php';
require_once 'model/class.PluginHook.php';
require_once 'model/class.Crawler.php';
require_once 'model/class.Utils.php';
require_once 'model/class.Captcha.php';
require_once 'model/class.Session.php';
require_once 'model/class.Plugin.php';
require_once 'model/class.LoggerSlowSQL.php';
require_once 'model/interface.iPlugin.php';

# crawler only
require_once 'model/class.Logger.php';

# webapp only
require_once 'model/class.Follow.php';
require_once 'model/class.Webapp.php';


require_once 'config.inc.php';


require_once ($THINKTANK_CFG['smarty_path'].'Smarty.class.php');
require_once 'model/class.SmartyThinkTank.php';
require_once $THINKTANK_CFG['source_root_path'].'extlib/twitteroauth/twitteroauth.php';

$webapp = new Webapp();
$crawler = new Crawler();

// Instantiate global database variable
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
    foreach (glob($THINKTANK_CFG['source_root_path'].'webapp/plugins/'.$ap->folder_name."/model/*.php") as $includefile) {
        require_once ($includefile);
    }
    foreach (glob($THINKTANK_CFG['source_root_path'].'webapp/plugins/'.$ap->folder_name."/controller/*.php") as $includefile) {
        require_once ($includefile);
    }
}
?>
