<?php

namespace Technically\DependencyResolver\Specs\Fixtures;

use Psr\Container\ContainerInterface;

final class MyAbstractContainerService
{
    public $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
