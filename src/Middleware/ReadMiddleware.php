<?php

namespace SlimPlatform\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ReadMiddleware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        if (in_array($request->getAttribute('_action'), ['read', 'update', 'delete', 'index'])) {
            $pdo = $this->container->get('pdo');
            $config = $request->getAttribute('_config');
            $table = $config['table'];
            $columns = array_keys($config['model']);

            if ($id = ($request->getAttribute('route')->getArguments()['id'] ?? null)) {
                $stmt = $pdo->prepare('SELECT id,' . implode(',', $columns) . ' FROM ' . $table . ' WHERE id = :id');
                $stmt->execute(['id' => (int)$id]);

                if (!$data = $stmt->fetch(\PDO::FETCH_OBJ)) {
                    throw new \Slim\Exception\HttpNotFoundException($request);
                }
            } else {
                $stmt = $pdo->prepare('SELECT id,' . implode(',', $columns) . ' FROM ' . $table);
                $stmt->execute();
                $data = $stmt->fetchAll(\PDO::FETCH_OBJ);
            }

            $request = $request->withAttribute('data', $data);
        }

        return $handler->handle($request);
    }
}
