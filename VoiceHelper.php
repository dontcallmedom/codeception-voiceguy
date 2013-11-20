<?php
namespace Codeception\Module;
use Codeception\Exception\ModuleConfig as ModuleConfigException;
use VoiceBrowser\VoiceBrowser, VoiceBrowser\VoiceXMLAudioRecord;

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
    protected $loaded = false;
    protected $readGenerator;

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

    public function _after() {
    }

    public function amCallingFrom($callerid) {
      VoiceBrowser::setCallerId($callerid);
    }

    public function call($url, $params = array()) {
      $this->loaded = VoiceBrowser::fetch($url, "GET", $params, $this->guzzle);
      $this->assertTrue($this->loaded);
      $this->readGenerator = VoiceBrowser::play();
    }

    public function hearText($text) {
      $this->assert($this->proceedHearText($text));
    }

    public function dontHearText($text) {
      $this->assertNot($this->proceedHearText($text));
    }

    protected function proceedHearText($text) {
      while (TRUE) {
	if (!$this->readGenerator->valid()) {
	  break;
	}
	$output = $this->readGenerator->current();
	if (is_object($output) && property_exists($output,"texts") && count($output->texts) > 0) {
	  foreach ($output->texts as $t) {
	    if ($text === trim($t)) {
	      return array('Equals', $text, trim($t));
	    }
	  }
	}
	$output = $this->readGenerator->next();
      }
      $this->fail("didn't hear “".$text."”");
    }

    public function hearAudio($filename) {
      $this->assert($this->proceedHearAudio($filename));
    }

    public function dontHearAudio($filename) {
      $this->assertNot($this->proceedHearAudio($filename));
    }

    protected function proceedHearAudio($filename) {
      $md5 = md5_file($filename);
      while (TRUE) {
	if (!$this->readGenerator->valid()) {
	  break;
	}
	$output = $this->readGenerator->current();
	if (is_object($output) && property_exists($output,"audios") && count($output->audios) > 0) {
	  foreach ($output->audios as $a) {
	    if ($md5 === md5_file($a)) {
	      return array('Equals', $md5, md5_file($a));
	    }
	  }
	}
	$this->readGenerator->next();
      }
      return array('True', false);
    }


    public function hearChoice($text, $dtmf) {
    }

    public function dontHearChoice($text, $dtmf) {
    }

    protected function proceedHearChoice($text, $dtmf) {
    }

    public function getPromptedToSpeak() {
      $this->proceedRespondAudio(false);
    }

    public function dontGetPromptedToSpeak() {
      $this->assertNot($this->proceedRespondAudio(false));
    }

    protected function proceedGetPromptedToSpeak($respond = false, $audiofilename=null, $duration=null) {
      while (TRUE) {
	if (!$this->readGenerator->valid()) {
	  break;
	}
	$output = $this->readGenerator->current();     
	if ($output === "record") {
	  if ($respond) {
	    if ($audiofilename !== null) {
	      $output = $this->readGenerator->send(new VoiceXMLAudioRecord($audiofilename, $duration));
	    } else {
	      $output = $this->readGenerator->send(null);
	    }
	  }
	  return $this->assertTrue(true);
	}
	$this->readGenerator->send(null);
      }
      return $this->fail("never got to chance to talk");
    }

    public function pressKey($key) {
      $this->pressKeySequence(array($key));
    }

    public function pressKeySequence($keys) {
    }

    public function pressNoKey() {
    }

    public function say($audiofilename, $duration) {
      $this->proceedRespondAudio($audiofilename, $duration);
    }

    public function keepSilent() {
      $this->proceedRespondAudio();
    }

    public function proceedRespondAudio($audiofilename=null, $duration=null) {
      return $this->proceedGetPromptedToSpeak(true, $audiofilename, $duration);
    }

    public function hangUp() {
    }

    public function getDisconnected() {
    }

    public function getTransferedToOperator() {
    }
}
