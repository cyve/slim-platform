<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class All extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $pdo = $this->container->get('pdo');
        $stmt = $pdo->prepare('SELECT id,'.implode(',', array_keys($this->config['model'])).' FROM '.$this->config['table']);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($results));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
