<?php

namespace Technically\DependencyResolver\Specs\Fixtures;

use Technically\ArrayContainer\ArrayContainer;

final class MyConcreteContainerService
{
    public $container;

    public function __construct(ArrayContainer $container)
    {
        $this->container = $container;
    }
}
