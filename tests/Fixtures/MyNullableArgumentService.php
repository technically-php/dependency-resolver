<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

use Psr\Container\ContainerInterface;

final class MyNullableArgumentService
{
    public $container;

    public function __construct(?ContainerInterface $container)
    {
        $this->container = $container;
    }
}
