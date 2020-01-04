<?php

namespace SlimPlatform\Action;

use Psr\Container\ContainerInterface;

abstract class AbstractAction
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container, $config) {
        $this->container = $container;
        $this->config = $config;
    }
}
