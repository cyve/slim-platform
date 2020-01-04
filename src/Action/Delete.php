<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

class Delete extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $pdo = $this->container->get('pdo');
        $stmt = $pdo->prepare('DELETE FROM '.$this->config['table'].' WHERE id = :id');
        $stmt->execute(['id' => (int) $args['id']]);
        if ($stmt->rowCount() === 0) {
            throw new HttpNotFoundException($request);
        }

        $response = $response->withStatus(204);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
