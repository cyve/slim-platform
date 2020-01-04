<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

class Put extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $pdo = $this->container->get('pdo');
        $stmt = $pdo->prepare('SELECT id,'.implode(',', array_keys($this->config['model'])).' FROM '.$this->config['table'].' WHERE id = :id');
        $stmt->execute(['id' => (int) $args['id']]);
        if (!$result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            throw new HttpNotFoundException($request);
        }

        $data = $request->getParsedBody();
        $sql = 'UPDATE '.$this->config['table'].' SET '.implode(',', array_map(function ($row) {
            return sprintf('%s=:%s', $row, $row);
        }, array_keys($this->config['model']))).' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge(['id' => (int) $args['id']], $data));

        $result = array_merge($result, $data);
        $response->getBody()->write(json_encode($result));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
