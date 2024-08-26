<?php

use PHPUnit\Framework\TestCase;
use KrisRo\PhpConfig\Config;

class ConfigTest extends TestCase {
  
  public function testStaticallyLoadAllConfigFilesInPath() {
    Config::buildConfig(dirname(dirname(__FILE__)) . '/configs');
    
    // magic call for debug entry in the config array
    $this->assertEquals(FALSE, Config::debug());
    $this->assertEquals('overwritten', Config::salt());
    
    // getter call
    $this->assertEquals('k', Config::get('database/username'));
    $this->assertEquals(10, Config::get('pagination/size'));
  }
  
  public function testLoadAllConfigFilesInPath() {
    new Config(dirname(dirname(__FILE__)) . '/configs');
    
    // magic call for debug entry in the config array
    $this->assertEquals(FALSE, Config::debug());
    $this->assertEquals('overwritten', Config::salt());
    
    // getter call
    $this->assertEquals('k', Config::get('database/username'));
    $this->assertEquals(10, Config::get('pagination/size'));
  }

  public function testLoadConfigFilesInOrder() {
    $configPath = dirname(dirname(__FILE__)) . '/configs';
    
    (new Config())
      ->loadConfigFile($configPath . '/config-3.json')
      ->loadConfigFile($configPath . '/config-2.json')
      ->loadConfigFile($configPath . '/config-1.json');
    
    $this->assertEquals('123456', Config::salt());
  }
}