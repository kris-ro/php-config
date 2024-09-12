<?php

namespace KrisRo\PhpConfig;

/**
 * Use this class to load application configuration from JSON files.
 */

class Config {
  
  protected static $config = [];

  /**
   * If <code>$configPath</code> is provided, every JSON file is loaded
   *  
   * @param string|null $configPath
   */
  public function __construct(?string $configPath = NULL) {
    self::buildConfig($configPath);
  }
  
  /**
   * Short hand call for setter and getter
   * 
   * @param string $key
   * @param type $args
   * 
   * @return array|string|int|bool|object|null
   */
  public static function __callStatic(string $key, $args): array|string|int|bool|object|null {
    if (!empty($args)) {
      return self::set($key, $args);
    } else {
      return self::get($key);
    }
  }

  /**
   * Provide the array path (keys joined by /) and get the corresponding value from the <code>$config</code> array
   * 
   * @param string $keys
   *   Array path - keys joined by /
   * @param type $return
   *   Default return value
   * 
   * @return array|string|int|bool|object|null
   */
  public static function get(string $keys, $return = NULL): array|string|int|bool|object|null {
    $item = self::$config;
    $keys = explode('/', $keys);

    if (empty($keys)) {
      return $return;
    }

    foreach ($keys as $k) {
      if (isset($item[$k])) {
        $item = $item[$k];
      } else {
        return $return;
      }
    }

    return $item;
  }

  /**
   * Add an element to the <code>$config</code> array
   * 
   * @param string|null $keys
   *   Array path - keys joined by /
   * @param type $value
   *   The value
   * 
   * @return array|string|int|bool|object|null
   */
  public static function set(?string $keys = '', $value = NULL): array|string|int|bool|object|null {
    $keys = explode('/', $keys);

    if (empty($keys)) {
      self::$config = $value;
      return $value;
    }

    $found = &self::$config;
    foreach ($keys as $key) {
      if (!isset($found[$key]) || !is_array($found[$key])) {
        $found[$key] = [];
      }

      $found = &$found[$key];
    }

    $found = $value;

    return $found;
  }

  /**
   * Build initial configuration
   * 
   * @param string|null $configPath
   * 
   * @return void
   */
  public static function buildConfig(?string $configPath = NULL): void {
    self::$config = [
      'is_cron' => php_sapi_name() == 'cli' ? TRUE : FALSE,
      'request_time_object' => new \DateTime('now'),
    ];

    self::$config['request_time'] = self::$config['request_time_object']->getTimestamp();

    self::loadConfigsFromPath($configPath);
    
    self::$config['url'] = !self::$config['is_cron'] ? [
      'protocol' => !empty($_SERVER['HTTPS']) ? 'https' : 'http',
      'domain' => $_SERVER['SERVER_NAME'],
      'uri' => $_SERVER['REQUEST_URI'],
      'query_string' => $_SERVER['QUERY_STRING'],
      'components' => array_filter(preg_split('%(/|\?|&)%', $_SERVER['REQUEST_URI'])),
    ] : [
      'protocol' => '',
      'domain' => '',
    ];
  }
  
  public function setConfigPath(string $configPath): self {
    self::$config['paths']['configs'] = $configPath;
    
    return $this;
  }
  
  /**
   * Load config from a specified file
   * 
   * @param string $fileName
   * 
   * @return self
   */
  public function loadConfigFile(string $fileName): self {
    self::load($fileName);

    return $this;
  }
  
  /**
   * Load config from all files in path
   * 
   * @param string|null $configPath
   * 
   * @return void
   */
  protected static function loadConfigsFromPath(?string $configPath = NULL): void {
    if (!$configPath || !file_exists($configPath) || !is_dir($configPath)) {
      return;
    }

    self::$config['paths']['configs'] = $configPath;

    if (!($list = scandir($configPath))) {
      trigger_error('Invalid config path', E_USER_ERROR);
      return;
    }

    foreach ($list as $configFile) {
      if (($ext = pathinfo($configFile, PATHINFO_EXTENSION)) == 'json') {
        self::load($configFile, $ext);
      }
    }
  }
  
  /**
   * Load config from a specified file
   * 
   * @param string $fileName
   * @param string|null $type
   * 
   * @return void
   */
  protected static function load(string $fileName, ?string $type = 'json'): void {
    $configItems = [];

    $configFile = $fileName;
    if (self::$config['paths']['configs'] ?? NULL) {
      $configFile = self::$config['paths']['configs'] . "/{$fileName}";
    }

    if (!file_exists($configFile)) {
      trigger_error('Invalid config : ' . $configFile, E_USER_ERROR);
      return;
    }

    if (!$configItem = file_get_contents($configFile)) {
      trigger_error('Invalid config : ' . $configFile, E_USER_ERROR);
      return;
    }

    if ($type == 'json') {
      $configItems = json_decode($configItem, true);
      if (json_last_error() != JSON_ERROR_NONE) {
        trigger_error('Invalid JSON : ' . $configFile . " ; " . json_last_error_msg(), E_USER_ERROR);
        return;
      }
    }

    self::$config = self::blend($configItems, self::$config);
  }
  
  /**
   * Insert the config item in the config array, overwrites item if already exists
   * 
   * @param array $configItems
   * @param array $toArray
   * 
   * @return array
   */
  protected static function blend(array $configItems, array $toArray): array {
    foreach ($configItems as $key => $values) {
      if (is_array($values)) {
        $toArray[$key] = self::blend($values, $toArray[$key] ?? []);
      } else {
        $toArray[$key] = $values;
      }
    }

    return $toArray;
  }
}
