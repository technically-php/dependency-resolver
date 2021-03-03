<?php

namespace Technically\DependencyResolver\Specs\Fixtures;

final class MyUnresolvableScalarArgumentService
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
