<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
require_once $SOURCE_ROOT_PATH.'tests/TestOfPublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPrivateDashboardController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPostController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestAuthController.php';

$controllertest = & new GroupTest('Controller tests');
$controllertest->addTestCase(new TestOfPublicTimelineController());
$controllertest->addTestCase(new TestOfPrivateDashboardController());
$controllertest->addTestCase(new TestOfPostController());
$controllertest->addTestCase(new TestOfTestController());
$controllertest->addTestCase(new TestOfTestAuthController());
$controllertest->run( new TextReporter());
