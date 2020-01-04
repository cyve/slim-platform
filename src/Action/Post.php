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

        foreach ($data as &$item) {
            $item = $this->write((object) $item);
        }

        if (array_values($data) === $data && count($data) === 1) {
            $data = $data[0];
        }

        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
