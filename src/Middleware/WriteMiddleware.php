<?php

namespace SlimPlatform\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class WriteMiddleware
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $this->config = $request->getAttribute('_config');
        $data = $request->getAttribute('data');

        if ($request->getAttribute('_action') === 'create') {
            if (is_array($data)) {
                array_walk($data, [$this, 'create']);
            } else {
                $this->create($data);
            }

        } elseif ($request->getAttribute('_action') === 'update') {
            $this->update($data);

        } elseif ($request->getAttribute('_action') === 'delete') {
            $this->delete($data);
            $request = $request->withoutAttribute('data');
        }

        return $handler->handle($request);
    }

    private function create (&$data): void
    {
        $columns = array_keys($this->config['model']);
        $sqlColumns = array_map(function ($column) {
            return sprintf(':%s', $column);
        }, $columns);
        $sql = 'INSERT INTO '.$this->config['table'].' ('.implode(',', $columns).') VALUES ('.implode(',', $sqlColumns).')';
        $pdo = $this->container->get('pdo');
        $stmt = $pdo->prepare($sql);
        $stmt->execute((array) $data);
        $data->id = $pdo->lastInsertId();
    }

    private function update ($data): void
    {
        $columns = array_keys($this->config['model']);
        $sqlColumns = array_map(function ($column) {
            return sprintf('%s=:%s', $column, $column);
        }, $columns);
        $sql = 'UPDATE '.$this->config['table'].' SET '.implode(',', $sqlColumns).' WHERE id = :id';
        $stmt = $this->container->get('pdo')->prepare($sql);
        $stmt->execute((array) $data);
    }

    private function delete ($data): void
    {
        $stmt = $this->container->get('pdo')->prepare('DELETE FROM '.$this->config['table'].' WHERE id = :id');
        $stmt->execute(['id' => $data->id]);
    }
}
