<?php

namespace SlimPlatform\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ValidateMiddleware
{
    private $config;

    public function __invoke(Request $request, RequestHandler $handler)
    {
        if (in_array($request->getAttribute('_action'), ['create', 'update'])) {
            $this->config = $request->getAttribute('_config');
            $this->validate($request->getAttribute('data'));
        }

        return $handler->handle($request);
    }

    private function validate($data)
    {
        // recursivity
        if (is_array($data)) {
            return array_walk($data, [$this, 'validate']);
        }

        foreach ($this->config['model'] as $property => $model) {
            $value = $data->$property;

            if ($value === null) {
                if ($model['required'] ?? false) {
                    throw new \Exception(sprintf('Property `%s` is empty.', $property));
                }
                continue;
            }

            $type = $model['type'] ?? 'string';
            if ($type === 'datetime') {
                if (!\DateTime::createFromFormat('Y-m-d H:i:s', $value)) {
                    throw new \Exception(sprintf('Invalid property `%s` (expected `datetime` with format `Y-m-d H:i:s`).', $property));
                }
            } elseif ($type !== gettype($value)) {
                throw new \Exception(sprintf('Invalid property `%s` (expected `%s`).', $property, $type));
            }
        }
    }
}
