<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

final class MyOptionalArgumentService
{
    public $name;

    public function __construct(string $name = 'MyOptionalArgumentService')
    {
        $this->name = $name;
    }
}
