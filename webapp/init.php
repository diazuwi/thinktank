<?php
define('DS', DIRECTORY_SEPARATOR);

// Define absolute path to thinktank's directory
define('THINKTANK_ROOT_PATH', dirname(dirname(__FILE__)) . DS);

// Define absolute path to thinktank's webapp directory
define('THINKTANK_WEBAPP_PATH', dirname(__FILE__) . DS);

// Define base URL, the same as $THINKTANK_CFG['site_root_path']
$current_script_path = explode('/', $_SERVER['PHP_SELF']);
array_pop($current_script_path);
if ( in_array($current_script_path[count($current_script_path)-1], array('account', 'post', 'session', 'user')) ) {
  array_pop($current_script_path);
}
$current_script_path = implode('/', $current_script_path);
if ( empty($current_script_path) ) {
  $current_script_path = '/';
}
define('THINKTANK_BASE_URL', $current_script_path);

require_once 'model/class.Loader.php';
Loader::register();

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