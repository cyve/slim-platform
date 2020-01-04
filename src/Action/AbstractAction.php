<?php

namespace SlimPlatform\Action;

use Psr\Container\ContainerInterface;

abstract class AbstractAction
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container, $resource) {
        $this->container = $container;
        $this->config = $container->get('config')['resources'][$resource];
    }
}
