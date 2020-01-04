<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Put extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $object = $this->read($request);

        $data = $request->getParsedBody();
        $object = (object) array_merge((array) $object, (array) $data);
        $this->write($object);

        $response->getBody()->write(json_encode($object));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
