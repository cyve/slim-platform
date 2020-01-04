<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Container\ContainerInterface;
use Slim\Exception\HttpNotFoundException;

abstract class AbstractAction
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container, $resource) {
        $this->container = $container;
        $this->config = $container->get('config')['resources'][$resource];
    }

    protected function read(Request $request)
    {
        $pdo = $this->container->get('pdo');
        $table = $this->config['table'];
        $columns = array_keys($this->config['model']);

        if ($id = $request->getAttribute('id')) {
            $stmt = $pdo->prepare('SELECT id,'.implode(',', $columns).' FROM '.$table.' WHERE id = :id');
            $stmt->execute(['id' => (int) $id]);

            if (!$result = $stmt->fetch(\PDO::FETCH_OBJ)) {
                throw new HttpNotFoundException($request);
            }

            return $result;
        }

        $stmt = $pdo->prepare('SELECT id,'.implode(',', $columns).' FROM '.$table);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    protected function write($object)
    {
        $pdo = $this->container->get('pdo');
        $table = $this->config['table'];
        $columns = array_keys($this->config['model']);

        if (isset($object->id)) {
            $sqlColumns = array_map(function ($column) {
                return sprintf('%s=:%s', $column, $column);
            }, $columns);
            $sql = 'UPDATE '.$table.' SET '.implode(',', $sqlColumns).' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute((array) $object);
        } else {
            $sqlColumns = array_map(function ($column) {
                return sprintf(':%s', $column);
            }, $columns);
            $sql = 'INSERT INTO '.$table.' ('.implode(',', $columns).') VALUES ('.implode(',', $sqlColumns).')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute((array) $object);
            $object->id = $pdo->lastInsertId();
        }

        return $object;
    }

    protected function delete($object)
    {
        $pdo = $this->container->get('pdo');

        $stmt = $pdo->prepare('DELETE FROM '.$this->config['table'].' WHERE id = :id');
        $stmt->execute(['id' => $object->id]);
    }
}
