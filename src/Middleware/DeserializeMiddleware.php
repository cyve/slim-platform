<?php

namespace SlimPlatform\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class DeserializeMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $action = $request->getAttribute('_action');
        if ($action === 'create') {
            $data = (object) $request->getParsedBody();
            $request = $request->withAttribute('data', $data);
        } elseif ($action === 'update') {
            $data = (object) array_merge((array) $request->getAttribute('data'), $request->getParsedBody());
            $request = $request->withAttribute('data', $data);
        }

        return $handler->handle($request);
    }
}
