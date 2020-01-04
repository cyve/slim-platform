<?php

namespace SlimPlatform\Action;

use Psr\Container\ContainerInterface;

abstract class AbstractAction
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
}
