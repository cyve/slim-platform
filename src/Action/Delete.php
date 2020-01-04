<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Delete extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        return $response->withStatus(204);
    }
}
