<?php
require 'vendor/autoload.php';

namespace Codeception\Module;
use Codeception\Exception\ModuleConfig as ModuleConfigException;
user VoiceBrowser\VoiceBrowser;

// here you can define custom functions for VoiceGuy 

class VoiceHelper extends \Codeception\Module
{  
    protected $config = array(
        'url' => '',
        'timeout' => 30,
        'xdebug_remote' => false
    );

    public $client = null;
    public $headers = array();
    public $params = array();
    public $response = "";

    public function _before(\Codeception\TestCase $test)
    {
        if (!$this->client) {
            if (!strpos($this->config['url'], '://')) {
                // not valid url
                foreach ($this->getModules() as $module) {
                    if ($module instanceof \Codeception\Util\Framework) {
                        $this->client = $module->client;
                        $this->is_functional = true;
                        break;
                    }
                }
            } else {
                if (!$this->hasModule('PhpBrowser')) {
                    throw new ModuleConfigException(__CLASS__, "For REST testing via HTTP please enable PhpBrowser module");
                }
                $this->client = $this->getModule('PhpBrowser')->session->getDriver()->getClient();
            }
            if (!$this->client) {
                throw new ModuleConfigException(__CLASS__, "Client for VoiceXML requests not initialized.\nProvide either PhpBrowser module, or a framework module which shares FrameworkInterface");
            }
        }
    }

    public function call($url, $params = array()) {
      $this->assertTrue(VoiceBrowser::fetch($url, "GET", $params, $this->client));
  
    }

    public function hearText($text) {
    }

    public function dontHearText($text) {
    }

    protected function proceedHearText($text) {

    }

    public function hearAudio($filename) {
    }

    public function dontHearAudio($filename) {
    }

    protected function proceedHearAudio($filename) {
      // return array('Equals',md5_file($filename),md5_file($playing));
    }


    public function hearChoice($text, $dtmf) {
    }

    public function dontHearChoice($text, $dtmf) {
    }

    protected function proceedHearChoice($text, $dtmf) {
    }

    public function getPromptedToSpeak() {
    }

    public function dontGetPromptedToSpeak() {
    }

    protected function proceedGetPromptedToSpeak() {
    }

    public function pressKey($key) {
      $this->pressKeySequence(array($key));
    }

    public function pressKeySequence($keys) {
    }

    public function pressNoKey() {
    }

    public function say($text, $duration) {
    }

    public function keepSilent() {
    }

    public function hangUp() {
    }

    public function getDisconnected() {
    }

    public function getTransferedToOperator() {
    }
}
