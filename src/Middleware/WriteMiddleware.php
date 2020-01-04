<?php

namespace SlimPlatform\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class WriteMiddleware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $pdo = $this->container->get('pdo');
        $config = $request->getAttribute('_config');
        $table = $config['table'];
        $columns = array_keys($config['model']);
        $data = $request->getAttribute('data');

        if ($request->getAttribute('_action') === 'create') {
            $sqlColumns = array_map(function ($column) {
                return sprintf(':%s', $column);
            }, $columns);
            $sql = 'INSERT INTO '.$table.' ('.implode(',', $columns).') VALUES ('.implode(',', $sqlColumns).')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute((array) $data);
            $data->id = $pdo->lastInsertId();

        } elseif ($request->getAttribute('_action') === 'update') {
            $sqlColumns = array_map(function ($column) {
                return sprintf('%s=:%s', $column, $column);
            }, $columns);
            $sql = 'UPDATE '.$table.' SET '.implode(',', $sqlColumns).' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute((array) $data);

        } elseif ($request->getAttribute('_action') === 'delete') {
            $stmt = $pdo->prepare('DELETE FROM '.$table.' WHERE id = :id');
            $stmt->execute(['id' => $data->id]);

            $request = $request->withoutAttribute('data');
        }

        return $handler->handle($request);
    }
}
