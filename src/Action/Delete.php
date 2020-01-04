<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Delete extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $object = $request->getAttribute('data');
        $this->delete($object);
        $response = $response->withStatus(204);

        return $response;
    }
}
