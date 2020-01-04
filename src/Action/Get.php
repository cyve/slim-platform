<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Get extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args = []): Response
    {
        $data = $this->read($request);
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
