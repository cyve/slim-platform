<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

require dirname(__DIR__).'/vendor/autoload.php';

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

$app = AppFactory::create();
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
