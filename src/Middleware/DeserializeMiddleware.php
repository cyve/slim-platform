<?php

namespace SlimPlatform\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class DeserializeMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        if ($requestBody = $request->getParsedBody()) {
            $data = $this->deserialize($request->getParsedBody(), $request->getAttribute('data'));
            $request = $request->withAttribute('data', $data);
        }

        return $handler->handle($request);
    }

    private function deserialize($data, $object = null)
    {
        if (is_object($object)) {
            return (object) array_merge((array) $object, (array) $data);
        }

        // recursivity
        if (array_values($data) === $data) {
            return array_map([$this, 'deserialize'], $data);
        }

        return (object) $data;
    }
}
