<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.TestFauxHookableApp.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/interface.TestAppPlugin.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.TestFauxPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/hellothinktank/model/class.HelloThinkTankPlugin.php';

/**
 * Test Crawler object
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfCrawler extends ThinkTankBasicUnitTestCase {

    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('Crawler class test');
    }

    /**
     * Set up test
     */
    function setUp() {
        parent::setUp();
    }

    /**
     * Tear down test
     */
    function tearDown() {
        parent::tearDown();
    }

    /**
     * Test Crawler singleton instantiation
     */
    public function testCrawlerSingleton() {
        $crawler = Crawler::getInstance();
        $this->assertTrue(isset($crawler));
        //clean copy of crawler, no registered plugins, will throw exception
        $this->expectException( new Exception("No plugin object defined for: hellothinktank") );
        $this->assertEqual($crawler->getPluginObject("hellothinktank"), "HelloThinkTankPlugin");
        //register a plugin
        $crawler->registerPlugin('hellothinktank', 'HelloThinkTankPlugin');
        $this->assertEqual($crawler->getPluginObject("hellothinktank"), "HelloThinkTankPlugin");

        //make sure singleton still has those values
        $crawler_two = Crawler::getInstance();
        $this->assertEqual($crawler->getPluginObject("hellothinktank"), "HelloThinkTankPlugin");
    }

    /**
     * Test Crawler->crawl
     */
    public function testCrawl() {
        $crawler = Crawler::getInstance();

        $crawler->registerPlugin('nonexistent', 'TestFauxPluginOne');
        $crawler->registerCrawlerPlugin('TestFauxPluginOne');
        $this->expectException( new Exception("The TestFauxPluginOne object does not have a crawl method.") );
        $crawler->crawl();

        $crawler->registerPlugin('hellothinktank', 'HelloThinkTankPlugin');
        $crawler->registerCrawlerPlugin('HelloThinkTankPlugin');
        $this->assertEqual($crawler->getPluginObject("hellothinktank"), "HelloThinkTankPlugin");
        $crawler->crawl();

    }
}
