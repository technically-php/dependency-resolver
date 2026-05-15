<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

use Technically\ArrayContainer\ArrayContainer;

final class MyConcreteContainerService
{
    public $container;

    public function __construct(ArrayContainer $container)
    {
        $this->container = $container;
    }
}
