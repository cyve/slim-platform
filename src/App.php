<?php

namespace SlimPlatform;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim;
use SlimPlatform\Middleware;
use SlimPlatform\Utils;

class App
{
    private $app;

    public function __construct(array $config)
    {
        $params = parse_url($config['parameters']['DATABASE_URL']);
        $pdo = new \PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', $params['host'], $params['port'] ?? 3306, trim($params['path'], '/')),
            $params['user'],
            $params['pass'] ?? null,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        $container = new Utils\ParameterBag(['config' => $config, 'pdo' => $pdo]);

        $app = Slim\Factory\AppFactory::createFromContainer($container);
        $app->get('/', function (Request $request, Response $response) {
            $config = $this->get('config');

            $html = '<h1>'.$config['title'].'</h1>';
            $html .= '<ul>';
            foreach ($config['resources'] as $resourceName => $resource) {
                foreach ($resource['actions'] as $actionName => $action) {
                    $html .= '<li><code>'.$action['method'].' '.$action['uri'].'</code></li>';
                }
            }
            $html .= '</ul>';
            $response->getBody()->write($html);

            return $response;
        });

        foreach ($config['resources'] as $resourceName => $resource) {
            foreach ($resource['actions'] as $actionName => $action) {
                $app->map([$action['method']], $action['uri'], function (Request $request, Response $response) {
                    $statusCode = 204;
                    $data = $request->getAttribute('data');
                    if ($data !== null) {
                        $statusCode = $request->getAttribute('_action') === 'create' ? 201 : 200;
                        $response->getBody()->write(json_encode($data));
                    }

                    return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
                })
                ->add(new Middleware\WriteMiddleware($container))
                ->add(new Middleware\ValidateMiddleware())
                ->add(new Middleware\DeserializeMiddleware())
                ->add(new Middleware\ReadMiddleware($container))
                ->add(new Slim\Middleware\BodyParsingMiddleware())
                ->add(function (Request $request, RequestHandler $handler) use ($action, $container, $resourceName, $actionName) {
                    $request = $request->withAttribute('_resource', $resourceName);
                    $request = $request->withAttribute('_action', strtolower($actionName));
                    $request = $request->withAttribute('_config', $container->get('config')['resources'][$resourceName]);

                    return $handler->handle($request);
                });
            }
        }

        $app->addErrorMiddleware(true, true, true);

        $this->app = $app;
    }

    public function run(): void
    {
        $this->app->run();
    }
}
