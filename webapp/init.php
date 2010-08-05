<?php
define('DS', DIRECTORY_SEPARATOR);

// Define absolute path to thinkup's directory
define('THINKUP_ROOT_PATH', dirname(dirname(__FILE__)) . DS);

// Define absolute path to thinkup's webapp directory
define('THINKUP_WEBAPP_PATH', dirname(__FILE__) . DS);

// Define base URL, the same as $THINKTANK_CFG['site_root_path']
$current_script_path = explode('/', $_SERVER['PHP_SELF']);
array_pop($current_script_path);
if ( in_array($current_script_path[count($current_script_path)-1], array('account', 'post', 'session', 'user')) ) {
  array_pop($current_script_path);
}
$current_script_path = implode('/', $current_script_path) . '/';
define('THINKUP_BASE_URL', $current_script_path);

require_once 'model/class.Loader.php';
Loader::register();

$config = Config::getInstance();

if ($config->getValue('time_zone')) {
    putenv($config->getValue('time_zone'));
}
if ($config->getValue('debug')) {
    ini_set("display_errors", 1);
    ini_set("error_reporting", E_ALL);
}

/* Start plugin-specific configuration handling */
$pdao = DAOFactory::getDAO('PluginDAO');
$active_plugins = $pdao->getActivePlugins();
foreach ($active_plugins as $ap) {
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/model/*.php") as $includefile) {
        require_once $includefile;
    }
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/controller/*.php") as $includefile) {
        require_once $includefile;
    }
}
