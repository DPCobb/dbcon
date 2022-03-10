# D-CLI

A Basic PHP CLI App Framework. This project is dependency free and meant to be a lightweight starting point for PHP CLI applications.

***These docs are a work in progress as this project is still in development.***

[Full Documentation](https://github.com/DPCobb/d-cli/wiki)

![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/dpcobb/d-cli?style=plastic) ![GitHub](https://img.shields.io/github/license/dpcobb/d-cli?style=plastic)

## Basic Setup

Install with: ```composer create-project danc0/dcli``` or download the latest package.

After creating your main file (ex: ``src/dcli``) ensure you make that file executable and optionally add it to your path so you can call your application from anywhere. You will also want to create a Composer autoload file.

The most basic setup uses anonymous functions to process commands.

```php
#!/usr/bin/php
<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require_once __DIR__ . "/vendor/autoload.php";

use App\Core\Application;
use App\Core\Config;
use App\Core\Command_Container;

// Get any user set config values
$config = Config::load(__DIR__ . "/App/config.ini")->get();
$command_container = new Command_Container($config, $argv);

$app = Application::load($command_container);


$app->set('hello-world', function () {
    echo "Hello World";
});

$app->run();

```

This is the most basic possible usage and does not take advantage of sub commands.


## Passing Class@method to Call

This method also does not make use of sub commands but allows you to move your logic into a class.

```php
#!/usr/bin/php
<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require_once __DIR__ . "/vendor/autoload.php";

use App\Core\Application;
use App\Core\Config;
use App\Core\Command_Container;

// Get any user set config values
$config = Config::load(__DIR__ . "/App/config.ini")->get();
$command_container = new Command_Container($config, $argv);

$app = Application::load($command_container);


$app->set('hello-world', 'App\Commands\Hello\Test@test');


$app->run()
```

This will call the ```test``` method in the ```Test``` class.

## File Structure Controlled Commands

This method makes use of sub commands. To use the advanced structure a specific project layout is required. Your classes that will handle the commands must be placed in the ```src/App/Commands``` directory. The command should be a sub folder. For example, the command ```hello``` should be placed in ```src/App/Commands/Hello```. Within that directory a file named ```Default_Handler.php``` should be created. This, and all classes in the ```src/App/Commands``` directory should implement ```Command_Handler_Interface```. This is the file that will be called on the command ```hello```.

To use a sub command create an additional file with that sub-commands name. For example, ```hello test``` would require you to make a file named ```Test.php``` with a class of ```Test``` in ```src/App/Commands/Hello```. This file will be called to process the sub command.

This requires a minimal setup in the main file.

```php
#!/usr/bin/php
<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require_once __DIR__ . "/vendor/autoload.php";

use App\Core\Application;
use App\Core\Config;
use App\Core\Command_Container;

// Get any user set config values
$config = Config::load(__DIR__ . "/App/config.ini")->get();
$command_container = new Command_Container($config, $argv);

$app = Application::load($command_container);

$app->run();
```
The ```Application``` class handles the logic and routing of the calls.
