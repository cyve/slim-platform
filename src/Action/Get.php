<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Get extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $response->getBody()->write(json_encode(['message' => 'Ok']));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
