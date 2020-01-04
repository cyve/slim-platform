<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Post extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $data = $request->getParsedBody();
        if (array_values($data) !== $data) {
            $data = [$data];
        }

        $pdo = $this->container->get('pdo');
        $sql = 'INSERT INTO '.$this->config['table'].' ('.implode(',', array_keys($this->config['model'])).')';
        $sql .= ' VALUES ('.implode(',', array_map(function ($row) {
            return sprintf(':%s', $row);
        }, array_keys($this->config['model']))).')';
        $stmt = $pdo->prepare($sql);

        foreach ($data as &$input) {
            $stmt->execute($input);
            $input['id'] = $pdo->lastInsertId();
        }

        if (array_values($data) === $data && count($data) === 1) {
            $data = $data[0];
        }

        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
