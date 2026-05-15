<?php

namespace Technically\DependencyResolver\Tests\Fixtures;

class MySelfDependencyService
{
    public $parent;

    public function __construct(?self $parent = null)
    {
        $this->parent = $parent;
    }
}
