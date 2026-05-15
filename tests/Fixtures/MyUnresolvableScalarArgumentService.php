<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

final class MyUnresolvableScalarArgumentService
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
