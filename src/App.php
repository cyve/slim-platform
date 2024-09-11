<?php

namespace SlimPlatform;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use SlimPlatform\Application\Action;
use SlimPlatform\Infrastructure\Persistence\Config;
use SlimPlatform\Infrastructure\Persistence\PdoFactory;
use SlimPlatform\Infrastructure\Persistence\PdoRepository;

class App
{
    private Slim\App $app;

    public function __construct()
    {
        $databaseDsn = $_ENV['DATABASE_DSN'] ?? throw new \RuntimeException('The environment variable "DATABASE_DSN" is not set.');
        $pdo = PdoFactory::create($databaseDsn);

        $config = new Config($pdo);

        $app = AppFactory::create();
        $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write(sprintf('SlimPlatform %s', $_ENV['APP_VERSION'] ?? '0.0.0'));

            return $response;
        });

        foreach ($config->getResources() as $resource) {
            $repository = new PdoRepository($pdo, $resource['table']);
            $app->get($resource['path'].'[/]', new Action\Index($repository));
            $app->get($resource['path'].'/{id}', new Action\Read($repository));
            $app->post($resource['path'].'[/]', new Action\Create($repository));
            $app->patch($resource['path'].'/{id}', new Action\Update($repository));
            $app->delete($resource['path'].'/{id}', new Action\Delete($repository));
        }

        $app->addMiddleware(new BodyParsingMiddleware());
        $app->addErrorMiddleware(true, true, true);

        $this->app = $app;
    }

    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->app->addMiddleware($middleware);
    }

    public function run(?ServerRequestInterface $request = null): void
    {
        $this->app->run($request);
    }
}
