<?php

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use SlimPlatform\Middleware;
use SlimPlatform\Utils;

require dirname(__DIR__).'/vendor/autoload.php';

if (is_readable(dirname(__DIR__).'/.env')) {
    $_ENV = $_ENV + parse_ini_file(dirname(__DIR__).'/.env');
}

$config = [
    'resources' => [
        'book' => [
            'table' => 'book',
            'model' => [
                'title' => [
                    'type' => 'string',
                    'required' => true
                ],
                'isbn' => [
                    'type' => 'string'
                ],
                'description' => [
                    'type' => 'string'
                ],
                'author' => [
                    'type' => 'string'
                ],
                'publicationDate' => [
                    'type' => 'datetime'
                ]
            ],
            'actions' => [
                'create' => [
                    'method' => 'POST',
                    'uri' => '/books',
                    'handler' => 'SlimPlatform\Action\Post'
                ],
                'read' => [
                    'method' => 'GET',
                    'uri' => '/books/{id}',
                    'handler' => 'SlimPlatform\Action\Get'
                ],
                'update' => [
                    'method' => 'PUT',
                    'uri' => '/books/{id}',
                    'handler' => 'SlimPlatform\Action\Put'
                ],
                'delete' => [
                    'method' => 'DELETE',
                    'uri' => '/books/{id}',
                    'handler' => 'SlimPlatform\Action\Delete'
                ],
                'index' => [
                    'method' => 'GET',
                    'uri' => '/books',
                    'handler' => 'SlimPlatform\Action\All'
                ]
            ]
        ]
    ]
];

$container = new Utils\ParameterBag(['config' => $config]);

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

foreach ($config['resources'] as $resourceName => $resource) {
    foreach ($resource['actions'] as $actionName => $action) {
        $app->map([$action['method']], $action['uri'], function(Request $request, Response $response, $args) use ($action, $container, $resourceName, $actionName) {
            $handler = new $action['handler']($container, $resourceName);

            return $handler($request, $response, $args);
        })
        ->add(new Middleware\ReadMiddleware($container))
        ->add(function (Request $request, RequestHandler $handler) use ($action, $container, $resourceName, $actionName) {
            $request = $request->withAttribute('_resource', $resourceName);
            $request = $request->withAttribute('_action', strtolower($actionName));
            $request = $request->withAttribute('_config', $container->get('config')['resources'][$resourceName]);

            $response = $handler->handle($request);

            return $response->withHeader('Content-Type', 'application/json');
        });
    }
}

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->run();
