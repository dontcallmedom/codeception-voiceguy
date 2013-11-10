<?php
use Codeception\Util\Stub as Stub;
class VoiceHelperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \SBC4D\VoiceHelper
     */
    protected $module;

    public function setUp() {
        $this->module = new \Codeception\Module\VoiceHelper();
        $connector = new \Codeception\Util\Connector\Universal();
        $connector->setIndex(\Codeception\Configuration::dataDir().'/test.vxml');
        $this->module->client = $connector;
        $this->module->_before(Stub::makeEmpty('\Codeception\TestCase\Cest'));
        $this->module->client->setServerParameters(array(
            'SCRIPT_FILENAME' => '',
            'SCRIPT_NAME' => 'index',
            'SERVER_NAME' => 'localhost',
            'SERVER_PROTOCOL' => 'http'
        ));
    }

    public function testCall() {
      $this->module->call('http://localhost:1349/test.vxml', 'GET');
    }

    public function testHearText() {
      $this->testCall();
      $this->module->hearText("Please tell us who you are");
    }

    public function testSay() {
      $this->module->call('http://localhost:1349/test.vxml', 'GET');
      //$this->testCall();
      $this->module->say("tests/data/test.wav", 5000);
    }

    /*    public function testKeepSilent() {
      $this->testCall();
      $this->module->keepSilent();
      }*/
}