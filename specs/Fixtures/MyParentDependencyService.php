<?php

namespace Technically\DependencyResolver\Specs\Fixtures;

final class MyParentDependencyService extends MySelfDependencyService
{
    public function __construct(parent $parent = null)
    {
        parent::__construct($parent);
    }
}
