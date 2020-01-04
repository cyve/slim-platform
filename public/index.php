<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use SlimPlatform\Container\Container;

require dirname(__DIR__).'/vendor/autoload.php';

if (is_readable(dirname(__DIR__).'/.env')) {
    $_ENV = $_ENV + parse_ini_file(dirname(__DIR__).'/.env');
}

$config = [
    'resources' => [
        'book' => [
            'actions' => [
                'get' => [
                    'method' => 'GET',
                    'uri' => '/books/{id}',
                    'handler' => 'SlimPlatform\Action\Get'
                ]
            ]
        ]
    ]
];

$container = new Container();
$container->set('config', $config);

$params = parse_url($_ENV['DATABASE_URL']);
$container->set('pdo', new \PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', $params['host'], $params['port'] ?? 3306, trim($params['path'], '/')),
    $params['user'],
    $params['pass'] ?? null,
    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
));

$app = AppFactory::createFromContainer($container);
$app->get('/', function (Request $request, Response $response) {
    return $response;
});

foreach ($config['resources'] as $name => $resource) {
    foreach ($resource['actions'] as $action) {
        $app->map([$action['method']], $action['uri'], $action['handler']);
    }
}

$app->addErrorMiddleware(true, true, true);
$app->run();
