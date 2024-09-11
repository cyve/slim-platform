# cyve/slim-platform

PHP micro framework based on [Slim](http://www.slimframework.com) to add a REST API on top of a MySQL server.

## Installation
```bash
composer create-project cyve/slim-platform
```
```php
// index.php
require 'vendor/autoload.php';

if (is_readable('.env')) {
    $_ENV = $_ENV + parse_ini_file('.env');
}

$app = new SlimPlatform\App();

// Add as many PSR-15 middlewares as you need
// Check some awesome middleware examples here: https://github.com/middlewares
$app->addMiddleware(new Middlewares\ResponseTime());
$app->addMiddleware(new Middlewares\GzipEncoder());
$app->addMiddleware(new Middlewares\Expires(['application/json' => '+1 hour']));

$app->run();
```
⚠️ The environment variable `DATABASE_DSN` is mandatory  (ex: `mysql://user:pa$$w0rd@127.0.0.1:3306/database`)
