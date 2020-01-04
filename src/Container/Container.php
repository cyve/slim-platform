<?php

namespace SlimPlatform\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $container = [];

    public function get($id)
    {
        return $this->container[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->container[$id]);
    }

    public function set(string $id, $value)
    {
        $this->container[$id] = $value;
    }
}
