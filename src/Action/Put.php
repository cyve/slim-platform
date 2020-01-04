<?php

namespace SlimPlatform\Action;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Put extends AbstractAction
{
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $data = $request->getAttribute('data');

        $this->validate(data);
        $this->write($data);

        $response->getBody()->write(json_encode($data));

        return $response;
    }
}
