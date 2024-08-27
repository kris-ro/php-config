# PHP App Configuration Class

PHP class for managing configuration throughout an application.

## Installation

Use composer to install *PHP Config*.

```bash
composer require kris-ro/php-config
```

## Usage

```php
require YOUR_PATH . '/vendor/autoload.php';

use KrisRo\PhpConfig\Config;

# static call
# providing a path (optional) to a folder here will load all json files in the folder
Config::buildConfig('/absolute/path/to/your/folder/with/json/files');

// magic call for debug and salt entries in the config array
Config::debug();
Config::salt();

// setter call
// set method returns the new value
Config::set('database/username', 'k2'); # returns 'k2'

Config::get('database/username'); # returns 'k2'
```

### Initialize as object

```php
// path here is also optional; if provided all json files in that folder will be loaded
new Config('/absolute/path/to/your/folder/with/json/files');

$this->assertEquals('k2', Config::set('database/username', 'k2'));
// verify new value
$this->assertEquals('k2', Config::get('database/username'));
```

### Load json files in specific order

```php
(new Config())
      ->loadConfigFile(/absolut/path/to/first.json')
      ->loadConfigFile(/absolut/path/to/second.json')
      ->loadConfigFile(/absolut/path/to/nth.json');

(new Config())
      ->setConfigPath('/absolute/path/to/your/folder/with/json/files')
      ->loadConfigFile(first-file-to-load.json')
      ->loadConfigFile(second-file-to-load.json')
      ->loadConfigFile(nth-file-to-load.json');
```

