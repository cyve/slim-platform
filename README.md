# cyve/slim-platform

PHP micro framework for REST API based on [Slim](http://www.slimframework.com) and inspired by [API Platform](https://api-platform.com/).

## Installation
```bash
$ composer create-project cyve/slim-platform"
```
```php
// config.php
return [
    'title' => 'Slim Platform',
    'parameters' => [],
    'resources' => [
        'book' => [
            'table' => 'book',
            'model' => [
                'title' => ['type' => 'string', 'required' => true],
                'isbn' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'author' => ['type' => 'string'],
                'publicationDate' => ['type' => 'datetime']
            ],
            'actions' => [
                'create' => ['method' => 'POST', 'uri' => '/books'],
                'read' => ['method' => 'GET', 'uri' => '/books/{id}'],
                'update' => ['method' => 'PUT', 'uri' => '/books/{id}'],
                'delete' => ['method' => 'DELETE', 'uri' => '/books/{id}'],
                'index' => ['method' => 'GET', 'uri' => '/books']
            ]
        ]
    ]
];
```
```php
// index.php
require 'vendor/autoload.php';

if (is_readable('.env')) {
    $_ENV = $_ENV + parse_ini_file('.env');
}

$config = include 'config.php';
$config['parameters'] += $_ENV;

$app = new SlimPlatform\App($config);
$app->run();
```
