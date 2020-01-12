<?php

namespace SlimPlatform\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class DeserializeMiddleware
{
    protected $config;

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $this->config = $request->getAttribute('_config');

        if ($requestBody = $request->getParsedBody()) {
            $data = $this->deserialize($request->getParsedBody(), $request->getAttribute('data'));
            $request = $request->withAttribute('data', $data);
        }

        return $handler->handle($request);
    }

    private function deserialize($data, $object = null)
    {
        // recursivity
        if (array_values($data) === $data) {
            return array_map([$this, 'deserialize'], $data);
        }

        // convert date to required type
        foreach ($data as $key => $value) {
            $data[$key] = $this->convertTo($value, $this->config['model'][$key]['type'] ?? 'string');
        }

        // update existing object
        if (is_object($object)) {
            return (object) array_merge((array) $object, (array) $data);
        }

        return (object) $data;
    }

    private function convertTo($value, string $type)
    {
        if (gettype($value) === $type) {
            return $value;
        }

        if ($value === '') {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'float':
                return (float) $value;
            case 'integer':
                return (int) $value;
            case 'string':
                return (string) $value;
            default:
                return $value;
        }
    }
}
