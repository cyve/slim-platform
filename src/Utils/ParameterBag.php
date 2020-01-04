<?php

namespace SlimPlatform\Utils;

use Psr\Container\ContainerInterface;

class ParameterBag implements ContainerInterface
{
    private $parameters = [];

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function get($name)
    {
        return $this->parameters[$name] ?? null;
    }

    public function has($name)
    {
        return isset($this->parameters[$name]);
    }

    public function set(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }
}
