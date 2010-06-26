<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;

/* PLUGIN TESTS */
require_once $SOURCE_ROOT_PATH.'webapp/plugins/expandurls/tests/TestOfExpandURLsPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookCrawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfRetweetDetector.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterAPIAccessorOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterCrawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterPluginConfigurationController.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookPluginConfigurationController.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/TestOfFlickrAPIAccessor.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/TestOfFlickrThumbnailsPlugin.php';

$plugin_tests = & new GroupTest('Plugin tests');
$plugin_tests->addTestCase(new TestOfExpandURLsPlugin());
$plugin_tests->addTestCase(new TestOfFacebookCrawler());
$plugin_tests->addTestCase(new TestOfFacebookPlugin());
$plugin_tests->addTestCase(new TestOfFacebookPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfFlickrAPIAccessor());
$plugin_tests->addTestCase(new TestOfFlickrThumbnailsPlugin());
$plugin_tests->addTestCase(new TestOfTwitterAPIAccessorOAuth());
$plugin_tests->addTestCase(new TestOfTwitterCrawler());
$plugin_tests->addTestCase(new TestOfTwitterOAuth());
$plugin_tests->addTestCase(new TestOfTwitterPlugin());
$plugin_tests->addTestCase(new TestOfTwitterPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfRetweetDetector());

$plugin_tests->run( new TextReporter());
