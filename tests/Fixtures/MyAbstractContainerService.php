<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

use Psr\Container\ContainerInterface;

final class MyAbstractContainerService
{
    public $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
